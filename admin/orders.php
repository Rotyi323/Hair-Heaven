<?php
session_start();
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../connect.php';

$mysqli = db();
if (empty($_SESSION['belepve']) || ($_SESSION['role'] ?? '') !== 'owner') {
  http_response_code(403);
  echo 'Hozzáférés megtagadva.';
  exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);

function e($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function post($k, $d = '')
{
  return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d;
}

function audit(mysqli $db, int $userId, string $action, string $entity, ?int $entityId)
{
  try {
    $st = $db->prepare("INSERT INTO audit_log (user_id, action, entity, entity_id) VALUES (?,?,?,?)");
    $st->bind_param('issi', $userId, $action, $entity, $entityId);
    $st->execute();
    $st->close();
  } catch (Throwable $e) {
  }
}

$flash = ['ok' => null, 'err' => null];
try {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
  }
} catch (Throwable $e) {
  $flash['err'] = 'Érvénytelen CSRF token.';
}

$allowedStatuses = ['new', 'confirmed', 'cancelled'];

if ($mysqli && $_SERVER['REQUEST_METHOD'] === 'POST' && !$flash['err']) {
  try {
    if (post('action') === 'order_status_update') {
      $orderId = (int)post('order_id', 0);
      $status = post('status', 'new');

      if ($orderId <= 0) {
        throw new RuntimeException('Hibás rendelés azonosító.');
      }
      if (!in_array($status, $allowedStatuses, true)) {
        throw new RuntimeException('Hibás státusz.');
      }

      $st = $mysqli->prepare("UPDATE orders SET status=? WHERE id=? LIMIT 1");
      $st->bind_param('si', $status, $orderId);
      $st->execute();
      $affected = (int)$st->affected_rows;
      $st->close();

      if ($affected < 0) {
        throw new RuntimeException('A módosítás nem sikerült.');
      }

      audit($mysqli, $userId, 'update', 'orders', $orderId);
      $flash['ok'] = 'Rendelés státusza frissítve.';
    }
  } catch (Throwable $e) {
    $flash['err'] = 'Hiba: ' . $e->getMessage();
  }
}

$orders = [];
if ($mysqli) {
  $res = $mysqli->query("\n    SELECT id, user_id, total_amount, status, customer_name, customer_email, customer_address, created_at\n    FROM orders\n    ORDER BY created_at DESC, id DESC\n    LIMIT 500\n  ");

  while ($row = $res->fetch_assoc()) {
    $row['items'] = [];
    $orders[(int)$row['id']] = $row;
  }

  if (!empty($orders)) {
    $ids = array_keys($orders);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sql = "\n      SELECT order_id, product_name, unit_price, qty\n      FROM order_items\n      WHERE order_id IN ($ph)\n      ORDER BY order_id ASC, id ASC\n    ";
    $st = $mysqli->prepare($sql);
    $st->bind_param($types, ...$ids);
    $st->execute();
    $itemsRes = $st->get_result();

    while ($it = $itemsRes->fetch_assoc()) {
      $oid = (int)$it['order_id'];
      if (isset($orders[$oid])) {
        $orders[$oid]['items'][] = $it;
      }
    }
    $st->close();
  }
}

function status_hu(string $status): string
{
  return match ($status) {
    'new' => 'Új',
    'confirmed' => 'Megerősített',
    'cancelled' => 'Törölt',
    default => $status,
  };
}

function status_badge(string $status): string
{
  return match ($status) {
    'new' => 'text-bg-secondary',
    'confirmed' => 'text-bg-success',
    'cancelled' => 'text-bg-danger',
    default => 'text-bg-secondary',
  };
}
?>
<!doctype html>
<html lang="hu">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Rendelések</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    body {
      background: #faf7ff;
    }

    .card {
      box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
      border: 0;
      border-radius: 16px;
    }

    .admin-tabs {
      border-bottom: 2px solid #efe9ff;
      margin-bottom: 1rem;
    }

    .admin-tabs .nav-link {
      border: 0;
      color: #6c6a75;
      font-weight: 800;
      padding: .7rem 1.1rem;
      border-radius: 12px 12px 0 0;
      background: transparent;
      transition: .18s ease;
    }

    .admin-tabs .nav-link:hover {
      color: #7e3dbf;
    }

    .admin-tabs .nav-link.active {
      color: #fff;
      background: linear-gradient(135deg, #c76df0, #8f4be2);
      box-shadow: 0 8px 18px rgba(143, 75, 226, .25);
    }

    .summary {
      max-width: 420px;
    }

    .summary small {
      color: #6c6a75;
    }
  </style>
</head>

<body>
  <?php $activePage = 'admin';
  include __DIR__ . '/../navbar.php'; ?>

  <div class="container-xxl py-4">
    <h1 class="h3 mb-3">Rendelések kezelése</h1>

    <ul class="nav admin-tabs" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="/admin/index.php#tabProducts">Termékek</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="/admin/index.php#tabServices">Szolgáltatások</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="/admin/stock.php">Készlet</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" href="/admin/treatments.php">Kezelések</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link active" href="/admin/orders.php">Rendelések</a>
      </li>
    </ul>

    <?php if ($flash['ok']): ?>
      <div class="alert alert-success"><?= e($flash['ok']) ?></div>
    <?php endif; ?>
    <?php if ($flash['err']): ?>
      <div class="alert alert-danger"><?= e($flash['err']) ?></div>
    <?php endif; ?>

    <div class="card p-3">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Vevő</th>
              <th>Termékek</th>
              <th class="text-end">Összeg</th>
              <th>Státusz</th>
              <th class="text-nowrap">Dátum</th>
              <th class="text-end">Művelet</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <?php
              $parts = [];
              foreach ($o['items'] as $it) {
                $parts[] = $it['product_name'] . ' x ' . (int)$it['qty'];
              }
              $summary = !empty($parts) ? implode(', ', $parts) : 'Nincsenek tételek';
              if (mb_strlen($summary) > 110) {
                $summary = mb_substr($summary, 0, 107) . '...';
              }
              ?>
              <tr>
                <td class="text-nowrap">#<?= (int)$o['id'] ?></td>
                <td>
                  <div class="fw-semibold"><?= e($o['customer_name']) ?></div>
                  <small class="text-muted d-block"><?= e($o['customer_email']) ?></small>
                  <small class="text-muted d-block"><?= e($o['customer_address']) ?></small>
                </td>
                <td class="summary"><small><?= e($summary) ?></small></td>
                <td class="text-end fw-bold"><?= number_format((float)$o['total_amount'], 0, ',', ' ') ?> Ft</td>
                <td>
                  <span class="badge <?= e(status_badge((string)$o['status'])) ?>"><?= e(status_hu((string)$o['status'])) ?></span>
                </td>
                <td class="text-nowrap"><?= e(date('Y.m.d H:i', strtotime($o['created_at']))) ?></td>
                <td class="text-end">
                  <form method="post" class="d-inline-flex gap-2 align-items-center justify-content-end">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="order_status_update">
                    <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                    <select name="status" class="form-select form-select-sm" style="min-width: 150px;">
                      <option value="new" <?= $o['status'] === 'new' ? 'selected' : '' ?>>Új</option>
                      <option value="confirmed" <?= $o['status'] === 'confirmed' ? 'selected' : '' ?>>Megerősített</option>
                      <option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : '' ?>>Törölt</option>
                    </select>
                    <button class="btn btn-sm btn-outline-dark" type="submit">
                      <i class="fa-solid fa-floppy-disk me-1"></i>Mentés
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="7" class="text-muted">Nincs rendelés.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

