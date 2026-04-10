<?php
session_start();
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/connect.php';

$mysqli = db(); 

// Csak bejelentkezve
if (empty($_SESSION['belepve']) || empty($_SESSION['user_id'])) {
  header('Location: /login.php');
  exit;
}
$userId = (int)$_SESSION['user_id'];

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

//Rendelések lehúzása (orders)
$orders = [];
if ($mysqli) {
  // 1) fej adatok
  $stmt = $mysqli->prepare("
    SELECT id, total_amount, status, customer_name, customer_email, customer_address, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC, id DESC
  ");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $row['items'] = []; // majd feltöltjük a tételekkel
    $orders[(int)$row['id']] = $row;
  }
  $stmt->close();

  if (!empty($orders)) {
    // 2) tételek egyben (order_items), majd csoportosítjuk
    $ids = array_keys($orders);
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sql = "SELECT order_id, product_name, unit_price, qty
            FROM order_items
            WHERE order_id IN ($in)
            ORDER BY order_id ASC, id ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($it = $res->fetch_assoc()) {
      $oid = (int)$it['order_id'];
      if (isset($orders[$oid])) {
        $orders[$oid]['items'][] = $it;
      }
    }
    $stmt->close();
  }
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rendeléseim – Hair Heaven</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    :root{ --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75; --hh-bg:#faf7ff; }
    body{ background:var(--hh-bg); color:var(--hh-dark); }
    .orders-card{ background:#fff; border-radius:16px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .status-badge{ font-weight:700; }
  </style>
</head>
<body>

<?php $activePage = ''; include __DIR__ . '/navbar.php'; ?>

<div class="container my-4">
  <h1 class="page-title mb-3">Rendeléseim</h1>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['flash_success']); ?></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= e($_SESSION['flash_error']); ?></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="orders-card">
    <?php if (empty($orders)): ?>
      <div class="text-center py-5">
        <p class="mb-3">Még nincs leadott rendelésed.</p>
        <a class="btn btn-cta text-white" href="/store.php">
          <i class="fa-solid fa-bag-shopping me-1"></i> Irány az áruház
        </a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th class="text-nowrap">Rendelés #</th>
              <th>Termékek</th>
              <th class="text-end">Összeg</th>
              <th class="text-nowrap">Státusz</th>
              <th class="text-nowrap">Dátum</th>
              <th class="text-nowrap">Szállítási cím</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($orders as $o): ?>
            <?php
              // Rövid összefoglaló a tételekből
              $summary = '—';
              if (!empty($o['items'])) {
                $parts = [];
                foreach ($o['items'] as $it) {
                  $parts[] = $it['product_name'] . ' × ' . (int)$it['qty'];
                }
                // ha túl hosszú, vágjuk
                $summary = implode(', ', $parts);
                if (mb_strlen($summary) > 120) {
                  $summary = mb_substr($summary, 0, 117) . '…';
                }
              }

              // státusz badge
              $status = (string)$o['status'];
              $statusText = match ($status) {
                'new'       => 'Új',
                'confirmed' => 'Megerősített',
                'cancelled' => 'Törölt',
                default     => 'Ismeretlen',
              };
              $badgeClass = match ($status) {
                'confirmed' => 'bg-success',
                'cancelled' => 'bg-danger',
                default     => 'bg-secondary'
              };
            ?>
            <tr>
              <td class="text-nowrap">#<?= (int)$o['id'] ?></td>
              <td><?= e($summary) ?></td>
              <td class="text-end fw-bold"><?= number_format((float)$o['total_amount'], 0, ',', ' ') ?> Ft</td>
              <td><span class="badge status-badge <?= $badgeClass ?>"><?= e($statusText) ?></span></td>
              <td class="text-nowrap"><?= e(date('Y.m.d H:i', strtotime($o['created_at']))) ?></td>
              <td><?= e($o['customer_address'] ?: '—') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

