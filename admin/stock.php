<?php
session_start();
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../connect.php';

$mysqli = db();
if (empty($_SESSION['belepve']) || ($_SESSION['role'] ?? '') !== 'owner') {
  http_response_code(403); echo 'Hozzáférés megtagadva.'; exit;
}
$userId = (int)($_SESSION['user_id'] ?? 0);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function abs_img(?string $p){ $p = $p ?: '/assets/img/placeholder.png'; return ($p[0]==='/')? $p : '/'.ltrim($p,'/'); }
function post($k,$d=''){ return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; }
function num($s){ return (float)str_replace([',',' '], ['.',''], $s); }
function audit(mysqli $db, int $userId, string $action, string $entity, ?int $entityId){
  try{
    $st=$db->prepare("INSERT INTO audit_log (user_id, action, entity, entity_id) VALUES (?,?,?,?)");
    $st->bind_param('issi',$userId,$action,$entity,$entityId);
    $st->execute();
  }catch(Throwable $e){}
}

$flash=['ok'=>null,'err'=>null];
try{ if($_SERVER['REQUEST_METHOD']==='POST') csrf_validate(); }catch(Throwable $e){ $flash['err']='Érvénytelen CSRF token.'; }

if($mysqli && $_SERVER['REQUEST_METHOD']==='POST' && !$flash['err']){
  try{
    $pid  = (int)post('product_id',0);
    $qty  = (int)post('qty',0);
    $unit = num(post('unit_cost','0'));
    if($pid<=0) throw new RuntimeException('Hibás termékazonosító.');
    if($qty<=0) throw new RuntimeException('A rendelt mennyiség legyen pozitív.');

    $pr = $mysqli->query("SELECT price FROM products WHERE id={$pid} LIMIT 1")->fetch_assoc();
    if(!$pr) throw new RuntimeException('Termék nem található.');
    $sell = (float)$pr['price'];

    if($unit>0 && $unit>$sell){
      throw new RuntimeException('Az egységár nem lehet magasabb az eladási árnál.');
    }

    $mysqli->begin_transaction();

    $st=$mysqli->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id=?");
    $st->bind_param('ii',$qty,$pid); $st->execute();

    if($unit>0){
      $st=$mysqli->prepare("UPDATE products SET cost_price=? WHERE id=?");
      $st->bind_param('di',$unit,$pid); $st->execute();
    }

    $st=$mysqli->prepare("INSERT INTO stock_movements (product_id, qty_change, unit_cost, reason) VALUES (?,?,?,'purchase')");
    $plus=+$qty; $st->bind_param('iid',$pid,$plus,$unit); $st->execute();

    audit($mysqli,$userId,'purchase','products',$pid);
    $mysqli->commit();
    $flash['ok']='Beszerzés rögzítve.';
  }catch(Throwable $ex){
    try{ $mysqli->rollback(); }catch(Throwable $e){}
    $flash['err']='Hiba: '.$ex->getMessage();
  }
}

$rows=[];
if($mysqli){
  $q="SELECT id, brand, name, type, price, cost_price, stock_qty, image FROM products ORDER BY brand, name";
  $res=$mysqli->query($q);
  while($r=$res->fetch_assoc()){
    $r['image']=abs_img($r['image']);
    $rows[]=$r;
  }
}
$types=['shampoo'=>'Sampon','conditioner'=>'Balzsam','mask'=>'Maszk','treatment'=>'Kezelés','styling'=>'Styling','other'=>'Egyéb'];
?>
<!doctype html>
<html lang="hu">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin – Készlet</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="/assets/hairheaven.css">
<style>
  body{ background:#faf7ff; }
  .card{ box-shadow:0 10px 30px rgba(0,0,0,.06); border:0; border-radius:16px; }
  .thumb{ width:72px;height:72px;object-fit:cover;border-radius:10px;border:1px solid #eee;background:#fff; }
  .table thead th{ white-space:nowrap; }
  .table tbody td{ vertical-align:middle; }
  .min-w-110{ min-width:110px; }
  .min-w-140{ min-width:140px; }
  .stock-low{ background:#ffe8e8 !important; }
  .profit-pos{ color:#0a7a18; font-weight:700; }
  .profit-neg{ color:#b20b0b; font-weight:700; }
  .helpbar{ border-radius:12px; background:#fff; box-shadow:0 6px 18px rgba(0,0,0,.05); }

  .admin-tabs{ border-bottom:2px solid #efe9ff; }
  .admin-tabs .nav-link{
    border:0;
    color:#6c6a75;
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
<?php $activePage='admin'; include __DIR__ . '/../navbar.php'; ?>

<div class="container-xxl py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Készletnyilvántartás</h1>
  </div>

  <ul class="nav admin-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link" href="/admin/index.php">Termékek</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" href="/admin/index.php#tabServices">Szolgáltatások</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link active" href="/admin/stock.php">Készlet</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" href="/admin/treatments.php">Kezelések</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" href="/admin/orders.php">Rendelések</a>
    </li>
  </ul>

  <?php if ($flash['ok']): ?><div class="alert alert-success"><?= e($flash['ok']) ?></div><?php endif; ?>
  <?php if ($flash['err']): ?><div class="alert alert-danger"><?= e($flash['err']) ?></div><?php endif; ?>

  <div class="helpbar p-3 mb-3">
    <strong>Beszerzés kitöltése:</strong>
    <ol class="mb-0 mt-2">
      <li><b>Rendelt mennyiség</b> (db) – kötelező, pozitív egész szám.</li>
      <li><b>Egységár</b> (Ft/db) – opcionális. Ha megadod, frissül a beszerzési ár. Nem lehet magasabb az eladási árnál.</li>
    </ol>
  </div>

  <div class="card p-3">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Kép</th>
            <th>Márka / Név</th>
            <th>Típus</th>
            <th class="text-end min-w-110">Raktár</th>
            <th class="text-end min-w-110">Beszerzési ár</th>
            <th class="text-end min-w-110">Eladási ár</th>
            <th class="text-end min-w-140">Haszon</th>
            <th class="text-end min-w-200">Beszerzés</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($rows as $r):
          $profit = (float)$r['price'] - (float)$r['cost_price'];
          $ppct   = ($r['price']>0) ? ($profit / (float)$r['price'] * 100.0) : 0.0;
          $low    = ((int)$r['stock_qty'] < 20);
        ?>
          <tr class="<?= $low? 'table-danger stock-low':'table-white' ?>">
            <td><?= (int)$r['id'] ?></td>
            <td><img src="<?= e($r['image']) ?>" class="thumb" alt=""></td>
            <td><strong><?= e($r['brand'].' – '.$r['name']) ?></strong></td>
            <td><?= e($types[$r['type']] ?? $r['type']) ?></td>
            <td class="text-end fw-bold"><?= (int)$r['stock_qty'] ?> db</td>
            <td class="text-end"><?= number_format((float)$r['cost_price'],0,',',' ') ?> Ft</td>
            <td class="text-end"><?= number_format((float)$r['price'],0,',',' ') ?> Ft</td>
            <td class="text-end">
              <span class="<?= $profit>=0?'profit-pos':'profit-neg' ?>">
                <?= number_format($profit,0,',',' ') ?> Ft
              </span>
              <small class="text-muted">(<?= number_format($ppct,1,',',' ') ?>%)</small>
            </td>
            <td class="text-end">
              <form method="post" class="d-inline-flex align-items-center gap-1">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int)$r['id'] ?>">
                <input type="number" class="form-control form-control-sm" name="qty" min="1" required placeholder="db" style="width:86px">
                <input type="number" class="form-control form-control-sm" name="unit_cost" min="0" step="1" placeholder="Ft/db" style="width:100px">
                <button class="btn btn-sm btn-cta text-white" name="action" value="purchase" title="Beszerzés">
                  <i class="fa-solid fa-circle-plus me-1"></i> Beszerzés
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($rows)): ?>
          <tr><td colspan="9" class="text-muted">Nincs termék.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
