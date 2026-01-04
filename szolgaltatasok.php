<?php
// szolgaltatasok.php – Időpontfoglalás
session_start();
require_once __DIR__ . '/biztonsag.php';
require_once __DIR__ . '/connect.php';

$mysqli   = db(); // mysqli|null
$isLogged = !empty($_SESSION['belepve']);
$userId   = $_SESSION['user_id'] ?? null;

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ——— időslotok generálása: 08:00–16:00, 15 percenként ———
function hh_time_slots(int $startH = 8, int $endH = 16, int $stepMin = 15): array {
  $out = [];
  $start = $startH * 60;       // percekben
  $end   = $endH   * 60;       // percekben (16:00 is benne)
  for ($m = $start; $m <= $end; $m += $stepMin) {
    $h = floor($m / 60);
    $mm= $m % 60;
    $out[] = sprintf('%02d:%02d', $h, $mm);
  }
  return $out;
}
$timeOptions = hh_time_slots(8, 16, 15);

$errors = [];
$success = null;

// ——— Foglalás kezelése ———
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='book') {
  if (function_exists('csrf_validate')) { csrf_validate(); }

  if (!$isLogged || !$userId) {
    $errors[] = 'Foglaláshoz be kell jelentkezned.';
  } elseif (!$mysqli) {
    $errors[] = 'Az adatbázis jelenleg nem elérhető.';
  } else {
    $service_id = (int)($_POST['service_id'] ?? 0);
    $date_raw   = trim((string)($_POST['date'] ?? ''));   // 2026-01-04
    $time_raw   = trim((string)($_POST['time'] ?? ''));   // 08:15 (kiválasztott option)

    if ($service_id <= 0 || $date_raw === '' || $time_raw === '') {
      $errors[] = 'Kérlek add meg a dátumot és az időpontot.';
    } else {
      // időpont összeépítése
      $dt_str = $date_raw . ' ' . $time_raw . ':00';
      $ts = strtotime($dt_str);
      if ($ts === false) {
        $errors[] = 'Hibás időpont formátum.';
      } else {
        // — szerveroldali szabályok —
        // 1) múlt tiltása
        if ($ts <= time()) {
          $errors[] = 'Múltbeli időpontra nem foglalhatsz.';
        }
        // 2) hétfő–péntek
        $dow = (int)date('N', $ts); // 1..7
        if ($dow < 1 || $dow > 5) {
          $errors[] = 'Időpontot csak hétfő és péntek között lehet foglalni.';
        }
        // 3) csak 08:00–16:00
        $h = (int)date('G', $ts);
        $m = (int)date('i', $ts);
        $minutesOfDay = $h*60 + $m;
        if ($minutesOfDay < 8*60 || $minutesOfDay > 16*60) {
          $errors[] = 'Csak 08:00 és 16:00 közötti időpont foglalható.';
        }
        // 4) 15 perces lépés
        if ($m % 15 !== 0) {
          $errors[] = 'Az időpont 15 perces lépésekben adható meg.';
        }
        // 5) a kliens oldali listát megkerülve csak az általunk generált opció engedett
        if (!in_array($time_raw, hh_time_slots(8,16,15), true)) {
          $errors[] = 'Érvénytelen időpont.';
        }

        if (empty($errors)) {
          $appointment = date('Y-m-d H:i:s', $ts);

          try {
            // létező és aktív szolgáltatás
            $stmt = $mysqli->prepare("SELECT id FROM services WHERE id=? AND is_active=1 LIMIT 1");
            $stmt->bind_param('i', $service_id);
            $stmt->execute();
            $okService = (bool)$stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$okService) {
              $errors[] = 'A szolgáltatás nem elérhető.';
            } else {
              // (opció) duplafoglalás ellenőrzés – ugyanarra a szolgáltatásra ugyanabban a percben
              $stmt = $mysqli->prepare("
                SELECT id FROM bookings
                WHERE service_id = ? AND appointment_datetime = ?
                LIMIT 1
              ");
              $stmt->bind_param('is', $service_id, $appointment);
              $stmt->execute();
              $exists = (bool)$stmt->get_result()->fetch_assoc();
              $stmt->close();

              if ($exists) {
                $errors[] = 'Erre az időpontra már van foglalás. Válassz másikat!';
              } else {
                // BESZÚRÁS – FIGYELEM: status = 'pending' (DB-d alapján)
                $status = 'pending';
                $note   = null;
                $stmt = $mysqli->prepare("
                  INSERT INTO bookings (user_id, service_id, appointment_datetime, status, note, created_at)
                  VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param('iisss', $userId, $service_id, $appointment, $status, $note);
                $stmt->execute();
                $stmt->close();

                $success = 'Foglalásod rögzítettük: ' . e(date('Y.m.d. H:i', $ts)) . '.';
              }
            }
          } catch (Throwable $ex) {
            // error_log('Booking error: '.$ex->getMessage());
            $errors[] = 'Váratlan hiba történt a foglalás mentése közben.';
          }
        }
      }
    }
  }
}

// ——— Szolgáltatások betöltése ———
$services = [];
if ($mysqli) {
  try {
    $res = $mysqli->query("
      SELECT id, name, duration_minutes, price, description
      FROM services
      WHERE is_active = 1
      ORDER BY id ASC
    ");
    while ($row = $res->fetch_assoc()) $services[] = $row;
  } catch (Throwable $e) { /* fallback (nem kell itt) */ }
}
if (empty($services)) {
  // minimális fallback
  $services = [
    ['id'=>1,'name'=>'Női hajvágás','duration_minutes'=>45,'price'=>6900,'description'=>'Konzultáció + vágás + szárítás.'],
    ['id'=>2,'name'=>'Férfi hajvágás','duration_minutes'=>30,'price'=>4900,'description'=>'Gyors vágás és formázás.'],
    ['id'=>3,'name'=>'Fejbőrkezelés','duration_minutes'=>40,'price'=>8900,'description'=>'Kíméletes fejbőrápoló kúra.'],
  ];
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hair Heaven – Szolgáltatások</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">

  <style>
    :root{ --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75; --hh-bg:#faf7ff; }
    body{ background:var(--hh-bg); color:var(--hh-dark); }

    .section-title{ font-weight:800; letter-spacing:.4px; }
    .service-card{
      background:#fff; border-radius:16px; padding:20px; height:100%;
      box-shadow:0 10px 30px rgba(0,0,0,.06);
    }
    .service-card h5{ font-weight:800; }
    .service-chip{ color: var(--hh-muted); font-size:.9rem; }
    .form-control, .form-select{ border-radius:10px; }

    .login-alert{ font-weight:700; font-size:1.05rem; }
    .login-alert .link-cta{
      color: var(--hh-primary) !important; font-weight:800; text-decoration:none;
      border-bottom: 2px dotted rgba(199,109,240,.35); padding-bottom:1px;
    }
    .login-alert .link-cta:hover{ text-decoration:underline; border-bottom-color: transparent; }
  </style>
</head>
<body>

<?php $activePage='services'; include __DIR__ . '/navbar.php'; ?>

<div class="container my-4">
  <div class="d-flex align-items-end justify-content-between mb-2">
    <div>
      <h1 class="section-title mb-2">Szolgáltatásaink</h1>
      <div class="text-muted">Foglalj időpontot kedvenc kezelésedre, pár kattintással.</div>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><i class="fa-regular fa-circle-check me-1"></i> <?= e($success) ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><strong>Hoppá!</strong> <?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <?php if (!$isLogged): ?>
    <div class="alert alert-warning login-alert d-flex align-items-center">
      <i class="fa-solid fa-lock me-2"></i>
      Időpont foglalásához <a class="link-cta ms-1 me-1" href="/belepes.php">jelentkezz be</a> vagy
      <a class="link-cta ms-1" href="/regisztracio.php">regisztrálj</a>.
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($services as $s): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="service-card h-100">
          <div class="d-flex justify-content-between align-items-start">
            <h5 class="mb-2"><?= e($s['name']) ?></h5>
            <span class="price-tag fw-bold"><?= number_format((float)$s['price'], 0, ',', ' ') ?> Ft</span>
          </div>
          <div class="service-chip mb-2">
            <i class="fa-regular fa-clock me-1"></i> <?= (int)$s['duration_minutes'] ?> perc
          </div>
          <?php if (!empty($s['description'])): ?>
            <p class="mb-3"><?= e($s['description']) ?></p>
          <?php endif; ?>

          <form method="post" action="/szolgaltatasok.php" class="mt-auto">
            <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
            <input type="hidden" name="action" value="book">
            <input type="hidden" name="service_id" value="<?= (int)$s['id'] ?>">

            <div class="row g-2">
              <div class="col-6">
                <label class="form-label">Dátum</label>
                <input
                  type="date"
                  class="form-control"
                  name="date"
                  min="<?= e(date('Y-m-d')) ?>"
                  <?= !$isLogged?'disabled':'' ?>>
              </div>
              <div class="col-6">
                <label class="form-label">Idő</label>
                <select class="form-select" name="time" <?= !$isLogged?'disabled':'' ?>>
                  <option value="">Válassz…</option>
                  <?php foreach ($timeOptions as $t): ?>
                    <option value="<?= e($t) ?>"><?= e($t) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="d-grid mt-3">
              <button class="btn btn-cta text-white" type="submit" <?= !$isLogged?'disabled':'' ?>>
                <i class="fa-regular fa-calendar-check me-1"></i> Időpontot foglalok
              </button>
            </div>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<footer class="py-4 border-top bg-white">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between">
    <div><strong>Hair Heaven</strong> &middot; Premium hair & scalp care</div>
    <div class="text-muted">© <?= date('Y') ?> Hair Heaven · Minden jog fenntartva</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
