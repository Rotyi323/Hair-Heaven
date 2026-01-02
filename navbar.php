<?php
// Közös navigáció – include-old minden oldal tetején a <body> után
// Opcionális bemenet: $activePage = 'home'|'shop'|'services'|'clients'

if (!isset($activePage)) $activePage = '';
$isLogged = !empty($_SESSION['belepve']);
$username = $_SESSION['username'] ?? null;

// Aktív link helper
function hh_active($key, $activePage) {
  return $activePage === $key ? 'active' : '';
}
?>
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
        <li class="nav-item"><a class="nav-link <?= hh_active('home', $activePage) ?>" href="/">Főoldal</a></li>
        <li class="nav-item"><a class="nav-link <?= hh_active('shop', $activePage) ?>" href="/aruhaz.php">Áruház</a></li>
        <li class="nav-item"><a class="nav-link <?= hh_active('services', $activePage) ?>" href="/szolgaltatasok.php">Szolgáltatások</a></li>
        <li class="nav-item"><a class="nav-link <?= hh_active('clients', $activePage) ?>" href="/ugyfelek.php">Elégedett vásárlók</a></li>
      </ul>

      <ul class="navbar-nav ms-auto align-items-lg-center">
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
          <li class="nav-item me-2">
            <a class="btn btn-sm btn-outline-dark" href="/regisztracio.php">
              <i class="fa-solid fa-user-plus me-1"></i> Regisztráció
            </a>
          </li>
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
