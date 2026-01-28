<?php
session_start();
require_once __DIR__ . '/biztonsag.php';
require_once __DIR__ . '/connect.php';

$mysqli = db(); 

//  Egyszerű adatlekérő helper (hiba esetén üres tömb)
function fetch_all_assoc(?mysqli $mysqli, string $sql): array {
  if (!$mysqli) return [];
  try {
    $res = $mysqli->query($sql);
    if (!$res) return [];
    $out = [];
    while ($row = $res->fetch_assoc()) $out[] = $row;
    return $out;
  } catch (Throwable $e) {
    return [];
  }
}

// ---- Bannerek
$banners = fetch_all_assoc($mysqli, "
  SELECT id, title, image_path, link_url
  FROM banners
  WHERE is_active = 1
  ORDER BY id DESC
  LIMIT 5
");

$slides = [];
if (!empty($banners)) {
  $use = array_slice($banners, 0, 2);
  foreach ($use as $b) {
    $slides[] = [
      'img'   => $b['image_path'],
      'title' => $b['title'],
      'href1' => '/szolgaltatasok.php',
      'text1' => 'Időpontfoglalás',
      'href2' => !empty($b['link_url']) ? $b['link_url'] : '/aruhaz.php',
      'text2' => 'Áruház',
    ];
  }
} else {
  // Fallback
  $slides = [
    [
      'img'   => '/assets/hero/hero-1.jpg',
      'title' => 'Őszi ápolások – vágás, színvédelem, kúrák',
      'href1' => '/szolgaltatasok.php',
      'text1' => 'Időpontfoglalás',
      'href2' => '/aruhaz.php',
      'text2' => 'Áruház',
    ],
    [
      'img'   => '/assets/hero/hero-2.jpg',
      'title' => 'Top ajánlatok & kedvencek – fedezd fel!',
      'href1' => '/aruhaz.php',
      'text1' => 'Felfedezem',
      'href2' => '/aruhaz.php', // NINCS többé elégedett vásárlók oldal
      'text2' => 'Áruház',
    ],
  ];
}

// ---- Kiemelt termékek
$featured = fetch_all_assoc($mysqli, "
  SELECT id, brand, name, price, image
  FROM products
  WHERE is_active = 1 AND is_featured = 1
  ORDER BY id DESC
  LIMIT 8
");
if (empty($featured)) {
  $featured = [
    ['id'=>1,'brand'=>'Garnier','name'=>'Vitamin+ Repair Conditioner','price'=>3490.00,'image'=>'uploads/products/1.jpg'],
    ['id'=>2,'brand'=>'Schwarzkopf','name'=>'Deep Cleanse Shampoo','price'=>4190.00,'image'=>'uploads/products/2.jpg'],
    ['id'=>3,'brand'=>"L'Oréal",'name'=>'Color Protect Mask','price'=>5990.00,'image'=>'uploads/products/3.jpg'],
    ['id'=>4,'brand'=>'Kérastase','name'=>'Scalp Elixir Treatment','price'=>8990.00,'image'=>'uploads/products/4.jpg'],
  ];
}

// ---- Szolgáltatások
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

// ---- Vevői profil képek + komment (CSAK DÍSZ, nincs CTA)
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
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hair Heaven – Főoldal</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    :root{ --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75; --hh-bg:#faf7ff; }
    body{ background:var(--hh-bg); color:var(--hh-dark); }

    .hero-carousel{ margin-bottom:1.5rem; border:.25rem solid #0b0b0b; border-radius:12px; overflow:hidden; }
    .hero-carousel .carousel-item{ height:42rem; position:relative; color:#fff; }
    .hero-carousel .carousel-item>img{ position:absolute; inset:0; width:100%; height:42rem; object-fit:cover; filter:contrast(1.03) saturate(1.05); }
    .hero-mask{ position:absolute; inset:0; background:linear-gradient(120deg, rgba(0,0,0,.55), rgba(0,0,0,.25) 60%, rgba(0,0,0,.08)); z-index:1; }
    .hero-carousel .carousel-caption{ z-index:2; bottom:3rem; text-shadow:0 6px 20px rgba(0,0,0,.45); }
    .hero-title{ font-size:clamp(1.9rem, 1.4rem + 2.4vw, 2.6rem); font-weight:800; line-height:1.2; }
    .hero-lead{ font-size:clamp(1.05rem, .9rem + .7vw, 1.35rem); line-height:1.5; max-width:62ch; margin:0 0 1.25rem 0; }
    .hero-btns .btn{ white-space:nowrap; }

    @media (max-width:576px){
      .hero-carousel .carousel-item{ height:34rem; }
      .hero-carousel .carousel-item>img{ height:34rem; }
      .hero-carousel .carousel-caption{ bottom:2rem; }
    }

    /* Kártyák / szekciók */
    .product-card{
      border:0; border-radius:16px; overflow:hidden; background:#fff;
      box-shadow:0 10px 30px rgba(0,0,0,.06);
      transition:transform .2s ease, box-shadow .2s ease;
    }
    .product-card:hover{ transform: translateY(-3px); box-shadow:0 14px 36px rgba(0,0,0,.1); }
    .product-card img{ height:210px; object-fit:cover; }
    .brand-badge{ background:#f3e7ff; color:#7e3dbf; font-weight:700; border-radius:999px; padding:.25rem .65rem; font-size:.75rem; }
    .price-tag{ font-weight:800; font-size:1.15rem; }
    .service-card{ background:#fff; border-radius:16px; padding:20px; height:100%; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .service-card h5{ font-weight:800; }
    .service-chip{ color: var(--hh-muted); font-size:.9rem; }
    .profile-card{ background:#fff; border-radius:16px; padding:20px; height:100%; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .profile-avatar{ width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid #fff; box-shadow:0 8px 24px rgba(0,0,0,.12); }
    .fav-brand{ color:#7e3dbf; border:1px solid #e5d6ff; padding:.2rem .55rem; border-radius:999px; font-size:.8rem; }
    footer{ color: var(--hh-muted); }
    #ajanlatok::before{ content:""; display:block; height:72px; margin-top:-72px; visibility:hidden; }
  </style>
</head>
<body>

<?php $activePage = 'home'; include __DIR__ . '/navbar.php'; ?>

<!-- HERO CAROUSEL -->
<div id="heroCarousel"
     class="carousel slide hero-carousel"
     data-bs-ride="carousel"
     data-bs-interval="6500"
     data-bs-pause="hover"
     data-bs-touch="true">

  <div class="carousel-indicators">
    <?php foreach ($slides as $i => $_): ?>
      <button type="button"
              data-bs-target="#heroCarousel"
              data-bs-slide-to="<?= $i ?>"
              class="<?= $i===0 ? 'active' : '' ?>"
              aria-current="<?= $i===0 ? 'true' : 'false' ?>"
              aria-label="Slide <?= $i+1 ?>"></button>
    <?php endforeach; ?>
  </div>

  <div class="carousel-inner">
    <?php foreach ($slides as $i => $s): ?>
      <div class="carousel-item <?= $i===0 ? 'active' : '' ?>">
        <img src="<?= htmlspecialchars($s['img'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="hero-mask"></div>
        <div class="container">
          <div class="carousel-caption text-start">
            <div class="mb-2">
              <span class="badge rounded-pill text-bg-light text-dark fw-bold">Hair Heaven • Szalon &amp; Shop</span>
            </div>
            <h1 class="hero-title mb-2"><?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="hero-lead">Prémium hajvágás, színvédelem és fejbőr-kúrák – foglalj online, és fedezd fel kedvenceidet az áruházban!</p>
            <div class="hero-btns d-flex gap-2 justify-content-start">
              <a class="btn btn-lg btn-cta text-white" href="<?= htmlspecialchars($s['href1'], ENT_QUOTES, 'UTF-8') ?>">
                <i class="fa-regular fa-calendar-check me-1"></i> <?= htmlspecialchars($s['text1'], ENT_QUOTES, 'UTF-8') ?>
              </a>
              <a class="btn btn-lg btn-outline-light" href="<?= htmlspecialchars($s['href2'], ENT_QUOTES, 'UTF-8') ?>">
                <i class="fa-solid fa-arrow-right me-1"></i> <?= htmlspecialchars($s['text2'], ENT_QUOTES, 'UTF-8') ?>
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Előző</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Következő</span>
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
            <img src="<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="brand-badge"><?= htmlspecialchars($p['brand'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="price-tag"><?= number_format((float)$p['price'], 0, ',', ' ') ?> Ft</span>
              </div>
              <h5 class="card-title"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h5>
              <div class="d-grid gap-2 mt-3">
                <a href="/termek.php?id=<?= (int)$p['id'] ?>" class="btn btn-outline-dark">Részletek</a>
                <form method="post" action="/api/cart_add.php" class="d-grid">
                  <?= csrf_field() ?>
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
        <div class="col-12"><div class="alert alert-secondary">Nincs kiemelt termék még beállítva.</div></div>
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
          <div class="service-card h-100">
            <div class="d-flex justify-content-between align-items-start">
              <h5 class="mb-2"><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?></h5>
              <span class="price-tag"><?= number_format((float)$s['price'], 0, ',', ' ') ?> Ft</span>
            </div>
            <div class="service-chip mb-2"><i class="fa-regular fa-clock me-1"></i> <?= (int)$s['duration_minutes'] ?> perc</div>
            <p class="mb-3"><?= htmlspecialchars($s['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <a href="/szolgaltatasok.php" class="btn btn-cta text-white">Időpontot foglalok</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- „Vásárlóink mondták” – dísz: kép + komment, NINCS CTA -->
<section class="py-5">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-3">
      <div>
        <h2 class="section-title">Vásárlóink mondták</h2>
        <p class="section-sub mb-0">Valódi arcok, valódi történetek – inspirálódj!</p>
      </div>
      <!-- NINCS „Minden profil” gomb -->
    </div>
    <div class="row g-4">
      <?php foreach ($profiles as $pr): ?>
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="profile-card h-100">
            <div class="d-flex align-items-center mb-3">
              <img class="profile-avatar me-3" src="<?= htmlspecialchars($pr['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($pr['display_name'], ENT_QUOTES, 'UTF-8') ?>">
              <div>
                <strong><?= htmlspecialchars($pr['display_name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                <?php if (!empty($pr['favorite_brand'])): ?>
                  <span class="fav-brand"><i class="fa-solid fa-heart me-1"></i> <?= htmlspecialchars($pr['favorite_brand'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
            </div>
            <?php if (!empty($pr['bio'])): ?>
              <p class="mb-0"><?= htmlspecialchars($pr['bio'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($profiles)): ?>
        <div class="col-12"><div class="alert alert-secondary">Hamarosan érkeznek a visszajelzések!</div></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- LÁBLÉC -->
<footer class="py-4 border-top bg-white">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between">
    <div><strong>Hair Heaven</strong> &middot; Premium hair & scalp care</div>
    <div class="text-muted">© <?= date('Y') ?> Hair Heaven · Minden jog fenntartva</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
