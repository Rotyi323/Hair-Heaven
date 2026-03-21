<?php
// /admin/kezelesek.php
session_start();
require_once __DIR__ . '/../biztonsag.php';
require_once __DIR__ . '/../connect.php';

$mysqli = db();
if (empty($_SESSION['belepve']) || ($_SESSION['role'] ?? '') !== 'owner') {
  http_response_code(403);
  exit('Hozzáférés megtagadva.');
}

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function post($k,$d=''){ return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; }
function abs_img(?string $p): string {
  $p = $p ?: '/assets/img/avatar-placeholder.svg';
  return ($p[0] === '/') ? $p : '/'.ltrim($p, '/');
}
function hu_status(string $status): string {
  return match($status){
    'active'   => 'Aktív',
    'paused'   => 'Szünetel',
    'finished' => 'Lezárt',
    default    => $status
  };
}
function status_badge_class(string $status): string {
  return match($status){
    'active'   => 'st-active',
    'paused'   => 'st-paused',
    'finished' => 'st-finished',
    default    => 'st-paused'
  };
}
function audit(mysqli $db, int $userId, string $action, string $entity, ?int $entityId){
  try{
    $st = $db->prepare("INSERT INTO audit_log (user_id, action, entity, entity_id) VALUES (?,?,?,?)");
    $st->bind_param('issi', $userId, $action, $entity, $entityId);
    $st->execute();
    $st->close();
  } catch(Throwable $e){}
}
function save_treatment_image(array $file): ?string {
  if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
  ];
  $mime = @mime_content_type($file['tmp_name']);
  if (!isset($allowed[$mime])) return null;

  $dir = __DIR__ . '/../uploads/treatments';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);

  $ext  = $allowed[$mime];
  $name = 'treat_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $dest = $dir . '/' . $name;

  if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
  return '/uploads/treatments/' . $name;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$flash = ['ok'=>null,'err'=>null];

try {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_validate();
} catch(Throwable $e) {
  $flash['err'] = 'Érvénytelen CSRF token.';
}

if ($mysqli && $_SERVER['REQUEST_METHOD'] === 'POST' && !$flash['err']) {
  try {
    $action = post('action');

    if ($action === 'create_treatment') {
      $targetUserId = (int)post('user_id', 0);
      $title = post('title');
      $description = post('description');
      $status = post('status', 'active');

      if ($targetUserId <= 0 || $title === '') {
        throw new RuntimeException('Felhasználó és cím kötelező.');
      }

      $st = $mysqli->prepare("
        INSERT INTO user_treatments (user_id, title, description, status, created_by)
        VALUES (?, ?, ?, ?, ?)
      ");
      $st->bind_param('isssi', $targetUserId, $title, $description, $status, $userId);
      $st->execute();
      $newId = (int)$st->insert_id;
      $st->close();

      $mysqli->query("UPDATE users SET has_active_treatment = 1 WHERE id = ".$targetUserId);
      audit($mysqli, $userId, 'insert', 'user_treatments', $newId);
      $flash['ok'] = 'Kezelés létrehozva.';
    }

    if ($action === 'add_product') {
      $treatmentId = (int)post('treatment_id', 0);
      $productId   = (int)post('product_id', 0);
      $usageNote   = post('usage_note');

      if ($treatmentId <= 0 || $productId <= 0) {
        throw new RuntimeException('Kezelés és termék kiválasztása kötelező.');
      }

      $st = $mysqli->prepare("
        INSERT IGNORE INTO user_treatment_products (treatment_id, product_id, usage_note)
        VALUES (?, ?, ?)
      ");
      $st->bind_param('iis', $treatmentId, $productId, $usageNote);
      $st->execute();
      $st->close();

      audit($mysqli, $userId, 'insert', 'user_treatment_products', $treatmentId);
      $flash['ok'] = 'Termék hozzáadva a kezeléshez.';
    }

    if ($action === 'add_entry') {
      $treatmentId = (int)post('treatment_id', 0);
      $title       = post('entry_title');
      $note        = post('entry_note');
      $entryDate   = post('entry_date');

      if ($treatmentId <= 0 || $title === '') {
        throw new RuntimeException('A bejegyzés címe kötelező.');
      }

      $imagePath = null;
      if (!empty($_FILES['entry_image']['name'])) {
        $imagePath = save_treatment_image($_FILES['entry_image']);
      }

      $entryDateSql = $entryDate !== ''
        ? date('Y-m-d H:i:s', strtotime($entryDate))
        : date('Y-m-d H:i:s');

      $st = $mysqli->prepare("
        INSERT INTO user_treatment_entries (treatment_id, title, note, entry_date, image_path, created_by)
        VALUES (?, ?, ?, ?, ?, ?)
      ");
      $st->bind_param('issssi', $treatmentId, $title, $note, $entryDateSql, $imagePath, $userId);
      $st->execute();
      $newId = (int)$st->insert_id;
      $st->close();

      audit($mysqli, $userId, 'insert', 'user_treatment_entries', $newId);
      $flash['ok'] = 'Idővonal-bejegyzés rögzítve.';
    }

    if ($action === 'update_treatment_status') {
      $treatmentId = (int)post('treatment_id', 0);
      $status = post('status', 'active');

      if ($treatmentId <= 0 || !in_array($status, ['active','paused','finished'], true)) {
        throw new RuntimeException('Hibás kezelés vagy állapot.');
      }

      $endedAt = ($status === 'finished') ? date('Y-m-d H:i:s') : null;

      if ($status === 'finished') {
        $st = $mysqli->prepare("UPDATE user_treatments SET status=?, ended_at=? WHERE id=?");
        $st->bind_param('ssi', $status, $endedAt, $treatmentId);
      } else {
        $st = $mysqli->prepare("UPDATE user_treatments SET status=?, ended_at=NULL WHERE id=?");
        $st->bind_param('si', $status, $treatmentId);
      }
      $st->execute();
      $st->close();

      audit($mysqli, $userId, 'update', 'user_treatments', $treatmentId);
      $flash['ok'] = 'Kezelés állapota frissítve.';
    }

  } catch(Throwable $e) {
    $flash['err'] = 'Hiba: '.$e->getMessage();
  }
}

$users = [];
$products = [];
$treatments = [];

if ($mysqli) {
  $r = $mysqli->query("SELECT id, username, email FROM users WHERE role='customer' ORDER BY username");
  while ($row = $r->fetch_assoc()) $users[] = $row;

  $r = $mysqli->query("SELECT id, brand, name FROM products WHERE is_active=1 ORDER BY brand, name");
  while ($row = $r->fetch_assoc()) $products[] = $row;

  $r = $mysqli->query("
    SELECT ut.*, u.username, u.email
    FROM user_treatments ut
    INNER JOIN users u ON u.id = ut.user_id
    ORDER BY FIELD(ut.status,'active','paused','finished'), ut.started_at DESC
  ");

  while ($row = $r->fetch_assoc()) {
    $tid = (int)$row['id'];

    $row['products'] = [];
    $stP = $mysqli->prepare("
      SELECT p.brand, p.name, p.image, utp.usage_note
      FROM user_treatment_products utp
      INNER JOIN products p ON p.id = utp.product_id
      WHERE utp.treatment_id = ?
      ORDER BY p.brand, p.name
    ");
    $stP->bind_param('i', $tid);
    $stP->execute();
    $resP = $stP->get_result();
    while ($p = $resP->fetch_assoc()) {
      $p['image'] = abs_img($p['image']);
      $row['products'][] = $p;
    }
    $stP->close();

    $row['entries'] = [];
    $stE = $mysqli->prepare("
      SELECT *
      FROM user_treatment_entries
      WHERE treatment_id = ?
      ORDER BY entry_date DESC, id DESC
    ");
    $stE->bind_param('i', $tid);
    $stE->execute();
    $resE = $stE->get_result();
    while ($en = $resE->fetch_assoc()) {
      $en['image_path'] = !empty($en['image_path']) ? abs_img($en['image_path']) : null;
      $row['entries'][] = $en;
    }
    $stE->close();

    $treatments[] = $row;
  }
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Kezelések</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    body{ background:#faf7ff; }
    .card{
      border:0;
      border-radius:16px;
      box-shadow:0 10px 30px rgba(0,0,0,.06);
    }
    .admin-tabs{ border-bottom:2px solid #efe9ff; margin-bottom:1rem; }
    .admin-tabs .nav-link{
      border:0;
      color:#6c6a75;
      font-weight:800;
      padding:.7rem 1.1rem;
      border-radius:12px 12px 0 0;
    }
    .admin-tabs .nav-link:hover{ color:#7e3dbf; }
    .admin-tabs .nav-link.active{
      color:#fff;
      background:linear-gradient(135deg,#c76df0,#8f4be2);
      box-shadow:0 8px 18px rgba(143,75,226,.25);
    }
    .treatment-user-btn{
      background:none;
      border:0;
      padding:0;
      color:#7e3dbf;
      font-weight:700;
      text-decoration:none;
    }
    .treatment-user-btn:hover{ text-decoration:underline; }
    .mini-product{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:6px 10px;
      background:#f7f1ff;
      border-radius:999px;
      margin:4px 6px 4px 0;
      font-size:.92rem;
    }
    .mini-product img{
      width:28px;
      height:28px;
      object-fit:cover;
      border-radius:50%;
    }
    .timeline-entry{
      border-left:4px solid #c76df0;
      padding-left:14px;
      margin-bottom:16px;
    }
    .entry-photo{
      width:100%;
      max-width:230px;
      border-radius:14px;
      object-fit:cover;
      box-shadow:0 8px 18px rgba(0,0,0,.10);
      margin-top:10px;
    }
    .st-active{ background:#d9f7da; color:#0a7a18; }
    .st-paused{ background:#fff3cd; color:#8a6500; }
    .st-finished{ background:#e6f0ff; color:#0b4da1; }
  </style>
</head>
<body>

<?php $activePage='admin'; include __DIR__ . '/../navbar.php'; ?>

<div class="container-xxl py-4">
  <h1 class="mb-3">Kezelések</h1>

  <ul class="nav admin-tabs" id="adminTabs" role="tablist">
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
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabTreatments" type="button" role="tab">Kezelések</button>
    </li>
  </ul>

  <?php if ($flash['ok']): ?><div class="alert alert-success"><?= e($flash['ok']) ?></div><?php endif; ?>
  <?php if ($flash['err']): ?><div class="alert alert-danger"><?= e($flash['err']) ?></div><?php endif; ?>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tabTreatments" role="tabpanel">
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="card p-3 mb-4">
            <h4>Új kezelés létrehozása</h4>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="create_treatment">

              <div class="mb-3">
                <label class="form-label">Felhasználó</label>
                <select class="form-select" name="user_id" required>
                  <option value="">Válassz felhasználót</option>
                  <?php foreach ($users as $u): ?>
                    <option value="<?= (int)$u['id'] ?>"><?= e($u['username'].' ('.$u['email'].')') ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Kezelés címe</label>
                <input type="text" class="form-control" name="title" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Részletes leírás</label>
                <textarea class="form-control" name="description" rows="4"></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Állapot</label>
                <select class="form-select" name="status">
                  <option value="active">Aktív</option>
                  <option value="paused">Szünetel</option>
                  <option value="finished">Lezárt</option>
                </select>
              </div>

              <button class="btn btn-cta text-white" type="submit">Mentés</button>
            </form>
          </div>

          <div class="card p-3 mb-4">
            <h4>Termék hozzáadása kezeléshez</h4>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add_product">

              <div class="mb-3">
                <label class="form-label">Kezelés</label>
                <select class="form-select" name="treatment_id" required>
                  <option value="">Válassz kezelést</option>
                  <?php foreach ($treatments as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= e($t['username'].' – '.$t['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Termék</label>
                <select class="form-select" name="product_id" required>
                  <option value="">Válassz terméket</option>
                  <?php foreach ($products as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"><?= e($p['brand'].' – '.$p['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Használati megjegyzés</label>
                <input type="text" class="form-control" name="usage_note" placeholder="pl. heti 2x, esti használat">
              </div>

              <button class="btn btn-cta text-white" type="submit">Hozzáadás</button>
            </form>
          </div>

          <div class="card p-3">
            <h4>Idővonal bejegyzés</h4>
            <form method="post" enctype="multipart/form-data">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add_entry">

              <div class="mb-3">
                <label class="form-label">Kezelés</label>
                <select class="form-select" name="treatment_id" required>
                  <option value="">Válassz kezelést</option>
                  <?php foreach ($treatments as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= e($t['username'].' – '.$t['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Bejegyzés címe</label>
                <input type="text" class="form-control" name="entry_title" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Megjegyzés</label>
                <textarea class="form-control" name="entry_note" rows="3"></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Dátum / idő</label>
                <input type="datetime-local" class="form-control" name="entry_date">
              </div>

              <div class="mb-3">
                <label class="form-label">Kép</label>
                <input type="file" class="form-control" name="entry_image" accept="image/png,image/jpeg,image/webp">
              </div>

              <button class="btn btn-cta text-white" type="submit">Bejegyzés mentése</button>
            </form>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="card p-3">
            <h4>Aktuális kezelések</h4>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Felhasználó</th>
                    <th>Kezelés</th>
                    <th>Állapot</th>
                    <th>Kezdete</th>
                    <th>Művelet</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($treatments as $idx => $t): ?>
                    <tr>
                      <td><?= (int)$t['id'] ?></td>
                      <td>
                        <button class="treatment-user-btn" type="button" data-bs-toggle="collapse" data-bs-target="#trDetails<?= $idx ?>" aria-expanded="false">
                          <?= e($t['username']) ?>
                        </button>
                        <div class="small text-muted"><?= e($t['email']) ?></div>
                      </td>
                      <td><?= e($t['title']) ?></td>
                      <td>
                        <span class="badge <?= e(status_badge_class($t['status'])) ?>">
                          <?= e(hu_status($t['status'])) ?>
                        </span>
                      </td>
                      <td><?= e(date('Y.m.d H:i', strtotime($t['started_at']))) ?></td>
                      <td>
                        <form method="post" class="d-flex gap-2">
                          <?= csrf_field() ?>
                          <input type="hidden" name="action" value="update_treatment_status">
                          <input type="hidden" name="treatment_id" value="<?= (int)$t['id'] ?>">
                          <select name="status" class="form-select form-select-sm">
                            <option value="active"   <?= $t['status']==='active'?'selected':'' ?>>Aktív</option>
                            <option value="paused"   <?= $t['status']==='paused'?'selected':'' ?>>Szünetel</option>
                            <option value="finished" <?= $t['status']==='finished'?'selected':'' ?>>Lezárt</option>
                          </select>
                          <button class="btn btn-sm btn-outline-dark" type="submit">Mentés</button>
                        </form>
                      </td>
                    </tr>

                    <tr class="collapse" id="trDetails<?= $idx ?>">
                      <td colspan="6">
                        <div class="p-3 bg-light rounded-4">
                          <h5 class="mb-3">Részletek</h5>

                          <div class="mb-3">
                            <strong>Leírás:</strong>
                            <div class="text-muted mt-1">
                              <?= !empty($t['description']) ? nl2br(e($t['description'])) : 'Nincs megadva részletes leírás.' ?>
                            </div>
                          </div>

                          <div class="mb-3">
                            <strong>Használt termékek:</strong>
                            <div class="mt-2">
                              <?php if (empty($t['products'])): ?>
                                <div class="text-muted">Még nincs hozzárendelt termék.</div>
                              <?php else: ?>
                                <?php foreach ($t['products'] as $p): ?>
                                  <span class="mini-product">
                                    <img src="<?= e($p['image']) ?>" alt="">
                                    <span>
                                      <?= e($p['brand'].' – '.$p['name']) ?>
                                      <?php if (!empty($p['usage_note'])): ?>
                                        <small class="d-block text-muted"><?= e($p['usage_note']) ?></small>
                                      <?php endif; ?>
                                    </span>
                                  </span>
                                <?php endforeach; ?>
                              <?php endif; ?>
                            </div>
                          </div>

                          <div>
                            <strong>Idővonal / feltöltött képek:</strong>
                            <div class="mt-3">
                              <?php if (empty($t['entries'])): ?>
                                <div class="text-muted">Még nincs idővonal bejegyzés.</div>
                              <?php else: ?>
                                <?php foreach ($t['entries'] as $en): ?>
                                  <div class="timeline-entry">
                                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                      <strong><?= e($en['title']) ?></strong>
                                      <small class="text-muted"><?= e(date('Y.m.d H:i', strtotime($en['entry_date']))) ?></small>
                                    </div>
                                    <?php if (!empty($en['note'])): ?>
                                      <div class="mb-2"><?= nl2br(e($en['note'])) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($en['image_path'])): ?>
                                      <img src="<?= e($en['image_path']) ?>" alt="Feltöltött kép" class="entry-photo">
                                    <?php endif; ?>
                                  </div>
                                <?php endforeach; ?>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>

                  <?php if (empty($treatments)): ?>
                    <tr><td colspan="6" class="text-muted">Még nincs rögzített kezelés.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>