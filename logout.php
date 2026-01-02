<?php
session_start();

// Kiléptetés: minden session adat törlése
$_SESSION = [];
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}
session_unset();
session_destroy();

// Vissza a főoldalra
header('Location: /');
echo '<!doctype html><meta http-equiv="refresh" content="0;url=/">';
exit;

?>