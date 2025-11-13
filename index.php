<?php
// Hair Heaven - Főoldal (index.php) - teljes, önállóan bemásolható fájl
session_start();

// --- DB kapcsolódás (opcionális) ---
$mysqli = null;
if (file_exists(__DIR__ . '/konfiguracio.php')) {
  include __DIR__ . '/konfiguracio.php'; // definiálhat $mysqli-t
  if (isset($mysqli) && $mysqli instanceof mysqli) {
    // ok
  } else {
    $mysqli = null;
  }
}

// --- Helper: biztonságos escapelés ---
function e($str) {
  return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// --- Adatlekérő helper (hiba esetén üres tömb) ---
function fetch_all_assoc($mysqli, $sql) {
  if (!$mysqli) return [];
  $res = @$mysqli->query($sql);
  if (!$res) return [];
  $out = [];
  while ($row = $res->fetch_assoc()) $out[] = $row;
  return $out;
}

// --- BANNEREK ---
$banners = fetch_all_assoc($mysqli, "
  SELECT id, title, image_path, link_url
  FROM banners
  WHERE is_active = 1
  ORDER BY id DESC
  LIMIT 5
");

// Dummy bannerek, ha nincs adatbázis/tábla
if (empty($banners)) {
  $banners = [
    ['id'=>1,'title'=>'Őszi hajápolás új szinten','image_path'=>'uploads/banners/fall.jpg','link_url'=>'/aruhaz.php?type=mask'],
    ['id'=>2,'title'=>'Top ajánlatok – válogatott kedvencek','image_path'=>'uploads/banners/top.jpg','link_url'=>'/#ajanlatok'],
    ['id'=>3,'title'=>'Fejbőr-kényeztetés','image_path'=>'uploads/banners/scalp.jpg','link_url'=>'/szolgaltatasok.php'],
  ];
}

// --- KIEMELT TERMÉKEK (Top ajánlatok) ---
$featured = fetch_all_assoc($mysqli, "
  SELECT id, brand, name, price, image
  FROM products
  WHERE is_active = 1 AND is_featured = 1
  ORDER BY id DESC
  LIMIT 8
");
if (empty($featured)) {
  $featured = [
    ['id'=>1,'brand'=>'Garnier','name'=>'Vitamin+ Repair Conditioner','price'=>3490.00,'image'=>'uploads/products/garnier fructis.jpg'],
    ['id'=>2,'brand'=>'Schwarzkopf','name'=>'Deep Cleanse Shampoo','price'=>4190.00,'image'=>'uploads/products/2.jpg'],
    ['id'=>3,'brand'=>'L\'Oréal','name'=>'Color Protect Mask','price'=>5990.00,'image'=>'uploads/products/loreal protect mask.jpg'],
    ['id'=>4,'brand'=>'Kérastase','name'=>'Scalp Elixir Treatment','price'=>8990.00,'image'=>'uploads/products/4.jpg'],
  ];
}


// --- SZOLGÁLTATÁSOK 
$services = fetch_all_assoc($mysqli, "
  SELECT id, name, duration_minutes, price, description
  FROM services
  WHERE is_active = 1
  ORDER BY id ASC
  LIMIT 6
");
if (empty($services)) {
  $services = [
    ['id'=>1,'name'=>'Női hajvágás','duration_minutes'=>45,'price'=>6900.00,'description'=>'Konzultáció + vágás + szárítás.'],
    ['id'=>2,'name'=>'Férfi hajvágás','duration_minutes'=>30,'price'=>4900.00,'description'=>'Gyors vágás és formázás.'],
    ['id'=>3,'name'=>'Fejbőrkezelés','duration_minutes'=>40,'price'=>8900.00,'description'=>'Kíméletes fejbőrápoló kúra.'],
  ];
}

// --- ELÉGEDETT VÁSÁRLÓK (publikus profil teaser) ---
$profiles = fetch_all_assoc($mysqli, "
  SELECT id, display_name, avatar, bio, favorite_brand
  FROM public_profiles
  ORDER BY id DESC
  LIMIT 6
");
if (empty($profiles)) {
  $profiles = [
    ['id'=>1,'display_name'=>'Anna K.','avatar'=>'uploads/profiles/anna_k.jpg','bio'=>'Színkezelt haj, heti pakolás.','favorite_brand'=>"L'Oréal"],
    ['id'=>2,'display_name'=>'Bence','avatar'=>'uploads/profiles/bence.jpg','bio'=>'Sportos fazon, mélytisztítás.','favorite_brand'=>"Schwarzkopf"],
    ['id'=>3,'display_name'=>'Luca','avatar'=>'uploads/profiles/luca.jpg','bio'=>'Hajerősítő kúra.','favorite_brand'=>"Kérastase"],
  ];
}

// --- NAVBAR segéd: belépve? ---
$isLogged = !empty($_SESSION['belepve']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hair Heaven – Főoldal</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Ikonok -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    :root{
      --hh-primary: #c76df0;   /* lila-pasztell */
      --hh-dark:    #1c1a27;
      --hh-muted:   #6c6a75;
      --hh-bg:      #faf7ff;
    }
    body{ background: var(--hh-bg); color: var(--hh-dark); }
    .navbar{ background: #fff; box-shadow: 0 6px 20px rgba(0,0,0,.06); }
    .navbar-brand{
      font-weight: 800; letter-spacing:.5px; color: var(--hh-dark);
    }
    .navbar-brand .dot{ color: var(--hh-primary); }
    .nav-link{ font-weight: 600; color: var(--hh-dark); }
    .nav-link:hover{ color: var(--hh-primary); }

    /* Carousel */
    .hh-carousel .carousel-item{
      height: 58vh; min-height: 360px; background:#111; position:relative; color:#fff;
    }
    .hh-carousel .carousel-item .mask{
      position:absolute; inset:0; background:linear-gradient(40deg, rgba(0,0,0,.5), rgba(0,0,0,.15));
    }
    .hh-carousel img{
      object-fit: cover; width:100%; height:100%;
      filter: saturate(1.05) contrast(1.02);
    }
    .hh-carousel h1{
      text-shadow: 0 10px 30px rgba(0,0,0,.4);
      font-weight: 800; letter-spacing:.3px;
    }
    .btn-cta{
      background: var(--hh-primary); border:0; font-weight:700; letter-spacing:.4px;
    }
    .btn-cta:hover{ filter: brightness(1.05); }

    /* Szekció fejlécek */
    .section-title{
      font-weight: 800; letter-spacing:.4px; margin-bottom: .5rem;
    }
    .section-sub{ color: var(--hh-muted); }

    /* Termék kártyák */
    .product-card{
      border: 0; border-radius: 16px; overflow:hidden; background:#fff;
      box-shadow: 0 10px 30px rgba(0,0,0,.06);
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .product-card:hover{
      transform: translateY(-3px);
      box-shadow: 0 14px 36px rgba(0,0,0,.1);
    }
    .product-card img{ height: 210px; object-fit: cover; }
    .brand-badge{
      background: #f3e7ff; color: #7e3dbf; font-weight:700;
      border-radius: 999px; padding: .25rem .65rem; font-size:.75rem;
    }
    .price-tag{ font-weight: 800; font-size: 1.15rem; }

    /* Szolgáltatás kártyák */
    .service-card{ background:#fff; border-radius:16px; padding:20px; height:100%;
      box-shadow: 0 10px 30px rgba(0,0,0,.06); }
    .service-card h5{ font-weight:800; }
    .service-chip{ color: var(--hh-muted); font-size:.9rem; }

    /* Profil kártyák */
    .profile-card{ background:#fff; border-radius:16px; padding:20px; height:100%;
      box-shadow: 0 10px 30px rgba(0,0,0,.06); }
    .profile-avatar{
      width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid #fff;
      box-shadow: 0 8px 24px rgba(0,0,0,.12);
    }
    .fav-brand{ background:#fff0; color:#7e3dbf; border:1px solid #e5d6ff; padding:.2rem .55rem; border-radius:999px; font-size:.8rem; }

    /* Lábléc */
    footer{ color: var(--hh-muted); }

    /* „Anchor” a Top ajánlatokhoz */
    #ajanlatok::before{ content:""; display:block; height:72px; margin-top:-72px; visibility:hidden; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/">
      Hair <span class="dot">Heaven</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hhNav" aria-controls="hhNav" aria-expanded="false" aria-label="Menü">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="hhNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="/">Főoldal</a></li>
        <li class="nav-item"><a class="nav-link" href="/aruhaz.php">Áruház</a></li>
        <li class="nav-item"><a class="nav-link" href="/szolgaltatasok.php">Szolgáltatások</a></li>
        <li class="nav-item"><a class="nav-link" href="/ugyfelek.php">Elégedett vásárlók</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item me-2">
          <a class="btn btn-sm btn-outline-dark" href="/kosar.php">
            <i class="fa-solid fa-bag-shopping me-1"></i> Kosár
          </a>
        </li>
        <?php if ($isLogged): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="fa-solid fa-user"></i> <?= e($username ?: 'Profil') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/profil.php">Profilom</a></li>
              <li><a class="dropdown-item" href="/rendeleseim.php">Rendeléseim</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/logout.php">Kijelentkezés</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-sm btn-cta text-white" href="/belepes.php">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Bejelentkezés
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- CAROUSEL -->
<div id="hhCarousel" class="carousel slide hh-carousel" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <?php foreach ($banners as $i => $b): ?>
      <button type="button" data-bs-target="#hhCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i===0?'active':'' ?>" aria-current="<?= $i===0?'true':'false' ?>" aria-label="Slide <?= $i+1 ?>"></button>
    <?php endforeach; ?>
  </div>
  <div class="carousel-inner">
    <?php foreach ($banners as $i => $b): ?>
    <div class="carousel-item <?= $i===0?'active':'' ?>">
      <img src="<?= e($b['image_path']) ?>" class="d-block w-100" alt="<?= e($b['title']) ?>">
      <div class="mask"></div>
      <div class="container h-100">
        <div class="row h-100 align-items-center">
          <div class="col-12 col-lg-7 text-white">
            <h1 class="display-5 mb-3"><?= e($b['title']) ?></h1>
            <p class="lead mb-4">Fedezd fel a prémium hajápolás világát – kényeztesd magad a Hair Heaven-ben.</p>
            <?php if (!empty($b['link_url'])): ?>
              <a class="btn btn-lg btn-cta text-white" href="<?= e($b['link_url']) ?>">Felfedezem</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#hhCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Előző</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#hhCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Következő</span>
  </button>
</div>

<!-- TOP AJÁNLATOK -->
<section class="py-5" id="ajanlatok">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-3">
      <div>
        <h2 class="section-title">Top ajánlatok</h2>
        <p class="section-sub mb-0">Kiemelt kedvencek válogatása – azonnal kosárba teheted.</p>
      </div>
      <a class="btn btn-outline-dark btn-sm" href="/aruhaz.php">
        Összes termék <i class="fa-solid fa-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-4">
      <?php foreach ($featured as $p): ?>
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card product-card h-100">
            <img src="<?= e($p['image']) ?>" class="card-img-top" alt="<?= e($p['name']) ?>">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="brand-badge"><?= e($p['brand']) ?></span>
                <span class="price-tag"><?= number_format((float)$p['price'], 0, ',', ' ') ?> Ft</span>
              </div>
              <h5 class="card-title"><?= e($p['name']) ?></h5>
              <div class="d-grid gap-2 mt-3">
                <a href="/termek.php?id=<?= (int)$p['id'] ?>" class="btn btn-outline-dark">
                  Részletek
                </a>
                <form method="post" action="/api/cart_add.php" class="d-grid">
                  <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                  <input type="hidden" name="qty" value="1">
                  <button class="btn btn-cta text-white" type="submit">
                    <i class="fa-solid fa-cart-plus me-1"></i> Kosárba
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($featured)): ?>
        <div class="col-12">
          <div class="alert alert-secondary">Nincs kiemelt termék még beállítva.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- SZOLGÁLTATÁSOK TEASER -->
<section class="py-5">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-3">
      <div>
        <h2 class="section-title">Szolgáltatásaink</h2>
        <p class="section-sub mb-0">Foglalj időpontot kedvenc kezelésedre, pár kattintással.</p>
      </div>
      <a class="btn btn-outline-dark btn-sm" href="/szolgaltatasok.php">
        Összes szolgáltatás <i class="fa-solid fa-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-4">
      <?php foreach ($services as $s): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="service-card">
            <div class="d-flex justify-content-between align-items-start">
              <h5 class="mb-2"><?= e($s['name']) ?></h5>
              <span class="price-tag"><?= number_format((float)$s['price'], 0, ',', ' ') ?> Ft</span>
            </div>
            <div class="service-chip mb-2"><i class="fa-regular fa-clock me-1"></i> <?= (int)$s['duration_minutes'] ?> perc</div>
            <p class="mb-3"><?= e($s['description']) ?></p>
            <a href="/foglalas.php?service_id=<?= (int)$s['id'] ?>" class="btn btn-cta text-white">
              Időpontot foglalok
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ELÉGEDETT VÁSÁRLÓK TEASER -->
<section class="py-5">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-3">
      <div>
        <h2 class="section-title">Elégedett vásárlók</h2>
        <p class="section-sub mb-0">Valódi arcok, valódi történetek – inspirálódj!</p>
      </div>
      <a class="btn btn-outline-dark btn-sm" href="/ugyfelek.php">
        Minden profil <i class="fa-solid fa-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-4">
      <?php foreach ($profiles as $pr): ?>
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="profile-card">
            <div class="d-flex align-items-center mb-3">
              <img class="profile-avatar me-3" src="<?= e($pr['avatar']) ?>" alt="<?= e($pr['display_name']) ?>">
              <div>
                <strong><?= e($pr['display_name']) ?></strong><br>
                <?php if (!empty($pr['favorite_brand'])): ?>
                  <span class="fav-brand"><i class="fa-solid fa-heart me-1"></i> <?= e($pr['favorite_brand']) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <?php if (!empty($pr['bio'])): ?>
              <p class="mb-3"><?= e($pr['bio']) ?></p>
            <?php endif; ?>
            <a class="btn btn-outline-dark btn-sm" href="/ugyfel.php?id=<?= (int)$pr['id'] ?>">
              Megnézem a profilját
            </a>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($profiles)): ?>
        <div class="col-12">
          <div class="alert alert-secondary">Még nincs feltöltött profil. Hamarosan!</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- LÁBLÉC -->
<footer class="py-4 border-top bg-white">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between">
    <div>
      <strong>Hair Heaven</strong> &middot; Premium hair & scalp care
    </div>
    <div class="text-muted">
      © <?= date('Y') ?> Hair Heaven · Minden jog fenntartva
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
