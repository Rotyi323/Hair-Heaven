<?php
// foglalasaim.php – saját foglalások listája + lemondás
session_start();
require_once __DIR__ . '/biztonsag.php';
require_once __DIR__ . '/connect.php';

$mysqli   = db();
$isLogged = !empty($_SESSION['belepve']);
$userId   = $_SESSION['user_id'] ?? null;

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

if (!$isLogged || !$userId) {
  // csak bejelentkezve
  http_response_code(403);
  ?>
  <!doctype html>
  <html lang="hu"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Foglalásaim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="/assets/hairheaven.css">
  </head><body>
  <?php $activePage=''; include __DIR__ . '/navbar.php'; ?>
  <div class="container my-4">
    <div class="alert alert-warning fw-bold">
      Időpontjaid megtekintéséhez <a href="/belepes.php" class="link-purple fw-bolder">jelentkezz be</a>!
    </div>
  </div>
  </body></html>
  <?php
  exit;
}

$success = null;
$errors  = [];

// Lemondás (soft-cancel: státusz 'cancelled')
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='cancel') {
  if (function_exists('csrf_validate')) csrf_validate();
  $bid = (int)($_POST['booking_id'] ?? 0);

  if ($bid > 0 && $mysqli) {
    try {
      // Ellenőrizzük, hogy a foglalás a bejelentkezett useré-e
      $stmt = $mysqli->prepare("
        SELECT id, appointment_datetime, status
        FROM bookings
        WHERE id=? AND user_id=?
        LIMIT 1
      ");
      $stmt->bind_param('ii', $bid, $userId);
      $stmt->execute();
      $row = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if (!$row) {
        $errors[] = 'A foglalás nem található.';
      } else {
        $ts = strtotime($row['appointment_datetime']);
        if ($ts !== false && $ts <= time()) {
          $errors[] = 'Múltbeli időpontot nem lehet lemondani.';
        } elseif ($row['status'] === 'cancelled') {
          $errors[] = 'A foglalás már le van mondva.';
        } else {
          $stmt = $mysqli->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=?");
          $stmt->bind_param('ii', $bid, $userId);
          $stmt->execute();
          $stmt->close();
          $success = 'A foglalást sikeresen lemondtad.';
        }
      }
    } catch (Throwable $e) {
      // error_log('Cancel error: '.$e->getMessage());
      $errors[] = 'Váratlan hiba történt a lemondás közben.';
    }
  }
}

// Saját foglalások lekérdezése
$bookings = [];
if ($mysqli) {
  $stmt = $mysqli->prepare("
    SELECT b.id, b.appointment_datetime, b.status,
           s.name AS service_name, s.duration_minutes
    FROM bookings b
    JOIN services s ON s.id = b.service_id
    WHERE b.user_id = ?
    ORDER BY b.appointment_datetime DESC
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $bookings[] = $r;
  $stmt->close();
}

function status_badge(string $st): string {
  switch ($st) {
    case 'confirmed': return '<span class="badge bg-success">Visszaigazolt</span>';
    case 'cancelled': return '<span class="badge bg-secondary">Lemondva</span>';
    default:          return '<span class="badge bg-warning text-dark">Folyamatban</span>'; // pending
  }
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hair Heaven – Foglalásaim</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    .page-title{ font-weight:800; letter-spacing:.4px; }
    .table th{ font-weight:700; }
  </style>
</head>
<body>
<?php $activePage=''; include __DIR__ . '/navbar.php'; ?>

<div class="container my-4">
  <h1 class="page-title mb-3">Foglalásaim</h1>

  <?php if ($success): ?>
    <div class="alert alert-success"><i class="fa-regular fa-circle-check me-1"></i> <?= e($success) ?></div>
  <?php endif; ?>
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <?php if (empty($bookings)): ?>
    <div class="alert alert-info">Még nincs foglalásod.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Szolgáltatás</th>
            <th>Időpont</th>
            <th>Időtartam</th>
            <th>Státusz</th>
            <th class="text-end">Művelet</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): 
          $dtTxt = date('Y.m.d. H:i', strtotime($b['appointment_datetime']));
          $isFuture = (strtotime($b['appointment_datetime']) > time());
          $canCancel = $isFuture && $b['status'] !== 'cancelled';
        ?>
          <tr>
            <td><?= (int)$b['id'] ?></td>
            <td><?= e($b['service_name']) ?></td>
            <td><?= e($dtTxt) ?></td>
            <td><?= (int)$b['duration_minutes'] ?> perc</td>
            <td><?= status_badge($b['status']) ?></td>
            <td class="text-end">
              <?php if ($canCancel): ?>
                <form method="post" action="/foglalasaim.php" class="d-inline">
                  <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                  <input type="hidden" name="action" value="cancel">
                  <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit"
                          onclick="return confirm('Biztosan lemondod ezt a foglalást?');">
                    <i class="fa-regular fa-calendar-xmark me-1"></i> Lemondás
                  </button>
                </form>
              <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary" disabled>Lemondás</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
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
