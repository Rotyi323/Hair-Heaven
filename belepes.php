<?php
session_start();
require_once __DIR__ . '/biztonsag.php';
require_once __DIR__ . '/connect.php';

$mysqli = db(); // mysqli | null

if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

// --- CSRF fallback, ha nincs közös ---
$use_local_csrf = !function_exists('csrf_field');
if ($use_local_csrf) {
  if (empty($_SESSION['_token_login'])) {
    $_SESSION['_token_login'] = bin2hex(random_bytes(32));
  }
  function local_csrf_field(){
    return '<input type="hidden" name="_token" value="'.e($_SESSION['_token_login'] ?? '').'">';
  }
}

// --- Rate limit: 5 próbálkozás / 15 perc ---
$rl_key   = '_login_attempts';
$rl_window= 15 * 60; // sec
$rl_limit = 5;

if (!isset($_SESSION[$rl_key])) {
  $_SESSION[$rl_key] = ['count'=>0, 'start'=>time()];
} else {
  if (time() - $_SESSION[$rl_key]['start'] > $rl_window) {
    $_SESSION[$rl_key] = ['count'=>0, 'start'=>time()];
  }
}

$errors = [];
$identifier = ''; // email vagy username
$justRegistered = isset($_GET['registered']) && $_GET['registered'] == '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Rate limit check
  if ($_SESSION[$rl_key]['count'] >= $rl_limit) {
    $errors[] = 'Túl sok sikertelen próbálkozás. Kérlek próbáld újra később.';
  }

  // CSRF
  if (function_exists('csrf_validate')) {
    // dob és leáll, ha nem oké – de biztos ami biztos:
    try { csrf_validate(); } catch (Throwable $e) { $errors[] = 'Biztonsági hiba (CSRF). Kérlek próbáld újra.'; }
  } else {
    $token = $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['_token_login'] ?? '', $token)) {
      $errors[] = 'Biztonsági hiba (CSRF). Kérlek frissítsd az oldalt és próbáld újra.';
    }
  }

  // Beolvasás
  $identifier = trim((string)($_POST['identifier'] ?? ''));
  $password   = (string)($_POST['password'] ?? '');

  if ($identifier === '' || $password === '') {
    $errors[] = 'Minden mező kötelező.';
  }

  if (empty($errors) && $mysqli) {
    try {
      // LEKÉRÜNK MINDENT, AVATARRAL EGYÜTT
      $stmt = $mysqli->prepare("
        SELECT id, username, email, password_hash, role, avatar
        FROM users
        WHERE email = ? OR username = ?
        LIMIT 1
      ");
      $stmt->bind_param('ss', $identifier, $identifier);
      $stmt->execute();
      $res = $stmt->get_result();
      $user = $res ? $res->fetch_assoc() : null;
      $stmt->close();

      $ok = false;
      if ($user && isset($user['password_hash'])) {
        $ok = password_verify($password, $user['password_hash']);
      }

      if ($ok) {
        // Reset rate limit
        $_SESSION[$rl_key] = ['count'=>0, 'start'=>time()];

        // Alap avatar, ha nincs megadva a DB-ben
        $defaultAvatar = '/assets/img/avatar-default.svg';
        $avatarPath = (!empty($user['avatar'])) ? $user['avatar'] : $defaultAvatar;

        // Bejelentkeztetés
        $_SESSION['belepve']  = true;
        $_SESSION['user_id']  = (int)$user['id'];
        $_SESSION['username'] = $user['username'] ?? null;
        $_SESSION['email']    = $user['email'] ?? null;
        $_SESSION['role']     = $user['role'] ?? 'customer';
        $_SESSION['avatar']   = $avatarPath;

        header('Location: /');
        exit;
      } else {
        $_SESSION[$rl_key]['count']++;
        $errors[] = 'Hibás felhasználónév/e-mail vagy jelszó.';
      }

    } catch (Throwable $ex) {
      $errors[] = 'Váratlan hiba történt. Próbáld újra később.';
      // error_log('Login error: '.$ex->getMessage());
    }
  } elseif (!$mysqli) {
    $errors[] = 'Az adatbázis jelenleg nem elérhető. Kérlek próbáld később.';
  }
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hair Heaven – Bejelentkezés</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    :root{ --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75; --hh-bg:#faf7ff; }
    body{ background:var(--hh-bg); color:var(--hh-dark); }
    .auth-card{ background:#fff; border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .form-control{ border-radius:10px; }
    .page-title{ font-weight:800; letter-spacing:.3px; }
    .muted{ color:var(--hh-muted); }
  </style>
</head>
<body>

<?php $activePage = ''; include __DIR__ . '/navbar.php'; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <h1 class="page-title mb-2">Bejelentkezés</h1>
      <div class="muted mb-4">Lépj be a fiókodba az időpontfoglaláshoz és rendeléshez.</div>

      <?php if ($justRegistered): ?>
        <div class="alert alert-success">
          Sikeres regisztráció! Most jelentkezz be a folytatáshoz.
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <strong>Nem sikerült a bejelentkezés.</strong><br>
          <?= e(implode(' ', $errors)) ?>
        </div>
      <?php endif; ?>

      <div class="auth-card">
        <form method="post" action="/belepes.php" novalidate>
          <?php
            if (function_exists('csrf_field')) { echo csrf_field(); }
            else { echo local_csrf_field(); }
          ?>

          <div class="mb-3">
            <label for="identifier" class="form-label">E-mail vagy felhasználónév</label>
            <input type="text" class="form-control" id="identifier" name="identifier"
                   value="<?= e($identifier) ?>" required placeholder="te@pelda.hu vagy pl. Anna Kovács">
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Jelszó</label>
            <input type="password" class="form-control" id="password" name="password"
                   required minlength="8" autocomplete="current-password" placeholder="••••••••">
          </div>

          <div class="d-grid mt-4">
            <button class="btn btn-cta text-white btn-lg" type="submit">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Bejelentkezés
            </button>
          </div>
        </form>

        <div class="text-center mt-3">
          Még nincs fiókod? <a href="/regisztracio.php">Regisztrálj</a>.
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="py-4 border-top bg-white">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between">
    <div><strong>Hair Heaven</strong> &middot; Premium hair & scalp care</div>
    <div class="text-muted">© <?= date('Y') ?> Hair Heaven · Minden jog fenntartva</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
