<?php
session_start();
require_once __DIR__ . '/../security.php';
try { csrf_validate(); } catch (Throwable $e) { http_response_code(400); exit('Bad CSRF'); }
if (empty($_SESSION['belepve'])) { header('Location: /login.php'); exit; }

$pid = (int)($_POST['product_id'] ?? 0);
if ($pid > 0 && isset($_SESSION['cart'][$pid])) {
  $q = max(0, (int)$_SESSION['cart'][$pid] - 1);
  if ($q === 0) unset($_SESSION['cart'][$pid]);
  else $_SESSION['cart'][$pid] = $q;
}
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/cart.php'));

