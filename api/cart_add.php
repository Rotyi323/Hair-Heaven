<?php
session_start();
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../connect.php';

try { csrf_validate(); } catch (Throwable $e) {
  http_response_code(400); exit('Érvénytelen CSRF token');
}

if (empty($_SESSION['belepve'])) {
  header('Location: /login.php'); exit;
}

$pid = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));

if ($pid <= 0) { header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/store.php')); exit; }

$_SESSION['cart'] ??= [];
$_SESSION['cart'][$pid] = min(99, (int)($_SESSION['cart'][$pid] ?? 0) + $qty);

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/cart.php'));
exit;

