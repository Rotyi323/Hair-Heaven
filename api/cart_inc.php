<?php
session_start();
require_once __DIR__ . '/../biztonsag.php';
try { csrf_validate(); } catch (Throwable $e) { http_response_code(400); exit('Bad CSRF'); }
if (empty($_SESSION['belepve'])) { header('Location: /belepes.php'); exit; }

$pid = (int)($_POST['product_id'] ?? 0);
if ($pid > 0 && isset($_SESSION['cart'][$pid])) {
  $_SESSION['cart'][$pid] = min(99, (int)$_SESSION['cart'][$pid] + 1);
}
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/kosar.php'));
