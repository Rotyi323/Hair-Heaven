<?php
// Hair Heaven – Kosár (session alapú)
session_start();
require_once __DIR__ . '/biztonsag.php';
require_once __DIR__ . '/connect.php';

$mysqli   = db();
$isLogged = !empty($_SESSION['belepve']);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// --- Session kosár: [ product_id => qty, ... ]
$cart  = $_SESSION['cart'] ?? [];
$items = [];
$grand = 0.0;

if (!empty($cart) && $mysqli) {
  // termékek behúzása egyben
  $ids = array_keys($cart);
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));
  $sql = "SELECT id, name, price, image FROM products WHERE id IN ($in)";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param($types, ...$ids);
  $stmt->execute();
  $res = $stmt->get_result();
  $map = [];
  while ($row = $res->fetch_assoc()) $map[(int)$row['id']] = $row;
  $stmt->close();

  foreach ($cart as $pid=>$qty) {
    $qty = max(0, (int)$qty);
    if ($qty === 0) continue;
    $p = $map[(int)$pid] ?? null;
    if (!$p) continue;
    $price = (float)$p['price'];
    $sub   = $price * $qty;
    $items[] = [
      'product_id' => (int)$pid,
      'name'  => $p['name'],
      'price' => $price,
      'qty'   => $qty,
      'sub'   => $sub,
      'image' => $p['image'] ?: '/assets/img/placeholder.png',
    ];
    $grand += $sub;
  }
}

// — Szállítási cím meglétének ellenőrzése (min. 10 karakter)
$hasAddress = false;
$addressVal = '';
if ($isLogged && $mysqli) {
  $stmt = $mysqli->prepare("SELECT address FROM users WHERE id=? LIMIT 1");
  $stmt->bind_param('i', $_SESSION['user_id']);
  $stmt->execute();
  $addressVal = (string)($stmt->get_result()->fetch_assoc()['address'] ?? '');
  $stmt->close();
  $hasAddress = (mb_strlen(trim($addressVal)) >= 10);
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kosár – Hair Heaven</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">

  <style>
    :root{ --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75; --hh-bg:#faf7ff; }
    body{ background:var(--hh-bg); color:var(--hh-dark); }
    .cart-card{ background:#fff; border-radius:16px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .cart-thumb{ width:70px;height:70px; border-radius:10px; object-fit:cover; background:#f7f3ff; border:1px solid #f0eaff; }

    /* mennyiség vezérlő */
    .qty-wrap{ display:flex; align-items:center; gap:.5rem; justify-content:center; }
    .qty-btn{
      min-width: 40px; height:40px; display:inline-flex; align-items:center; justify-content:center;
      border:1px solid #e4e4ea; border-radius:10px; background:#fff;
    }
    .qty-btn:hover{ border-color:#d2cde7; background:#f8f5ff; }
    .qty-input{
      width: 64px; height:40px; text-align:center; font-weight:700;
      border:1px solid #e4e4ea; border-radius:10px; background:#fff;
      -moz-appearance:textfield;
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button{ -webkit-appearance:none; margin:0; }

    .totals-line{ font-weight:800; }
    .btn-cta{ background:var(--hh-primary); border:0; font-weight:700; letter-spacing:.3px; }
    .btn-cta:hover{ filter:brightness(1.05); }
  </style>
</head>
<body>

<?php $activePage = 'cart'; include __DIR__ . '/navbar.php'; ?>

<div class="container my-4">
  <h1 class="page-title mb-3">Kosár</h1>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <?php if (!$isLogged): ?>
    <div class="alert alert-warning">
      A vásárláshoz be kell jelentkezned. <a class="fw-bold" href="/belepes.php">Jelentkezz be</a> vagy <a class="fw-bold" href="/regisztracio.php">regisztrálj</a>!
    </div>
  <?php endif; ?>

  <?php if ($isLogged && !$hasAddress): ?>
    <div class="alert alert-info">
      A <strong>Rendelés leadása</strong> gomb aktiválásához adj meg egy szállítási címet a <a href="/profil.php" class="fw-bold">Profilom</a> oldalon (min. 10 karakter).
    </div>
  <?php endif; ?>

  <div class="cart-card">
    <?php if (empty($items)): ?>
      <div class="text-center py-5">
        <p class="mb-3">A kosarad üres.</p>
        <a href="/aruhaz.php" class="btn btn-outline-dark"><i class="fa-solid fa-arrow-left me-1"></i> Vissza az áruházba</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Termék</th>
              <th class="text-end">Egységár</th>
              <th class="text-center">Mennyiség</th>
              <th class="text-end">Részösszeg</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($items as $it): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <img src="<?= e($it['image']) ?>" class="cart-thumb" alt="">
                  <div>
                    <div class="fw-bold"><?= e($it['name']) ?></div>
                    <div class="text-muted small">#<?= (int)$it['product_id'] ?></div>
                  </div>
                </div>
              </td>
              <td class="text-end"><?= number_format($it['price'], 0, ',', ' ') ?> Ft</td>
              <td class="text-center">
                <div class="qty-wrap">
                  <form method="post" action="/api/cart_dec.php" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int)$it['product_id'] ?>">
                    <button class="qty-btn" type="submit" title="Csökkentés"><i class="fa-solid fa-minus"></i></button>
                  </form>
                  <input class="qty-input" type="number" readonly value="<?= (int)$it['qty'] ?>" aria-label="Mennyiség">
                  <form method="post" action="/api/cart_inc.php" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int)$it['product_id'] ?>">
                    <button class="qty-btn" type="submit" title="Növelés"><i class="fa-solid fa-plus"></i></button>
                  </form>
                </div>
              </td>
              <td class="text-end fw-bold"><?= number_format($it['sub'], 0, ',', ' ') ?> Ft</td>
              <td class="text-end">
                <form method="post" action="/api/cart_remove.php" class="d-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="product_id" value="<?= (int)$it['product_id'] ?>">
                  <button class="btn btn-link text-danger p-0" type="submit" title="Tétel törlése">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-end totals-line">Végösszeg:</td>
              <td class="text-end totals-line"><?= number_format($grand, 0, ',', ' ') ?> Ft</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch gap-2 mt-3">
        <a class="btn btn-outline-dark" href="/aruhaz.php">
          <i class="fa-solid fa-arrow-left me-1"></i> Vásárlás folytatása
        </a>

        <div class="d-flex gap-2">
          <!-- Teljes kosár kiürítése -->
          <form method="post" action="/api/cart_clear.php">
            <?= csrf_field() ?>
            <button class="btn btn-outline-danger" type="submit">
              <i class="fa-solid fa-trash me-1"></i> Kosár kiürítése
            </button>
          </form>

          <?php if ($isLogged && $hasAddress): ?>
            <!-- Rendelés leadása -->
            <form method="post" action="/api/order_place.php">
              <?= csrf_field() ?>
              <button class="btn btn-cta text-white" type="submit" title="Rendelés leadása">
                <i class="fa-solid fa-receipt me-1"></i> Rendelés leadása
              </button>
            </form>
          <?php elseif ($isLogged && !$hasAddress): ?>
            <a class="btn btn-secondary disabled" href="#" tabindex="-1" aria-disabled="true">
              <i class="fa-solid fa-receipt me-1"></i> Rendelés leadása
            </a>
            <a class="btn btn-outline-dark" href="/profil.php" title="Adj meg szállítási címet a Profilom oldalon!">
              <i class="fa-solid fa-location-dot me-1"></i> Szállítási cím megadása
            </a>
          <?php else: ?>
            <a class="btn btn-cta text-white" href="/belepes.php" title="A rendeléshez kérlek jelentkezz be.">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Bejelentkezés a rendeléshez
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
