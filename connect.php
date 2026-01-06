<?php

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'hair_heaven'; 

$cfgFile = __DIR__ . '/db_config.php';
if (is_file($cfgFile)) {
  include $cfgFile;
}

function db(): ?mysqli {
  static $conn = null;

  if ($conn instanceof mysqli) {
    return $conn;
  }


  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


  $DB_HOST = $GLOBALS['DB_HOST'] ?? 'localhost';
  $DB_USER = $GLOBALS['DB_USER'] ?? 'root';
  $DB_PASS = $GLOBALS['DB_PASS'] ?? '';
  $DB_NAME = $GLOBALS['DB_NAME'] ?? '';

  try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8'); 
    $conn->query("SET NAMES utf8 COLLATE utf8_hungarian_ci");
    $conn->query("SET collation_connection = 'utf8_hungarian_ci'");
    $conn->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

    return $conn;
  } catch (Throwable $e) {
    $conn = null;
    return null;
  }
}


function db_close(): void {
  $c = db();
  if ($c instanceof mysqli) { $c->close(); }
}
