<?php
// Hair Heaven – Központi biztonsági include
// Tedd be MINDEN PHP oldal legelejére (session_start() elé, ha lehet).

/* ===== CSP NONCE =====
   Ezzel engedélyezheted az inline <script>-eket:
   <script nonce="<?= e($GLOBALS['CSP_NONCE'] ?? '') ?>"> ... </script>
*/
if (!isset($GLOBALS['CSP_NONCE'])) {
    $GLOBALS['CSP_NONCE'] = base64_encode(random_bytes(16));
}
$cspNonce = $GLOBALS['CSP_NONCE'];

/* ===== Biztonsági HTTP headerek + CSP ===== */
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("X-XSS-Protection: 0"); // modern böngészőkben deprecated; CSP számít

    // Használt CDN-ek: jsDelivr, Cloudflare (FontAwesome)
    $csp = [
        "default-src 'self'",
        // Inline script csak a nonce-szal engedélyezett + CDN-ek
        "script-src 'self' 'nonce-{$cspNonce}' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        // Bootstrap miatt maradhat az inline style
        "style-src  'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "img-src    'self' data: blob:",
        "font-src   'self' https://cdnjs.cloudflare.com",
        // DevTools source map kérések ne dobjanak hibát
        "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ];
    header("Content-Security-Policy: " . implode("; ", $csp));
}

/* ===== Multibyte/UTF-8 normalizálás ===== */
if (!function_exists('hh_to_utf8')) {
    function hh_to_utf8($s) {
        if ($s === null) return null;
        if (is_array($s)) { foreach ($s as $k=>$v) { $s[$k] = hh_to_utf8($v); } return $s; }
        $s = (string)$s;
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        }
        return str_replace("\0","", $s); // NUL byte kill
    }
    $_GET  = hh_to_utf8($_GET);
    $_POST = hh_to_utf8($_POST);
}

/* ===== HTML escape ===== */
if (!function_exists('e')) {
    function e($str){ return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
}

/* ===== Fehérlistás beolvasók ===== */
if (!function_exists('get_str')) {
    // Szöveg: max hossz, opcionális regexp mintával (pl. csak betű/szám/space)
    function get_str($key, $default = '', $maxlen = 200, $pattern = null, $src = 'GET') {
        $arr = ($src === 'POST') ? $_POST : $_GET;
        if (!isset($arr[$key])) return $default;
        $v = trim((string)$arr[$key]);
        $v = preg_replace('/\s+/u', ' ', $v);  // felesleges whitespace ki
        $v = mb_substr($v, 0, $maxlen);
        if ($pattern && !preg_match($pattern, $v)) return $default;
        return $v;
    }
}

if (!function_exists('get_int')) {
    function get_int($key, $default = 0, $min = null, $max = null, $src = 'GET'){
        $arr = ($src === 'POST') ? $_POST : $_GET;
        if (!isset($arr[$key])) return $default;
        $raw = preg_replace('/\D+/', '', (string)$arr[$key]); // csak számjegy
        if ($raw === '') return $default;
        $n = (int)$raw;
        if ($min !== null) $n = max($min, $n);
        if ($max !== null) $n = min($max, $n);
        return $n;
    }
}

if (!function_exists('get_float')) {
    function get_float($key, $default = 0.0, $min = null, $max = null, $src = 'GET'){
        $arr = ($src === 'POST') ? $_POST : $_GET;
        if (!isset($arr[$key])) return $default;
        $raw = str_replace([',',' '], ['.',''], (string)$arr[$key]);
        if (!is_numeric($raw)) return $default;
        $n = (float)$raw;
        if ($min !== null) $n = max($min, $n);
        if ($max !== null) $n = min($max, $n);
        return $n;
    }
}

if (!function_exists('get_enum')) {
    // Fehérlista: csak a megadott elemek egyike engedélyezett
    function get_enum($key, array $allowed, $default = '', $src = 'GET'){
        $arr = ($src === 'POST') ? $_POST : $_GET;
        if (!isset($arr[$key])) return $default;
        $v = trim((string)$arr[$key]);
        return in_array($v, $allowed, true) ? $v : $default;
    }
}

/* ===== get_param – biztonságos alap szövegbeolvasó ===== */
if (!function_exists('get_param')) {
    // Enged: betűk/számok/space/.-_+%()#@&!/:;'
    function get_param($key, $default = '', $src = 'GET'){
        $pattern = '/^[\p{L}\p{N}\s\.\-\_\+\%\(\)\#\@\&\!\:;\'\/]*$/u';
        return get_str($key, $default, 200, $pattern, $src);
    }
}

/* ===== CSRF token segédek ===== */
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf'];
    }
    function csrf_field() {
        $t = csrf_token();
        return '<input type="hidden" name="csrf" value="'.e($t).'">';
    }
    function csrf_validate() {
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
        if (!$ok) { http_response_code(400); exit('Érvénytelen CSRF token.'); }
    }
}

/* ===== Egyszerű POST rate limit (DoS ellen alap) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $fp = sys_get_temp_dir() . '/hh_rl_' . md5($ip);
    $now = time();
    $win = 1; // 1 mp
    $n   = 0;
    if (is_file($fp)) {
        [$cnt, $ts] = explode('|', @file_get_contents($fp)) + [0,0];
        $cnt = (int)$cnt; $ts=(int)$ts;
        if ($now - $ts <= $win) $n = $cnt;
    }
    $n++;
    file_put_contents($fp, $n.'|'.$now);
    if ($n > 30) { http_response_code(429); exit('Túl sok kérés.'); }
}
