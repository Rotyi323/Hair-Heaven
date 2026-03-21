<?php
$isLogged  = !empty($_SESSION['belepve']);
$username  = $_SESSION['username'] ?? null;
$avatarInSession = $_SESSION['avatar'] ?? '';
$navAvatar = $avatarInSession ?: '/assets/img/avatar-placeholder.svg';

if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
?>
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/">Hair <span class="dot">Heaven</span></a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hhNav" aria-controls="hhNav" aria-expanded="false" aria-label="Menü">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="hhNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= ($activePage==='home'?'active':'') ?>" href="/">Főoldal</a></li>
        <li class="nav-item"><a class="nav-link <?= ($activePage==='shop'?'active':'') ?>" href="/aruhaz.php">Áruház</a></li>
        <li class="nav-item"><a class="nav-link <?= ($activePage==='services'?'active':'') ?>" href="/szolgaltatasok.php">Szolgáltatások</a></li>
      </ul>

      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-2">
          <a class="btn btn-outline-dark" href="/kosar.php"><i class="fa-solid fa-bag-shopping"></i> Kosár</a>
        </li>

        <?php if ($isLogged): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?= e($navAvatar) ?>" alt="profil" class="profile-thumb">
              <span class="d-none d-md-inline"><?= e($username ?: 'Profil') ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/profil.php">Profilom</a></li>
              <li><a class="dropdown-item" href="/foglalasaim.php">Foglalásaim</a></li>
              <li><a class="dropdown-item" href="/rendeleseim.php">Rendeléseim</a></li>
              <li><a class="dropdown-item" href="/kezeleseim.php">Kezeléseim</a></li>

              <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/admin/index.php"><i class="fa-solid fa-toolbox me-1"></i> Admin felület</a></li>
                <li><a class="dropdown-item" href="/admin/stock.php"><i class="fa-solid fa-boxes-stacked me-1"></i> Készlet</a></li>
                <li><a class="dropdown-item" href="/admin/kezelesek.php"><i class="fa-solid fa-heart-pulse me-1"></i> Kezelések</a></li>
              <?php endif; ?>

              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/logout.php">Kijelentkezés</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item me-2">
            <a class="btn btn-outline-dark" href="/belepes.php"><i class="fa-solid fa-right-to-bracket"></i> Bejelentkezés</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-cta text-white" href="/regisztracio.php"><i class="fa-solid fa-user-plus"></i> Regisztráció</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>