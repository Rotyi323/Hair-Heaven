<?php
session_start();
require_once __DIR__ . '/../biztonsag.php';


function back_to_cart(){
  header('Location: /kosar.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method Not Allowed'); }
try { csrf_validate(); } catch (Throwable $e) {
  $_SESSION['flash_error'] = 'Biztonsági hiba (CSRF). Próbáld újra.';
  back_to_cart();
}

if (empty($_SESSION['belepve']) || empty($_SESSION['user_id'])) {
  $_SESSION['flash_error'] = 'A kosár használatához jelentkezz be.';
  header('Location: /belepes.php?next=/kosar.php'); exit;
}

$pid = (int)($_POST['product_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 1);

if ($pid <= 0 || $qty < 0) { $_SESSION['flash_error'] = 'Érvénytelen kérés.'; back_to_cart(); }
if (empty($_SESSION['cart'][$pid])) { back_to_cart(); }

if ($qty === 0) { unset($_SESSION['cart'][$pid]); }
else { $_SESSION['cart'][$pid] = max(1, min(99, $qty)); }

$_SESSION['flash_ok'] = 'Kosár frissítve.';
back_to_cart();
