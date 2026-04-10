<?php
session_start();
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../connect.php';

header('Content-Type: application/json; charset=utf-8');

function out($ok, $slots = [], $msg = '') {
  echo json_encode([
    'ok'    => $ok,
    'slots' => $slots,
    'msg'   => $msg,
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$mysqli = db();
if (!$mysqli) {
  out(false, [], 'Adatbázis hiba.');
}

$serviceId = (int)($_GET['service_id'] ?? 0);
$date      = trim((string)($_GET['date'] ?? ''));

if ($serviceId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  out(false, [], 'Hibás paraméter.');
}

// hétvége kizárása
$dayTs = strtotime($date . ' 00:00:00');
if ($dayTs === false) {
  out(false, [], 'Hibás dátum.');
}

$todayStartTs = strtotime(date('Y-m-d') . ' 00:00:00');
if ($dayTs < $todayStartTs) {
  out(true, []);
}

$now = new DateTime();
$isToday = ($date === $now->format('Y-m-d'));
$sameDayCutoff = (clone $now)->setTime(15, 45, 0);
if ($isToday && $now >= $sameDayCutoff) {
  out(true, []);
}

$weekday = (int)date('N', $dayTs); // 6=szombat, 7=vasárnap
if ($weekday >= 6) {
  out(true, []);
}

// Szolgáltatás időtartama
$stmt = $mysqli->prepare("SELECT duration_minutes FROM services WHERE id = ? AND is_active = 1 LIMIT 1");
$stmt->bind_param('i', $serviceId);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$service) {
  out(false, [], 'A szolgáltatás nem található.');
}

$duration = (int)$service['duration_minutes'];
if ($duration <= 0) $duration = 30;

// Nyitvatartási idő foglalható kezdőpontokhoz
$openTime  = '08:00';
$closeTime = '16:00';

// 15 perces osztás
$stepMinutes = 15;

// Foglalt időpontok az adott napra
$stmt = $mysqli->prepare("
  SELECT appointment_datetime
  FROM bookings
  WHERE service_id = ?
    AND DATE(appointment_datetime) = ?
    AND status IN ('pending', 'confirmed')
");
$stmt->bind_param('is', $serviceId, $date);
$stmt->execute();
$res = $stmt->get_result();

$busy = [];
while ($row = $res->fetch_assoc()) {
  $start = strtotime($row['appointment_datetime']);
  if ($start === false) continue;
  $end = $start + ($duration * 60);
  $busy[] = [$start, $end];
}
$stmt->close();

$slots = [];
$dayStartTs = strtotime($date . ' ' . $openTime . ':00');
$dayEndTs   = strtotime($date . ' ' . $closeTime . ':00');

$nowTs = time();
$isToday = ($date === date('Y-m-d'));

for ($ts = $dayStartTs; $ts <= $dayEndTs; $ts += $stepMinutes * 60) {
  $slotEnd = $ts + ($duration * 60);

  // ne lógjon túl zárás után
  if ($slotEnd > strtotime($date . ' ' . $closeTime . ':00')) {
    continue;
  }

  // mai napból a múltbeli időpontokat kizárjuk
  if ($isToday && $ts <= $nowTs) {
    continue;
  }

  $overlap = false;
  foreach ($busy as [$bStart, $bEnd]) {
    if ($ts < $bEnd && $slotEnd > $bStart) {
      $overlap = true;
      break;
    }
  }

  if (!$overlap) {
    $slots[] = date('H:i', $ts);
  }
}

out(true, $slots);
