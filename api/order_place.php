<?php
// /api/order_place.php – rendelés mentése a DB-be a session kosárból
session_start();
require_once __DIR__ . '/../biztonsag.php';
require_once __DIR__ . '/../connect.php';

// CSRF
try { csrf_validate(); } catch (Throwable $e) {
  http_response_code(400); exit('Érvénytelen CSRF token');
}

// Auth
if (empty($_SESSION['belepve']) || empty($_SESSION['user_id'])) {
  header('Location: /belepes.php'); exit;
}

// Kosár ellenőrzés
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
  $_SESSION['flash_error'] = 'A kosár üres, nincs mit leadni.';
  header('Location: /kosar.php'); exit;
}

$mysqli = db();
if (!$mysqli) {
  $_SESSION['flash_error'] = 'Adatbázis hiba. Kérlek próbáld meg újra később.';
  header('Location: /kosar.php'); exit;
}

$userId = (int)$_SESSION['user_id'];

// 0) Felhasználó adatai + kötelező cím ellenőrzése
$stmt = $mysqli->prepare("SELECT username, email, address FROM users WHERE id=? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$u) {
  $_SESSION['flash_error'] = 'Felhasználó nem található.';
  header('Location: /kosar.php'); exit;
}

$username = (string)($u['username'] ?? '');
$email    = (string)($u['email'] ?? '');
$address  = trim((string)($u['address'] ?? ''));

// Kötelező cím – min. 10 karakter
if (mb_strlen($address) < 10) {
  $_SESSION['flash_error'] = 'Rendelés leadásához előbb add meg a szállítási címed (min. 10 karakter) a Profilom oldalon.';
  header('Location: /profil.php'); exit;
}

// 1) Termékek behúzása, összegzés
$ids = array_keys($cart);
if (empty($ids)) {
  $_SESSION['flash_error'] = 'A kosár üres, nincs mit leadni.';
  header('Location: /kosar.php'); exit;
}

$inPlaceholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$sql = "SELECT id, name, price FROM products WHERE id IN ($inPlaceholders)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$res = $stmt->get_result();

$map = [];
while ($row = $res->fetch_assoc()) {
  $map[(int)$row['id']] = [
    'name'  => (string)$row['name'],
    'price' => (float)$row['price'],
  ];
}
$stmt->close();

$items = [];
$total = 0.0;

foreach ($cart as $pid => $qty) {
  $pid = (int)$pid;
  $qty = max(1, (int)$qty);
  if (!isset($map[$pid])) continue;

  $name  = $map[$pid]['name'];
  $price = $map[$pid]['price'];
  $sub   = $price * $qty;

  $items[] = [
    'product_id'   => $pid,
    'product_name' => $name,
    'unit_price'   => $price,
    'qty'          => $qty
  ];
  $total += $sub;
}

if (empty($items)) {
  $_SESSION['flash_error'] = 'A kosárban lévő termékek időközben eltűntek.';
  header('Location: /kosar.php'); exit;
}

// 2) Mentés tranzakcióban
$mysqli->begin_transaction();
try {
  // orders
  $sqlO = "INSERT INTO orders (user_id, total_amount, status, customer_name, customer_email, customer_address, created_at)
           VALUES (?, ?, 'new', ?, ?, ?, NOW())";
  $stmt = $mysqli->prepare($sqlO);
  $stmt->bind_param('idsss', $userId, $total, $username, $email, $address);
  $stmt->execute();
  $orderId = (int)$stmt->insert_id;
  $stmt->close();

  // order_items
  $sqlI = "INSERT INTO order_items (order_id, product_id, product_name, unit_price, qty)
           VALUES (?, ?, ?, ?, ?)";
  $stmt = $mysqli->prepare($sqlI);
  foreach ($items as $it) {
    $stmt->bind_param(
      'iisdi',
      $orderId,
      $it['product_id'],
      $it['product_name'],
      $it['unit_price'],
      $it['qty']
    );
    $stmt->execute();
  }
  $stmt->close();

  // commit
  $mysqli->commit();

  // 3) Kosár ürítése + flash + átirányítás
  unset($_SESSION['cart']);
  $_SESSION['flash_success'] = "Rendelés sikeresen leadva (#{$orderId}).";
  header('Location: /rendeleseim.php'); exit;

} catch (Throwable $e) {
  $mysqli->rollback();
  // error_log('order_place error: '.$e->getMessage());
  $_SESSION['flash_error'] = 'Váratlan hiba történt a rendelés mentése közben.';
  header('Location: /kosar.php'); exit;
}
