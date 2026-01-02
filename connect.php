<?php
// Hair Heaven – Központi adatbázis kapcsolat (MySQLi)
// Használat minden oldalon:
//   require_once __DIR__ . '/connect.php';
//   $mysqli = db(); // -> mysqli|NULL

// (1) Alap beállítások – lehetőleg tedd a valós hitelesítőket db_config.php-ba
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'hair_heaven'; // állítsd a tényleges adatbázisnévre

// (2) Ha létezik db_config.php, az felülírhatja a fenti változókat
$cfgFile = __DIR__ . '/db_config.php';
if (is_file($cfgFile)) {
  // elvárás: a fájl a fenti változóneveket (stringeket) állítja be
  include $cfgFile;
}

// (3) Singleton kapcsolat visszaadó függvény
function db(): ?mysqli {
  static $conn = null;

  if ($conn instanceof mysqli) {
    return $conn;
  }

  // Hibák dobása kivétellel – könnyebb try/catch kezelés
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

  // Külső látható változók beemelése
  $DB_HOST = $GLOBALS['DB_HOST'] ?? 'localhost';
  $DB_USER = $GLOBALS['DB_USER'] ?? 'root';
  $DB_PASS = $GLOBALS['DB_PASS'] ?? '';
  $DB_NAME = $GLOBALS['DB_NAME'] ?? '';

  try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    // Kapcsolat karakterkészlet + magyar kolláció
    $conn->set_charset('utf8'); // kliens/kapcsolat
    $conn->query("SET NAMES utf8 COLLATE utf8_hungarian_ci");
    $conn->query("SET collation_connection = 'utf8_hungarian_ci'");

    // STRICT mód (jobb adat-integritás)
    $conn->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

    // Időzóna opcionális (pl. Europe/Budapest)
    // $conn->query(\"SET time_zone = '+01:00'\"); // téli idő – ha kell

    return $conn;
  } catch (Throwable $e) {
    // Ha bármi gond van, ne álljon fejre az oldal – visszaadunk null-t.
    // Logolhatod is fájlba, ha szeretnéd:
    // error_log('DB connect error: ' . $e->getMessage());
    $conn = null;
    return null;
  }
}

// (4) Opcionális: lezáró segédfüggvény (ritkán kell weben)
function db_close(): void {
  $c = db();
  if ($c instanceof mysqli) { $c->close(); }
}
