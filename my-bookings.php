<?php
session_start();
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/connect.php';

$cspNonce = $GLOBALS['CSP_NONCE'] ?? '';
$mysqli   = db();

if (empty($_SESSION['belepve']) || empty($_SESSION['user_id'])) {
  header('Location: /login.php');
  exit;
}

$userId = (int)$_SESSION['user_id'];

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$rows = [];

if ($mysqli) {
  // Automatikusan lejárttá tesszük a már elmúlt, még nem lezárt foglalásokat
  $stmt = $mysqli->prepare("
    UPDATE bookings
    SET status = 'expired'
    WHERE user_id = ?
      AND status IN ('pending', 'confirmed')
      AND appointment_datetime < NOW()
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $stmt->close();

  // Lista lekérdezése
  $stmt = $mysqli->prepare("
    SELECT b.id, s.name AS service_name, b.appointment_datetime, b.status
    FROM bookings b
    JOIN services s ON s.id = b.service_id
    WHERE b.user_id = ?
    ORDER BY b.appointment_datetime DESC
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $rows[] = $r;
  $stmt->close();
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Foglalásaim – Hair Heaven</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    .card-lite{
      background:#fff;
      border-radius:14px;
      padding:18px;
      box-shadow:0 10px 30px rgba(0,0,0,.06);
    }
    .badge-expired{
      background:#dc3545 !important;
      color:#fff !important;
    }
  </style>
</head>
<body>

<?php $activePage=''; include __DIR__.'/navbar.php'; ?>

<div class="container my-4">
  <h1 class="page-title mb-3">Foglalásaim</h1>

  <div class="card-lite">
    <?php if (empty($rows)): ?>
      <div class="text-muted">Még nincs foglalásod.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Szolgáltatás</th>
              <th>Időpont</th>
              <th>Státusz</th>
              <th class="text-end">Művelet</th>
            </tr>
          </thead>
          <tbody id="tblBody">
          <?php foreach ($rows as $i => $r): ?>
            <?php
              $statusMap = [
                'pending'   => ['text' => 'Függőben',   'badge' => 'warning',   'extraClass' => ''],
                'confirmed' => ['text' => 'Megerősítve','badge' => 'success',   'extraClass' => ''],
                'cancelled' => ['text' => 'Lemondva',   'badge' => 'secondary', 'extraClass' => ''],
                'expired'   => ['text' => 'Lejárt',     'badge' => '',          'extraClass' => 'badge-expired'],
              ];

              $statusInfo = $statusMap[$r['status']] ?? ['text'=>'Ismeretlen', 'badge'=>'secondary', 'extraClass'=>''];
              $canCancel = in_array($r['status'], ['pending', 'confirmed'], true);
            ?>
            <tr data-id="<?= (int)$r['id'] ?>">
              <td><?= $i + 1 ?></td>
              <td><?= e($r['service_name']) ?></td>
              <td><?= date('Y.m.d. H:i', strtotime($r['appointment_datetime'])) ?></td>
              <td>
                <span class="badge <?= e($statusInfo['extraClass']) ?> <?= $statusInfo['badge'] ? 'text-bg-'.e($statusInfo['badge']) : '' ?>">
                  <?= e($statusInfo['text']) ?>
                </span>
              </td>
              <td class="text-end">
                <?php if ($canCancel): ?>
                  <form class="cancel-form d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="booking_id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="fa-regular fa-calendar-xmark me-1"></i> Lemondom
                    </button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script nonce="<?= e($cspNonce) ?>">
document.querySelectorAll('.cancel-form').forEach(form => {
  form.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    if (!confirm('Biztosan lemondod ezt a foglalást?')) return;

    const btn = form.querySelector('button[type="submit"]');
    const tr  = form.closest('tr');
    const fd  = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Lemondás...';

    try {
      const res = await fetch('/api/booking_cancel.php', { method:'POST', body:fd });
      const data = await res.json();

      if (data && data.ok) {
        tr?.parentNode?.removeChild(tr);
        localStorage.setItem('hh_booking_changed', String(Date.now()));
      } else {
        alert(data?.msg || 'Váratlan hiba.');
      }
    } catch (e) {
      alert('Váratlan hiba.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-regular fa-calendar-xmark me-1"></i> Lemondom';
    }
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
