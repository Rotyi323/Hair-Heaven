<?php
session_start();
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/connect.php';

$mysqli = db();
if (empty($_SESSION['belepve']) || empty($_SESSION['user_id'])) {
  header('Location: /login.php');
  exit;
}

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function abs_img(?string $p): string {
  $p = $p ?: '/assets/img/avatar-placeholder.svg';
  return ($p[0] === '/') ? $p : '/'.ltrim($p, '/');
}

$userId = (int)$_SESSION['user_id'];

$treatments = [];
if ($mysqli) {
  $sql = "SELECT *
          FROM user_treatments
          WHERE user_id = ?
          ORDER BY FIELD(status,'active','paused','finished'), started_at DESC";
  $st = $mysqli->prepare($sql);
  $st->bind_param('i', $userId);
  $st->execute();
  $res = $st->get_result();

  while ($tr = $res->fetch_assoc()) {
    $trId = (int)$tr['id'];

    $tr['products'] = [];
    $st2 = $mysqli->prepare("
      SELECT p.name, p.brand, p.image, utp.usage_note
      FROM user_treatment_products utp
      INNER JOIN products p ON p.id = utp.product_id
      WHERE utp.treatment_id = ?
      ORDER BY p.brand, p.name
    ");
    $st2->bind_param('i', $trId);
    $st2->execute();
    $r2 = $st2->get_result();
    while ($p = $r2->fetch_assoc()) {
      $p['image'] = abs_img($p['image']);
      $tr['products'][] = $p;
    }
    $st2->close();

    $tr['entries'] = [];
    $st3 = $mysqli->prepare("
      SELECT *
      FROM user_treatment_entries
      WHERE treatment_id = ?
      ORDER BY entry_date DESC, id DESC
    ");
    $st3->bind_param('i', $trId);
    $st3->execute();
    $r3 = $st3->get_result();
    while ($en = $r3->fetch_assoc()) {
      $en['image_path'] = !empty($en['image_path']) ? abs_img($en['image_path']) : null;
      $tr['entries'][] = $en;
    }
    $st3->close();

    $treatments[] = $tr;
  }
  $st->close();
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kezeléseim – Hair Heaven</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    body{ background:#faf7ff; }
    .page-card{ background:#fff; border-radius:18px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .timeline{ position:relative; margin-left:14px; }
    .timeline::before{
      content:""; position:absolute; left:14px; top:0; bottom:0; width:3px;
      background:linear-gradient(180deg,#c76df0,#ead7ff);
      border-radius:99px;
    }
    .timeline-item{
      position:relative; padding-left:56px; margin-bottom:22px;
    }
    .timeline-dot{
      position:absolute; left:3px; top:6px; width:24px; height:24px; border-radius:50%;
      background:#c76df0; border:4px solid #fff; box-shadow:0 4px 14px rgba(199,109,240,.3);
    }
    .timeline-card{
      background:#fff; border:1px solid #eee6ff; border-radius:16px; padding:16px;
      box-shadow:0 8px 24px rgba(0,0,0,.04);
    }
    .entry-photo{
      width:100%; max-width:280px; border-radius:14px; object-fit:cover; box-shadow:0 8px 18px rgba(0,0,0,.10);
    }
    .product-pill{
      display:inline-flex; align-items:center; gap:10px; padding:8px 12px; border-radius:999px;
      background:#f7f1ff; margin:4px 6px 4px 0;
    }
    .product-pill img{
      width:34px; height:34px; object-fit:cover; border-radius:50%;
    }
    .status-badge.active{ background:#d9f7da; color:#0a7a18; }
    .status-badge.paused{ background:#fff3cd; color:#8a6500; }
    .status-badge.finished{ background:#e6f0ff; color:#0b4da1; }
  </style>
</head>
<body>

<?php $activePage = ''; include __DIR__ . '/navbar.php'; ?>

<div class="container py-4">
  <h1 class="mb-4">Kezeléseim</h1>

  <?php if (empty($treatments)): ?>
    <div class="page-card">
      <div class="text-muted">Jelenleg még nincs hozzád rendelt kezelés.</div>
    </div>
  <?php else: ?>
    <?php foreach ($treatments as $tr): ?>
      <div class="page-card mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
          <div>
            <h3 class="mb-1"><?= e($tr['title']) ?></h3>
            <?php if (!empty($tr['description'])): ?>
              <div class="text-muted"><?= e($tr['description']) ?></div>
            <?php endif; ?>
          </div>
          <div>
            <span class="badge status-badge <?= e($tr['status']) ?>">
              <?= $tr['status']==='active' ? 'Aktív' : ($tr['status']==='paused' ? 'Szünetel' : 'Lezárt') ?>
            </span>
          </div>
        </div>

        <div class="mb-4">
          <strong>Használt termékek</strong>
          <div class="mt-2">
            <?php foreach ($tr['products'] as $p): ?>
              <span class="product-pill">
                <img src="<?= e($p['image']) ?>" alt="">
                <span>
                  <strong><?= e($p['brand'].' – '.$p['name']) ?></strong>
                  <?php if (!empty($p['usage_note'])): ?>
                    <small class="d-block text-muted"><?= e($p['usage_note']) ?></small>
                  <?php endif; ?>
                </span>
              </span>
            <?php endforeach; ?>
          </div>
        </div>

        <div>
          <strong>Haladási idővonal</strong>
          <div class="timeline mt-3">
            <?php if (empty($tr['entries'])): ?>
              <div class="text-muted">Ehhez a kezeléshez még nincs rögzített bejegyzés.</div>
            <?php else: ?>
              <?php foreach ($tr['entries'] as $en): ?>
                <div class="timeline-item">
                  <div class="timeline-dot"></div>
                  <div class="timeline-card">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                      <strong><?= e($en['title']) ?></strong>
                      <small class="text-muted"><?= e(date('Y.m.d H:i', strtotime($en['entry_date']))) ?></small>
                    </div>
                    <?php if (!empty($en['note'])): ?>
                      <p class="mb-3"><?= nl2br(e($en['note'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($en['image_path'])): ?>
                      <img src="<?= e($en['image_path']) ?>" alt="Kezelési fotó" class="entry-photo">
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
