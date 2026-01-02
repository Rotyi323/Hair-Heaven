<?php
session_start();
require_once __DIR__ . '/biztonsag.php';
require_once __DIR__ . '/connect.php';

$mysqli = db(); // mysqli | null

// --- Helpers már a biztonsag.php-ból: e(), get_param(), stb.

// Alap beállítások
$PAGE_SIZE = 12;

// Szűrők
$q           = get_param('q');
$brand       = get_param('brand');
$allowedTypes= ['shampoo','conditioner','mask','treatment','styling','other'];
$type        = get_enum('type', $allowedTypes, '');
$types       = $allowedTypes; // <- kell a legördülőhöz
$sort        = get_enum('sort', ['name_asc','name_desc','price_asc','price_desc'], 'name_asc');

// Min/Max ár (mezőkben ezres tagolás lehet -> tisztítás)
$min_price = get_int('min_price', 0, 0, 100000);
$max_price = get_int('max_price', 100000, 0, 100000);

// Rendezés opciók
$sortOptions = [
  'name_asc'  => ['label' => 'Név (A–Z)',    'sql' => 'name ASC'],
  'name_desc' => ['label' => 'Név (Z–A)',    'sql' => 'name DESC'],
  'price_asc' => ['label' => 'Ár (növekvő)', 'sql' => 'price ASC'],
  'price_desc'=> ['label' => 'Ár (csökkenő)','sql' => 'price DESC'],
];
$page = max(1, (int)($_GET['page'] ?? 1));

// Ár csúszka fix tartomány
$rangeMin = 0; $rangeMax = 100000;
$curMin = max($rangeMin, min($min_price, $rangeMax));
$curMax = max($rangeMin, min($max_price, $rangeMax));
if ($curMin > $curMax) { $t=$curMin; $curMin=$curMax; $curMax=$t; }

// --- Márkák listája ---
$brands = [];
if ($mysqli) {
  try {
    $res = $mysqli->query("SELECT DISTINCT brand FROM products WHERE is_active=1 ORDER BY brand ASC");
    while ($row = $res->fetch_assoc()) { if (!empty($row['brand'])) $brands[] = $row['brand']; }
  } catch (Throwable $ex) { /* fallback */ }
}
if (!$mysqli || empty($brands)) { $brands = ["Garnier","Schwarzkopf","L'Oréal","Kérastase"]; }

// --- Termékek lekérdezése ---
$items=[]; $total=0; $pages=1; $offset=($page-1)*$PAGE_SIZE;

if ($mysqli) {
  $where = ["is_active=1"];
  $params=[]; $typesBind='';

  if ($q !== '') {
    $where[]="(name LIKE CONCAT('%', ?, '%') OR description LIKE CONCAT('%', ?, '%') OR brand LIKE CONCAT('%', ?, '%'))";
    $params[]=$q; $params[]=$q; $params[]=$q; $typesBind.='sss';
  }
  if ($brand !== '') { $where[]="brand = ?"; $params[]=$brand; $typesBind.='s'; }
  if ($type  !== '') { $where[]="type = ?";  $params[]=$type;  $typesBind.='s'; }
  $where[]="price >= ?"; $params[]=(float)$curMin; $typesBind.='d';
  $where[]="price <= ?"; $params[]=(float)$curMax; $typesBind.='d';

  $whereSql = 'WHERE '.implode(' AND ', $where);
  $orderSql = 'ORDER BY '.$sortOptions[$sort]['sql'];

  $stmt = $mysqli->prepare("SELECT COUNT(*) c FROM products $whereSql");
  $stmt->bind_param($typesBind, ...$params);
  $stmt->execute();
  $total = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
  $pages = max(1, (int)ceil($total / $PAGE_SIZE));
  if ($page > $pages) { $page=$pages; $offset=($page-1)*$PAGE_SIZE; }

  $stmt = $mysqli->prepare("SELECT id, brand, name, price, image, type FROM products $whereSql $orderSql LIMIT ? OFFSET ?");
  $bindTypes = $typesBind.'ii';
  $bindParams = array_merge($params, [$PAGE_SIZE, $offset]);
  $stmt->bind_param($bindTypes, ...$bindParams);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) $items[]=$row;

} else {
  // Dummy adatok + szűrés/rendezés
  $all = [
    ['id'=>1,'brand'=>'Garnier','name'=>'Vitamin+ Repair Conditioner','type'=>'conditioner','price'=>3490.00,'image'=>'uploads/products/1.jpg'],
    ['id'=>2,'brand'=>'Schwarzkopf','name'=>'Deep Cleanse Shampoo','type'=>'shampoo','price'=>4190.00,'image'=>'uploads/products/2.jpg'],
    ['id'=>3,'brand'=>"L'Oréal",'name'=>'Color Protect Mask','type'=>'mask','price'=>5990.00,'image'=>'uploads/products/3.jpg'],
    ['id'=>4,'brand'=>'Kérastase','name'=>'Scalp Elixir Treatment','type'=>'treatment','price'=>8990.00,'image'=>'uploads/products/4.jpg'],
    ['id'=>5,'brand'=>'Garnier','name'=>'Fructis Curl Styling Cream','type'=>'styling','price'=>3290.00,'image'=>'uploads/products/5.jpg'],
    ['id'=>6,'brand'=>"L'Oréal",'name'=>'Purifying Shampoo','type'=>'shampoo','price'=>4590.00,'image'=>'uploads/products/6.jpg'],
    ['id'=>7,'brand'=>'Schwarzkopf','name'=>'Keratin Repair Mask','type'=>'mask','price'=>6490.00,'image'=>'uploads/products/7.jpg'],
    ['id'=>8,'brand'=>'Kérastase','name'=>'Nourish Conditioner','type'=>'conditioner','price'=>8790.00,'image'=>'uploads/products/8.jpg'],
    ['id'=>9,'brand'=>'Garnier','name'=>'Aloe Hydrate Shampoo','type'=>'shampoo','price'=>3390.00,'image'=>'uploads/products/9.jpg'],
    ['id'=>10,'brand'=>"L'Oréal",'name'=>'Gloss Styling Spray','type'=>'styling','price'=>3990.00,'image'=>'uploads/products/10.jpg'],
    ['id'=>11,'brand'=>'Schwarzkopf','name'=>'Scalp Care Treatment','type'=>'treatment','price'=>9590.00,'image'=>'uploads/products/11.jpg'],
    ['id'=>12,'brand'=>'Kérastase','name'=>'Volume Boost Mask','type'=>'mask','price'=>9990.00,'image'=>'uploads/products/12.jpg'],
    ['id'=>13,'brand'=>'Garnier','name'=>'Fructis Shine Conditioner','type'=>'conditioner','price'=>3790.00,'image'=>'uploads/products/13.jpg'],
  ];
  $items = array_filter($all, function($p) use($q,$brand,$type,$curMin,$curMax){
    if ($brand !== '' && strcasecmp($p['brand'],$brand)!==0) return false;
    if ($type !== ''  && $p['type'] !== $type) return false;
    if ($q !== '' && stripos($p['name'].$p['brand'], $q) === false) return false;
    if ($p['price'] < (float)$curMin || $p['price'] > (float)$curMax) return false;
    return true;
  });
  usort($items, function($a,$b) use($sort){
    switch($sort){
      case 'price_asc': return $a['price'] <=> $b['price'];
      case 'price_desc':return $b['price'] <=> $a['price'];
      case 'name_desc': return strcasecmp($b['name'],$a['name']);
      default:          return strcasecmp($a['name'],$b['name']);
    }
  });
  $total = count($items);
  $pages = max(1, (int)ceil($total / $PAGE_SIZE));
  $page  = min($page, $pages);
  $offset= ($page-1)*$PAGE_SIZE;
  $items = array_slice($items, $offset, $PAGE_SIZE);
}

// Lapozó helper
function qs_without_page(){
  $params = $_GET; unset($params['page']);
  $pairs=[];
  foreach($params as $k=>$v){ if($v===''||$v===null) continue; $pairs[] = urlencode($k).'='.urlencode($v); }
  return $pairs ? ('&'.implode('&',$pairs)) : '';
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hair Heaven – Áruház</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    :root{
      --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75; --hh-bg:#faf7ff;
      --range-bg:#e9d9fb; --range-fill:#c76df0;
    }
    body{ background:var(--hh-bg); color:var(--hh-dark); }
    .navbar{ background:#fff; box-shadow:0 6px 20px rgba(0,0,0,.06); }
    .navbar-brand{ font-weight:800; letter-spacing:.5px; color:var(--hh-dark); }
    .navbar-brand .dot{ color:var(--hh-primary); }
    .nav-link{ font-weight:600; color:var(--hh-dark); }
    .nav-link:hover, .nav-link.active{ color:var(--hh-primary); }

    .page-title{ font-weight:800; letter-spacing:.4px; }
    .filter-card{ background:#fff; border-radius:14px; padding:16px; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .product-card{ border:0; border-radius:16px; overflow:hidden; background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.06); transition:transform .2s, box-shadow .2s; }
    .product-card:hover{ transform:translateY(-3px); box-shadow:0 14px 36px rgba(0,0,0,.1); }
    .product-card img{ height:210px; object-fit:cover; }
    .brand-badge{ background:#f3e7ff; color:#7e3dbf; font-weight:700; border-radius:999px; padding:.25rem .65rem; font-size:.75rem; }
    .price-tag{ font-weight:800; font-size:1.1rem; }
    .form-select, .form-control{ border-radius:10px; }
    .btn-cta{ background:var(--hh-primary); border:0; font-weight:700; letter-spacing:.3px; }
    .btn-cta:hover{ filter:brightness(1.05); }
    .range-wrap{ position:relative; }
    .multi-range{ position:relative; height:36px; }
    .multi-range input[type=range]{ position:absolute; left:0; right:0; top:8px; pointer-events:none; -webkit-appearance:none; width:100%; background:transparent; height:0; }
    .multi-range input[type=range]::-webkit-slider-thumb{ pointer-events:auto; -webkit-appearance:none; width:18px; height:18px; border-radius:50%; background:var(--hh-primary); box-shadow:0 2px 8px rgba(0,0,0,.2); border:2px solid #fff; margin-top:-7px; }
    .multi-range input[type=range]::-moz-range-thumb{ pointer-events:auto; width:18px; height:18px; border-radius:50%; background:var(--hh-primary); border:2px solid #fff; }
    .range-track{ position:absolute; left:0; right:0; top:16px; height:4px; border-radius:999px; background:var(--range-bg); }
    .range-fill{ position:absolute; top:16px; height:4px; border-radius:999px; background:var(--range-fill); }
    .range-values{ display:flex; gap:.75rem; align-items:center; margin-top:8px; flex-wrap:wrap; }
    .range-values .input-group{ min-width:220px; max-width:240px; }
  </style>
</head>
<body>

<?php $activePage = 'shop'; include __DIR__ . '/navbar.php'; ?>
<!-- FEJLÉC -->
<div class="container mt-4">
  <div class="d-flex align-items-end justify-content-between flex-wrap gap-2">
    <div>
      <h1 class="page-title mb-1">Áruház</h1>
      <div class="text-muted">Válogass prémium hajápolási termékeink közül – szűrés és rendezés egy kattintással.</div>
    </div>
  </div>
</div>

<!-- SZŰRŐK / KERESÉS -->
<div class="container my-3">
  <form class="filter-card" method="get" action="/aruhaz.php" id="filterForm">
    <!-- 1. sor: általános szűrők + gombok egy oszlopban -->
    <div class="row g-3 align-items-end">
      <div class="col-12 col-md-4 col-lg-3">
        <label class="form-label">Keresés</label>
        <input type="text" class="form-control" name="q" value="<?= e($q) ?>" placeholder="Terméknév, márka...">
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <label class="form-label">Márka</label>
        <select class="form-select" name="brand">
          <option value="">Összes</option>
          <?php foreach ($brands as $b): ?>
            <option value="<?= e($b) ?>" <?= ($brand===$b?'selected':'') ?>><?= e($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <label class="form-label">Típus</label>
        <select class="form-select" name="type">
          <option value="">Összes</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= e($t) ?>" <?= ($type===$t?'selected':'') ?>><?= e(ucfirst($t)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <label class="form-label">Rendezés</label>
        <select class="form-select" name="sort">
          <?php foreach ($sortOptions as $k=>$cfg): ?>
            <option value="<?= e($k) ?>" <?= ($sort===$k?'selected':'') ?>><?= e($cfg['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3 col-lg-3">
        <button class="btn btn-cta text-white w-100 mb-2" type="submit" style="margin-top: 32px;">
          <i class="fa-solid fa-magnifying-glass me-1"></i> Keresés
        </button>
        <button class="btn btn-danger w-100" type="button" id="resetBtn">
          <i class="fa-solid fa-rotate-left me-1"></i> Szűrők visszaállítása
        </button>
      </div>
    </div>

    <!-- 2. sor: ár tartomány -->
    <div class="row g-3 mt-2">
      <div class="col-12">
        <label class="form-label mb-1">Ár tartomány</label>
        <div class="range-wrap">
          <div class="multi-range" data-min="<?= (int)$rangeMin ?>" data-max="<?= (int)$rangeMax ?>">
            <div class="range-track"></div>
            <div class="range-fill" id="rangeFill"></div>
            <input type="range" id="rangeMin" min="<?= (int)$rangeMin ?>" max="<?= (int)$rangeMax ?>" step="10" value="<?= (int)$curMin ?>">
            <input type="range" id="rangeMax" min="<?= (int)$rangeMin ?>" max="<?= (int)$rangeMax ?>" step="10" value="<?= (int)$curMax ?>">
          </div>
          <div class="range-values">
            <div class="input-group">
              <span class="input-group-text">Min</span>
              <input
                type="text" inputmode="numeric" pattern="[0-9 ]*"
                class="form-control price-input" id="min_price" name="min_price"
                value="<?= number_format((int)$curMin, 0, ',', ' ') ?>">
              <span class="input-group-text">Ft</span>
            </div>
            <div class="input-group">
              <span class="input-group-text">Max</span>
              <input
                type="text" inputmode="numeric" pattern="[0-9 ]*"
                class="form-control price-input" id="max_price" name="max_price"
                value="<?= number_format((int)$curMax, 0, ',', ' ') ?>">
              <span class="input-group-text">Ft</span>
            </div>
            <small class="text-muted ms-2">Tartomány: <?= number_format($rangeMin,0,',',' ') ?> – <?= number_format($rangeMax,0,',',' ') ?> Ft</small>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<!-- TERMÉK LISTA -->
<div class="container my-4">
  <div class="row g-4">
    <?php if (empty($items)): ?>
      <div class="col-12"><div class="alert alert-secondary">Nincs a szűrésnek megfelelő termék.</div></div>
    <?php else: ?>
      <?php foreach ($items as $p): ?>
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card product-card h-100">
            <img src="<?= e($p['image']) ?>" class="card-img-top" alt="<?= e($p['name']) ?>">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="brand-badge"><?= e($p['brand']) ?></span>
                <span class="price-tag"><?= number_format((float)$p['price'], 0, ',', ' ') ?> Ft</span>
              </div>
              <h5 class="card-title mb-2"><?= e($p['name']) ?></h5>
              <?php if (!empty($p['type'])): ?>
                <div class="text-muted mb-3" style="font-size:.9rem;"><?= e(ucfirst($p['type'])) ?></div>
              <?php endif; ?>
              <div class="mt-auto d-grid gap-2">
                <a class="btn btn-outline-dark" href="/termek.php?id=<?= (int)$p['id'] ?>">Részletek</a>
                <form method="post" action="/api/cart_add.php">
                  <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                  <input type="hidden" name="qty" value="1">
                  <button class="btn btn-cta text-white w-100" type="submit">
                    <i class="fa-solid fa-cart-plus me-1"></i> Kosárba
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- PAGINÁCIÓ -->
  <?php if ($pages > 1): ?>
  <nav class="mt-4" aria-label="Oldal navigáció">
    <ul class="pagination justify-content-center">
      <?php
        $qs = qs_without_page();
        $prev = max(1, $page-1);
        $next = min($pages, $page+1);
      ?>
      <li class="page-item <?= $page<=1?'disabled':'' ?>">
        <a class="page-link" href="/aruhaz.php?page=1<?= $qs ?>">«</a>
      </li>
      <li class="page-item <?= $page<=1?'disabled':'' ?>">
        <a class="page-link" href="/aruhaz.php?page=<?= $prev . $qs ?>">‹</a>
      </li>
      <?php
        $window = 3;
        $start = max(1, $page-$window);
        $end   = min($pages, $page+$window);
        if ($start > 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
        for($i=$start;$i<=$end;$i++){
          $active = ($i===$page)?'active':'';
          echo '<li class="page-item '.$active.'"><a class="page-link" href="/aruhaz.php?page='.$i.$qs.'">'.$i.'</a></li>';
        }
        if ($end < $pages) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
      ?>
      <li class="page-item <?= $page>=$pages?'disabled':'' ?>">
        <a class="page-link" href="/aruhaz.php?page=<?= $next . $qs ?>">›</a>
      </li>
      <li class="page-item <?= $page>=$pages?'disabled':'' ?>">
        <a class="page-link" href="/aruhaz.php?page=<?= $pages . $qs ?>">»</a>
      </li>
    </ul>
  </nav>
  <?php endif; ?>
</div>

<!-- LÁBLÉC -->
<footer class="py-4 border-top bg-white">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between">
    <div><strong>Hair Heaven</strong> &middot; Premium hair & scalp care</div>
    <div class="text-muted">© <?= date('Y') ?> Hair Heaven · Minden jog fenntartva</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  const form = document.getElementById('filterForm');
  const wrap = document.querySelector('.multi-range');
  if(!wrap) return;

  const min = parseInt(wrap.dataset.min,10);
  const max = parseInt(wrap.dataset.max,10);
  const r1  = document.getElementById('rangeMin');
  const r2  = document.getElementById('rangeMax');
  const f1  = document.getElementById('min_price');
  const f2  = document.getElementById('max_price');
  const fill= document.getElementById('rangeFill');
  const brandSel = form.querySelector('select[name="brand"]');
  const typeSel  = form.querySelector('select[name="type"]');
  const sortSel  = form.querySelector('select[name="sort"]');
  const qInput   = form.querySelector('input[name="q"]');
  const resetBtn = document.getElementById('resetBtn');

  const step= parseInt(r1.getAttribute('step'),10) || 10;

  function clamp(v){ return Math.max(min, Math.min(max, v)); }
  
  function formatThousands(n){
    const s = String(n).replace(/\D/g,'');
    return s.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  }
  function drawFill(v1, v2){
    const pct1 = ((v1 - min) / (max - min)) * 100;
    const pct2 = ((v2 - min) / (max - min)) * 100;
    fill.style.left  = pct1 + '%';
    fill.style.width = (pct2 - pct1) + '%';
  }
  function syncFromRanges(){
    let v1 = parseInt(r1.value,10);
    let v2 = parseInt(r2.value,10);
    if (v1 > v2){ const t=v1; v1=v2; v2=t; }
    f1.value = formatThousands(v1);
    f2.value = formatThousands(v2);
    drawFill(v1, v2);
  }
  function syncFromFields(){
    let v1 = clamp(parseInt((f1.value||'').replace(/\D/g,''),10) || min);
    let v2 = clamp(parseInt((f2.value||'').replace(/\D/g,''),10) || max);
    if (v1 > v2){ const t=v1; v1=v2; v2=t; }
    v1 = Math.round(v1/step)*step;
    v2 = Math.round(v2/step)*step;
    r1.value = v1; r2.value = v2;
    f1.value = formatThousands(v1);
    f2.value = formatThousands(v2);
    drawFill(v1, v2);
  }

  [f1,f2].forEach(el=>{
    el.addEventListener('input', ()=>{
      const raw = el.value.replace(/\D/g,'');
      el.value = formatThousands(raw);
    });
    el.addEventListener('change', syncFromFields);
  });

  r1.addEventListener('input', syncFromRanges);
  r2.addEventListener('input', syncFromRanges);

  form.addEventListener('submit', function(){
    f1.value = (f1.value||'').replace(/\D/g,'');
    f2.value = (f2.value||'').replace(/\D/g,'');
  });

  resetBtn.addEventListener('click', function(){
    qInput.value = '';
    brandSel.value = '';
    typeSel.value  = '';
    sortSel.value  = 'name_asc';

    r1.value = min; r2.value = max;
    f1.value = formatThousands(min);
    f2.value = formatThousands(max);
    drawFill(min, max);

    f1.value = (f1.value||'').replace(/\D/g,'');
    f2.value = (f2.value||'').replace(/\D/g,'');
    form.submit();
  });

  syncFromFields();
})();
</script>
</body>
</html>
