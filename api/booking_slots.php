<?php
// Hair Heaven – API: szabad időpontok listázása
// GET paramok: service_id, date=YYYY-MM-DD
// Válasz: { ok: true, slots: ["08:00","08:15", ...] }

require_once __DIR__ . '/../biztonsag.php';
require_once __DIR__ . '/../connect.php';

header('Content-Type: application/json; charset=utf-8');

$mysqli = db();
if (!$mysqli) {
  echo json_encode(['ok'=>false,'msg'=>'DB hiba','slots'=>[]]); exit;
}

$serviceId = (int)($_GET['service_id'] ?? 0);
$date      = trim((string)($_GET['date'] ?? ''));

// formátum-ellenőrzés
if ($serviceId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  echo json_encode(['ok'=>false,'msg'=>'Hiányzó vagy hibás paraméter','slots'=>[]]); exit;
}

// csak hétfő–péntek
$weekday = (int)date('N', strtotime($date)); // 1..7
if ($weekday > 5) { echo json_encode(['ok'=>true,'slots'=>[]]); exit; }

// 15 perces raszter, 08:00–16:00
function hh_slots_for_day(string $ymd): array {
  $open  = new DateTime("$ymd 08:00:00");
  $close = new DateTime("$ymd 16:00:00");
  $out   = [];
  for ($t = clone $open; $t < $close; $t->modify('+15 minutes')) {
    $out[] = $t->format('H:i');
  }
  return $out;
}

// foglalt (aktív) időpontok a napon
$stmt = $mysqli->prepare("
  SELECT appointment_datetime
  FROM bookings
  WHERE service_id = ?
    AND DATE(appointment_datetime) = ?
    AND status IN ('pending','confirmed')
");
$stmt->bind_param('is', $serviceId, $date);
$stmt->execute();
$res = $stmt->get_result();
$busy = [];
while ($r = $res->fetch_assoc()) {
  $busy[ date('H:i', strtotime($r['appointment_datetime'])) ] = true;
}
$stmt->close();

$all  = hh_slots_for_day($date);
$free = array_values(array_filter($all, fn($h) => !isset($busy[$h])));

echo json_encode(['ok'=>true,'slots'=>$free]);
