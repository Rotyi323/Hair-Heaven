<?php
session_start();
require_once __DIR__ . '/../biztonsag.php';
require_once __DIR__ . '/../connect.php';

$mysqli = db();

// Jogosultság
if (empty($_SESSION['belepve']) || ($_SESSION['role'] ?? '') !== 'owner') {
  http_response_code(403);
  echo 'Hozzáférés megtagadva.'; exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);

// Helper
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function abs_img(?string $p): string {
  $p = $p ?: '/assets/img/placeholder.png';
  return ($p[0] === '/') ? $p : '/'.ltrim($p,'/');
}
function post($k,$d=''){ return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; }
function post_bool($k){ return isset($_POST[$k]) && ($_POST[$k]==='1' || $_POST[$k]==='on'); }

// biztonságos feltöltés (termék kép)
function save_product_image(array $file): ?string {
  if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  $mime = @mime_content_type($file['tmp_name']);
  if (!isset($allowed[$mime])) return null;
  $ext = $allowed[$mime];
  $dir = __DIR__ . '/../uploads/products';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  $name = 'p_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.'.$ext;
  $dest = $dir.'/'.$name;
  if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
  return '/uploads/products/'.$name;
}

// audit log
function audit(mysqli $db, int $userId, string $action, string $entity, ?int $entityId){
  try{
    $sql = "INSERT INTO audit_log (user_id, action, entity, entity_id) VALUES (?,?,?,?)";
    $st = $db->prepare($sql);
    $st->bind_param('issi', $userId, $action, $entity, $entityId);
    $st->execute();
  }catch(Throwable $e){}
}

// Akciók
$flash = ['ok'=>null,'err'=>null];
try {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_validate();
} catch(Throwable $e){
  $flash['err'] = 'Érvénytelen CSRF token.';
}

if ($mysqli && $_SERVER['REQUEST_METHOD']==='POST' && !$flash['err']) {
  try {
    // TERMÉK mentés
    if (post('action') === 'product_save') {
      $id          = (int)post('id', 0);
      $brand       = post('brand');
      $name        = post('name');
      $type        = post('type','other');
      $description = post('description');
      $price       = (float)str_replace([',',' '], ['.',''], post('price','0'));
      $is_active   = post_bool('is_active') ? 1 : 0;
      $is_featured = post_bool('is_featured') ? 1 : 0;

      if ($name==='') throw new RuntimeException('A név kötelező.');
      if (!in_array($type, ['shampoo','conditioner','mask','treatment','styling','other'], true))
        throw new RuntimeException('Hibás típus.');
      if ($price < 0) throw new RuntimeException('Az ár nem lehet negatív.');

      $imgPath = (!empty($_FILES['image'])) ? save_product_image($_FILES['image']) : null;

      if ($id > 0) {
        if ($imgPath) {
          $sql = "UPDATE products SET brand=?, name=?, type=?, description=?, price=?, image=?, is_active=?, is_featured=? WHERE id=?";
          $st  = $mysqli->prepare($sql);
          $st->bind_param('ssssdsiii', $brand,$name,$type,$description,$price,$imgPath,$is_active,$is_featured,$id);
        } else {
          $sql = "UPDATE products SET brand=?, name=?, type=?, description=?, price=?, is_active=?, is_featured=? WHERE id=?";
          $st  = $mysqli->prepare($sql);
          $st->bind_param('ssssdiii', $brand,$name,$type,$description,$price,$is_active,$is_featured,$id);
        }
        $st->execute();
        $flash['ok'] = 'Termék frissítve.';
        audit($mysqli,$userId,'update','products',$id);
      } else {
        $sql = "INSERT INTO products (brand,name,type,description,price,image,is_active,is_featured) VALUES (?,?,?,?,?,?,?,?)";
        $st  = $mysqli->prepare($sql);
        $imgPath = $imgPath ?: '/assets/img/placeholder.png';
        $st->bind_param('ssssdsii',$brand,$name,$type,$description,$price,$imgPath,$is_active,$is_featured);
        $st->execute();
        $flash['ok'] = 'Új termék hozzáadva.';
        audit($mysqli,$userId,'insert','products',(int)$st->insert_id);
      }
    }

    // TERMÉK törlés
    if (post('action') === 'product_delete') {
      $id = (int)post('id',0);
      if ($id>0) {
        $st = $mysqli->prepare("DELETE FROM products WHERE id=?");
        $st->bind_param('i',$id); $st->execute();
        if ($st->affected_rows>0) {
          $flash['ok'] = 'Termék törölve.';
          audit($mysqli,$userId,'delete','products',$id);
        } else $flash['err']='A termék nem törölhető.';
      }
    }

    // SZOLGÁLTATÁS mentés
    if (post('action') === 'service_save') {
      $id       = (int)post('id',0);
      $name     = post('name');
      $minutes  = (int)post('duration_minutes', 0);
      $price    = (float)str_replace([',',' '], ['.',''], post('price','0'));
      $desc     = post('description');
      $active   = post_bool('is_active') ? 1 : 0;

      if ($name==='' || $minutes<=0 || $price<0) throw new RuntimeException('Hiányzó vagy hibás mezők.');

      if ($id>0) {
        $sql = "UPDATE services SET name=?, duration_minutes=?, price=?, description=?, is_active=? WHERE id=?";
        $st  = $mysqli->prepare($sql);
        $st->bind_param('sidsii', $name,$minutes,$price,$desc,$active,$id);
        $st->execute();
        $flash['ok'] = 'Szolgáltatás frissítve.';
        audit($mysqli,$userId,'update','services',$id);
      } else {
        $sql = "INSERT INTO services (name,duration_minutes,price,description,is_active) VALUES (?,?,?,?,?)";
        $st  = $mysqli->prepare($sql);
        $st->bind_param('sidsi', $name,$minutes,$price,$desc,$active);
        $st->execute();
        $flash['ok'] = 'Új szolgáltatás hozzáadva.';
        audit($mysqli,$userId,'insert','services',(int)$st->insert_id);
      }
    }

    // SZOLGÁLTATÁS törlés
    if (post('action') === 'service_delete') {
      $id = (int)post('id',0);
      if ($id>0) {
        $st=$mysqli->prepare("DELETE FROM services WHERE id=?");
        $st->bind_param('i',$id); $st->execute();
        if ($st->affected_rows>0) { $flash['ok']='Szolgáltatás törölve.'; audit($mysqli,$userId,'delete','services',$id); }
        else $flash['err']='A szolgáltatás nem törölhető.';
      }
    }
  } catch (Throwable $ex) {
    $flash['err'] = 'Hiba: '.$ex->getMessage();
  }
}

// Listák
$products = [];
$services = [];
if ($mysqli) {
  $r = $mysqli->query("SELECT id,brand,name,type,description,price,image,is_active,is_featured FROM products ORDER BY id DESC LIMIT 300");
  while ($row = $r->fetch_assoc()) {
    $row['image'] = abs_img($row['image']);
    $products[] = $row;
  }
  $r = $mysqli->query("SELECT id,name,duration_minutes,price,description,is_active FROM services ORDER BY id DESC LIMIT 300");
  while ($row = $r->fetch_assoc()) $services[] = $row;
}

// Enum opciók
$types = ['shampoo'=>'Sampon','conditioner'=>'Balzsam','mask'=>'Maszk','treatment'=>'Kezelés','styling'=>'Styling','other'=>'Egyéb'];
?>
<!doctype html>
<html lang="hu">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin – Hair Heaven</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="/assets/hairheaven.css">
<style>
  :root{
    --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75; --hh-bg:#faf7ff;
    --card-radius:16px;
  }
  body{ background:var(--hh-bg); font-size:1.06rem; color:var(--hh-dark); }
  .container-xxl{ max-width:1700px; }
  .card{ box-shadow:0 10px 30px rgba(0,0,0,.06); border:0; border-radius:var(--card-radius); }
  .card h5{ font-weight:800; }
  .table td,.table th{ vertical-align:middle; }
  .badge-on{ background:#d9f7da; color:#0a7a18; }
  .badge-off{ background:#ffeaea; color:#b20b0b; }
  .thumb{ width:100px; height:100px; object-fit:cover; border-radius:12px; border:1.6px solid #000000; background:#fff; }
  #prodTable th:nth-child(4), #prodTable td:nth-child(4){ min-width:120px; }
  #prodTable th:nth-child(5), #prodTable td:nth-child(5){ min-width:120px; }
  .img-preview{ width:100%; max-width:370px; height:370px; object-fit:cover; border-radius:16px; border:1px solid #eee; background:#fff; }

  .switch-wrap .form-check-input{
    width:3.2rem; height:1.7rem; cursor:pointer;
    background-color:#ece7ff; border-color:#ded4ff;
  }
  .switch-wrap .form-check-input:checked{
    background-color:var(--hh-primary); border-color:var(--hh-primary);
  }
  .switch-wrap .form-check-input:focus{ box-shadow:0 0 0 .15rem rgba(199,109,240,.25); }
  .switch-row{ display:flex; align-items:center; gap:.65rem; }
  .switch-row + .switch-row{ margin-top:.35rem; }
  .switch-row label{ margin:0 0 0 .25rem; font-weight:600; min-width:72px; }

  .admin-tabs{ border-bottom:2px solid #efe9ff; }
  .admin-tabs .nav-link{
    border:0;
    color:var(--hh-muted);
    font-weight:800;
    padding:.7rem 1.1rem;
    border-radius:12px 12px 0 0;
    background:transparent;
    transition:.18s ease;
  }
  .admin-tabs .nav-link:hover{ color:#7e3dbf; }
  .admin-tabs .nav-link.active{
    color:#fff;
    background:linear-gradient(135deg,#c76df0,#8f4be2);
    box-shadow:0 8px 18px rgba(143,75,226,.25);
  }
</style>
</head>
<body>

<?php $activePage = ''; include __DIR__ . '/../navbar.php'; ?>
<div class="container-xxl py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Admin felület</h1>
  </div>

  <?php if ($flash['ok']): ?><div class="alert alert-success"><?= e($flash['ok']) ?></div><?php endif; ?>
  <?php if ($flash['err']): ?><div class="alert alert-danger"><?= e($flash['err']) ?></div><?php endif; ?>

  <ul class="nav admin-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabProducts" type="button" role="tab">Termékek</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabServices" type="button" role="tab">Szolgáltatások</button>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" href="/admin/stock.php">Készlet</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" href="/admin/kezelesek.php">Kezelések</a>
    </li>
  </ul>

  <div class="tab-content mt-3">

    <div class="tab-pane fade show active" id="tabProducts" role="tabpanel">
      <div class="row g-3">

        <div class="col-xxl-7 col-lg-7">
          <div class="card p-3">
            <h5 class="mb-3">Terméklista</h5>
            <div class="table-responsive">
              <table class="table align-middle" id="prodTable">
                <thead class="table-light">
                <tr>
                  <th>#</th><th>Kép</th><th>Márka / Név</th><th>Típus</th><th class="text-end">Ár</th><th>Állapot</th><th class="text-end">Műveletek</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                  <tr data-id="<?= (int)$p['id'] ?>">
                    <td><?= (int)$p['id'] ?></td>
                    <td><img src="<?= e($p['image']) ?>" class="thumb" alt=""></td>
                    <td>
                      <strong><?= e($p['brand'].' – '.$p['name']) ?></strong>
                      <?php if (!empty($p['description'])): ?>
                        <div class="text-muted small"><?= e(mb_strimwidth($p['description'],0,120,'…','UTF-8')) ?></div>
                      <?php endif; ?>
                    </td>
                    <td><?= e($types[$p['type']] ?? $p['type']) ?></td>
                    <td class="text-end"><?= number_format((float)$p['price'],0,',',' ') ?> Ft</td>
                    <td>
                      <span class="badge <?= $p['is_active']?'badge-on':'badge-off' ?>"><?= $p['is_active']?'Aktív':'Inaktív' ?></span>
                      <?php if ($p['is_featured']): ?><span class="badge text-bg-warning">Kiemelt</span><?php endif; ?>
                    </td>
                    <td class="text-end">
                      <div class="d-inline-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary js-edit-product"
                                title="Szerkesztés"
                                data-product='<?= e(json_encode($p)) ?>'>
                          <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <form method="post" class="d-inline js-del-product" onsubmit="return confirm('Biztosan törlöd?');">
                          <?= csrf_field() ?>
                          <input type="hidden" name="action" value="product_delete">
                          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                          <button class="btn btn-sm btn-outline-danger" title="Törlés"><i class="fa-solid fa-trash"></i></button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                  <tr><td colspan="7" class="text-muted">Nincs termék.</td></tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-xxl-5 col-lg-5">
          <div class="card p-3">
            <h5 class="mb-3" id="pfTitle">Új termék</h5>
            <form method="post" enctype="multipart/form-data" id="productForm">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="product_save">
              <input type="hidden" name="id" id="p_id" value="">

              <div class="row g-4">
                <div class="col-md-6">
                  <div class="mb-2">
                    <label class="form-label">Márka</label>
                    <input class="form-control" name="brand" id="p_brand">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Név *</label>
                    <input class="form-control" name="name" id="p_name" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Típus</label>
                    <select class="form-select" name="type" id="p_type">
                      <?php foreach ($types as $k=>$v): ?>
                        <option value="<?= e($k) ?>"><?= e($v) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Leírás</label>
                    <textarea class="form-control" rows="4" name="description" id="p_desc"></textarea>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Ár (Ft) *</label>
                    <input type="number" min="0" step="1" class="form-control" name="price" id="p_price" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Kép (jpg/png/webp)</label>
                    <input type="file" class="form-control" name="image" id="p_image" accept="image/*">
                  </div>

                  <div class="mt-3">
                    <div class="switch-row switch-wrap form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="p_active" name="is_active" checked>
                      <label class="form-check-label" for="p_active">Aktív</label>
                    </div>
                    <div class="switch-row switch-wrap form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="p_feat" name="is_featured">
                      <label class="form-check-label" for="p_feat">Kiemelt</label>
                    </div>
                  </div>

                  <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i> Mentés</button>
                    <button class="btn btn-outline-secondary" type="button" id="btnResetProduct"><i class="fa-solid fa-rotate-left me-1"></i> Újrakezdem</button>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Előnézet</label>
                  <div class="text-center">
                    <img id="p_preview" class="img-preview" src="/assets/img/placeholder.png" alt="Előnézet">
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>

    <div class="tab-pane fade" id="tabServices" role="tabpanel">
      <div class="row g-3">
        <div class="col-xxl-7 col-lg-7">
          <div class="card p-3">
            <h5 class="mb-3">Szolgáltatáslista</h5>
            <div class="table-responsive">
              <table class="table align-middle" id="srvTable">
                <thead class="table-light">
                <tr><th>#</th><th>Név</th><th>Időtartam</th><th class="text-end">Ár</th><th>Állapot</th><th class="text-end">Műveletek</th></tr>
                </thead>
                <tbody>
                <?php foreach ($services as $s): ?>
                  <tr data-id="<?= (int)$s['id'] ?>">
                    <td><?= (int)$s['id'] ?></td>
                    <td>
                      <strong><?= e($s['name']) ?></strong>
                      <?php if (!empty($s['description'])): ?>
                        <div class="text-muted small"><?= e(mb_strimwidth($s['description'],0,120,'…','UTF-8')) ?></div>
                      <?php endif; ?>
                    </td>
                    <td><?= (int)$s['duration_minutes'] ?> perc</td>
                    <td class="text-end"><?= number_format((float)$s['price'],0,',',' ') ?> Ft</td>
                    <td><span class="badge <?= $s['is_active']?'badge-on':'badge-off' ?>"><?= $s['is_active']?'Aktív':'Inaktív' ?></span></td>
                    <td class="text-end">
                      <div class="d-inline-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary js-edit-service"
                                title="Szerkesztés"
                                data-service='<?= e(json_encode($s)) ?>'>
                          <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <form method="post" class="d-inline js-del-service" onsubmit="return confirm('Biztosan törlöd?');">
                          <?= csrf_field() ?>
                          <input type="hidden" name="action" value="service_delete">
                          <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                          <button class="btn btn-sm btn-outline-danger" title="Törlés"><i class="fa-solid fa-trash"></i></button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($services)): ?>
                  <tr><td colspan="6" class="text-muted">Nincs szolgáltatás.</td></tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-xxl-5 col-lg-5">
          <div class="card p-3">
            <h5 class="mb-3" id="sfTitle">Új szolgáltatás</h5>
            <form method="post" id="serviceForm">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="service_save">
              <input type="hidden" name="id" id="s_id" value="">
              <div class="mb-2">
                <label class="form-label">Megnevezés *</label>
                <input class="form-control" name="name" id="s_name" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Időtartam (perc) *</label>
                <input type="number" min="1" class="form-control" name="duration_minutes" id="s_minutes" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Ár (Ft) *</label>
                <input type="number" min="0" step="1" class="form-control" name="price" id="s_price" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Leírás</label>
                <textarea class="form-control" rows="4" name="description" id="s_desc"></textarea>
              </div>
              <div class="switch-row switch-wrap form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="s_active" name="is_active" checked>
                <label class="form-check-label" for="s_active">Aktív</label>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i> Mentés</button>
                <button class="btn btn-outline-secondary" type="button" id="btnResetService"><i class="fa-solid fa-rotate-left me-1"></i> Újrakezdem</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/admin/admin.js"></script>
</body>
</html>