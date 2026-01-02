<?php
// Navbar – közös
$isLogged  = !empty($_SESSION['belepve']);
$username  = $_SESSION['username'] ?? null;
$avatar    = $_SESSION['avatar'] ?? null;
?>
<nav class="navbar navbar-expand-lg sticky-top">
    <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    :root{
      --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75;
    }
    /* Linkek */
    .navbar .nav-link{ font-weight:600; color:var(--hh-dark); transition:color .15s ease; }
    .navbar .nav-link:hover,
    .navbar .nav-link.active{ color:var(--hh-primary); }

    /* Avatar badge */
    .navbar .hh-avatar{
      width:28px;height:28px;border-radius:50%;object-fit:cover;border:2px solid #fff;
      box-shadow:0 2px 8px rgba(0,0,0,.15);
    }
    .navbar .hh-avatar-fallback{
      width:28px;height:28px;border-radius:50%;background:#f3e7ff;color:#7e3dbf;
      display:inline-flex;align-items:center;justify-content:center;
      box-shadow:0 2px 8px rgba(0,0,0,.15);
    }

    /* Sáv alap */
    .navbar{ background:#fff; box-shadow:0 6px 20px rgba(0,0,0,.06); }
    .navbar-brand{ font-weight:800; letter-spacing:.5px; color:var(--hh-dark); }
    .navbar-brand .dot{ color:var(--hh-primary); }
  </style>

  <div class="container">
    <a class="navbar-brand" href="/">Hair <span class="dot">Heaven</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hhNav" aria-controls="hhNav" aria-expanded="false" aria-label="Menü">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="hhNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= ($activePage==='home'?'active':'') ?>" href="/">Főoldal</a></li>
        <li class="nav-item"><a class="nav-link <?= ($activePage==='shop'?'active':'') ?>" href="/aruhaz.php">Áruház</a></li>
        <li class="nav-item"><a class="nav-link <?= ($activePage==='services'?'active':'') ?>" href="/szolgaltatasok.php">Szolgáltatások</a></li>
        <li class="nav-item"><a class="nav-link <?= ($activePage==='clients'?'active':'') ?>" href="/ugyfelek.php">Elégedett vásárlók</a></li>
      </ul>

      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-2">
          <a class="btn btn-sm btn-outline-dark" href="/kosar.php">
            <i class="fa-solid fa-bag-shopping me-1"></i> Kosár
          </a>
        </li>

        <?php if (!empty($_SESSION['belepve'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
              <?php if (!empty($avatar)): ?>
                <img src="<?= e($avatar) ?>" alt="profil" class="hh-avatar">
              <?php else: ?>
                <span class="hh-avatar-fallback">
                  <i class="fa-solid fa-user" style="font-size:.85rem;"></i>
                </span>
              <?php endif; ?>
              <span><?= e($username ?: 'Profil') ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/profil.php">Profilom</a></li>
              <li><a class="dropdown-item" href="/rendeleseim.php">Rendeléseim</a></li>
              <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/admin/index.php"><i class="fa-solid fa-toolbox me-1"></i> Admin felület</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/logout.php">Kijelentkezés</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item me-2">
            <a class="btn btn-sm btn-outline-dark" href="/belepes.php">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Bejelentkezés
            </a>
          </li>
          <li class="nav-item">
            <a class="btn btn-sm btn-cta text-white" href="/regisztracio.php">
              <i class="fa-solid fa-user-plus me-1"></i> Regisztráció
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
