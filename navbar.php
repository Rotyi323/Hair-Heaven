<?php
// Navbar – közös
$isLogged  = !empty($_SESSION['belepve']);
$username  = $_SESSION['username'] ?? null;
$avatarInSession = $_SESSION['avatar'] ?? '';
// Mindig legyen használható kép: ha nincs user avatar, jöjjön a placeholder
$navAvatar = $avatarInSession ?: '/assets/img/avatar-placeholder.svg';
?>
<nav class="navbar navbar-expand-lg sticky-top">
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    :root{
      --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75;
      --hh-nav-height: 72px; --hh-nav-font:1rem; --hh-nav-brand:1.65rem;
      --hh-nav-btn-py:.60rem; --hh-nav-btn-px:1.00rem; --hh-avatar:48px;
    }
    @media (min-width: 992px){
      :root{ --hh-nav-height:80px; --hh-nav-font:1.06rem; --hh-nav-brand:1.85rem; --hh-avatar:54px; }
    }
    .navbar{ background:#fff; box-shadow:0 6px 20px rgba(0,0,0,.06); min-height:var(--hh-nav-height); }
    .navbar-brand{ font-weight:800; letter-spacing:.5px; color:var(--hh-dark); font-size:var(--hh-nav-brand); }
    .navbar-brand .dot{ color:var(--hh-primary); }
    .navbar .nav-link{ font-weight:600; color:var(--hh-dark); transition:color .15s ease; font-size:var(--hh-nav-font); padding:.6rem .9rem; }
    .navbar .nav-link:hover,.navbar .nav-link.active{ color:var(--hh-primary); }
    .navbar .btn,.navbar .btn-cta{ font-size:.98rem; padding:var(--hh-nav-btn-py) var(--hh-nav-btn-px); border-radius:12px; }
    /* egységes avatar osztály: profile-thumb */
    .navbar .profile-thumb{
      width:var(--hh-avatar); height:var(--hh-avatar);
      border-radius:50%; object-fit:cover; border:2px solid #fff;
      box-shadow:0 4px 12px rgba(0,0,0,.15);
    }
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
          <a class="btn btn-outline-dark" href="/kosar.php"><i class="fa-solid fa-bag-shopping me-1"></i> Kosár</a>
        </li>

        <?php if ($isLogged): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
              <!-- MINDIG képet renderelünk: user avatar vagy placeholder -->
              <img src="<?= e($navAvatar) ?>" alt="profil" class="profile-thumb" />
              <span class="d-none d-md-inline"><?= e($username ?: 'Profil') ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/profil.php">Profilom</a></li>
              <li><a class="dropdown-item" href="/foglalasaim.php">Foglalásaim</a></li>
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
            <a class="btn btn-outline-dark" href="/belepes.php"><i class="fa-solid fa-right-to-bracket me-1"></i> Bejelentkezés</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-cta text-white" href="/regisztracio.php"><i class="fa-solid fa-user-plus me-1"></i> Regisztráció</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
