<?php
require_once __DIR__.'/../biztonsag.php';
session_start();
require_once __DIR__.'/../connect.php';

header('Content-Type: application/json');

if (empty($_SESSION['belepve']) || empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'msg'=>'Jelentkezz be!']); exit;
}
csrf_validate();

$mysqli = db();
if (!$mysqli) { echo json_encode(['ok'=>false,'msg'=>'DB hiba']); exit; }

$bookingId = (int)($_POST['booking_id'] ?? 0);
$userId    = (int)$_SESSION['user_id'];
$isOwner   = (!empty($_SESSION['role']) && $_SESSION['role']==='owner'); // ha van tulaj/admin

try {
  // jogosultság: saját vagy owner
  if ($isOwner) {
    $stmt = $mysqli->prepare("UPDATE bookings SET status='cancelled' WHERE id=?");
    $stmt->bind_param('i', $bookingId);
  } else {
    $stmt = $mysqli->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $bookingId, $userId);
  }
  $stmt->execute();
  $rows = $stmt->affected_rows;
  $stmt->close();

  if ($rows < 1) {
    echo json_encode(['ok'=>false,'msg'=>'Nem található vagy nem lemondható foglalás']); exit;
  }

  echo json_encode(['ok'=>true,'msg'=>'Foglalás lemondva']); exit;

} catch(Throwable $e){
  // error_log('cancel: '.$e->getMessage());
  echo json_encode(['ok'=>false,'msg'=>'Váratlan hiba történt']); exit;
}
