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

$userId    = (int)$_SESSION['user_id'];
$serviceId = (int)($_POST['service_id'] ?? 0);
$date      = trim((string)($_POST['date'] ?? '')); 
$time      = trim((string)($_POST['time'] ?? '')); 

//  H–P 08:00–16:00, 15 percenként
try {
  $dt = new DateTime("$date $time:00");
} catch(Throwable $e){
  echo json_encode(['ok'=>false,'msg'=>'Érvénytelen dátum/idő']); exit;
}
$w = (int)$dt->format('N'); // 1=Hétfő … 7=Vasárnap
$h = (int)$dt->format('H');
$m = (int)$dt->format('i');

if ($w > 5)                    { echo json_encode(['ok'=>false,'msg'=>'Csak hétköznap']); exit; }
if ($h < 8 || $h >= 16)        { echo json_encode(['ok'=>false,'msg'=>'Nyitva tartásban foglalhatsz (08–16)']); exit; }
if ($m % 15 !== 0)             { echo json_encode(['ok'=>false,'msg'=>'Negyedórás időpontok foglalhatók']); exit; }

$slot = $dt->format('Y-m-d H:i:00');

$mysqli->begin_transaction();
try {
  //szolgáltatás+időpont ellenőrzése aktív státuszokra
  $stmt = $mysqli->prepare("
    SELECT id FROM bookings
    WHERE service_id=? AND appointment_datetime=? AND status IN ('pending','confirmed')
    FOR UPDATE
  ");
  $stmt->bind_param('is', $serviceId, $slot);
  $stmt->execute();
  $has = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($has) {
    $mysqli->rollback();
    echo json_encode(['ok'=>false,'msg'=>'Ez az időpont már foglalt.']); exit;
  }

  // beszúrás
  $stmt = $mysqli->prepare("
    INSERT INTO bookings (user_id, service_id, appointment_datetime, status, created_at)
    VALUES (?, ?, ?, 'pending', NOW())
  ");
  $stmt->bind_param('iis', $userId, $serviceId, $slot);
  $stmt->execute();
  $stmt->close();

  $mysqli->commit();
  echo json_encode(['ok'=>true,'msg'=>'Foglalás rögzítve']); exit;

} catch(Throwable $e){
  $mysqli->rollback();
  // error_log('book_create: '.$e->getMessage());
  echo json_encode(['ok'=>false,'msg'=>'Váratlan hiba történt']); exit;
}
