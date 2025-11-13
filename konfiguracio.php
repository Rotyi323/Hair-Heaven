<?php
/**
 * Hair Heaven – konfiguracio.php
 * MySQLi kapcsolat utf8 + utf8_hungarian_ci kol­lációval.
 * - MySQLi kivételek (STRICT report)
 * - Környezeti változó felülírások
 * - Nem szivárogtatunk DB-hibát a kliensnek
 */

declare(strict_types=1);

/* ===== Alap hibakezelés ===== */
error_reporting(E_ALL);
// Élesben inkább logolj és ne jeleníts meg:
// ini_set('display_errors', '0');

/* ===== Időzóna ===== */
date_default_timezone_set('Europe/Budapest');

/* ===== Adatbázis beállítások (ENV-vel felülírhatók) ===== */
$DB_HOST    = getenv('HH_DB_HOST') ?: 'localhost';
$DB_USER    = getenv('HH_DB_USER') ?: 'root';
$DB_PASS    = getenv('HH_DB_PASS') ?: '';
$DB_NAME    = getenv('HH_DB_NAME') ?: 'hair_heaven';
$DB_CHARSET = getenv('HH_DB_CHARSET') ?: 'utf8';
$DB_COLLATE = getenv('HH_DB_COLLATE') ?: 'utf8_hungarian_ci';

/* ===== MySQLi kivételek bekapcsolása ===== */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ===== Kapcsolódás ===== */
try {
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

    // Karakterkészlet és kol­láció beállítása
    // 1) karakterkészlet
    if (!@$mysqli->set_charset($DB_CHARSET)) {
        // Fallback, de mivel utf8-at kérünk, ez ritkán fut le
        $mysqli->set_charset('utf8');
    }
    // 2) kapcsolat kol­lációja
    // SET NAMES biztosítja a connection collation-t is
    $charsetEsc = $mysqli->real_escape_string($DB_CHARSET);
    $collateEsc = $mysqli->real_escape_string($DB_COLLATE);
    @$mysqli->query("SET NAMES '{$charsetEsc}' COLLATE '{$collateEsc}'");
    // (Opcionális) explicit:
    @$mysqli->query("SET collation_connection = '{$collateEsc}'");

    // (Opcionális) időzóna a DB-nek is
    @$mysqli->query("SET time_zone = '+01:00'");

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Adatbázis hiba történt. Kérjük, próbáld meg később.');
}

/* ===== Hasznos segédfüggvények ===== */

/** HTML-escape rövidítő */
if (!function_exists('e')) {
    function e(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

/** Egyszerű lekérő – prepared támogatással */
if (!function_exists('db_fetch_all')) {
    function db_fetch_all(mysqli $db, string $sql, string $types = '', array $params = []): array {
        if ($types !== '' && !empty($params)) {
            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $db->query($sql);
        }
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}
