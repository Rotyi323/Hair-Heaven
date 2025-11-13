<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
//A menüsor ahoz viszonyítva változik, hogy éppen be van-e lépve az adott felhasználó, illetve admin jogkörrel is rendelkezik-e.
if ($_SESSION["jog"] == "admin") {
    $admin = '<li><a class="dropdown-item" href="/admin/">Admin</a></li>';
}
if ($_SESSION["belepve"] == true) {
    echo '
    <header class="py-3 mb-4 border-bottom">
    <div class="container d-flex flex-wrap justify-content-center">
        <a href="/" class="d-flex align-items-center mb-3 mb-lg-0 me-lg-auto text-dark text-decoration-none">
        <svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"/></svg>

        <b><span class="fs-4">TelefonFix</span></b>

        </a>

        <ul class="nav me-auto">
        <li class="nav-item"><a href="/" class="nav-link link-dark px-2 active" aria-current="page"> <i class="fa fa-home" style="color:black"></i> Főoldal</a></li>
        <li class="nav-item"><a href="/szerviz.php" class="nav-link link-dark px-2"><i class="fa fa-wrench" style="color:#a4a8a2"></i>Szerviz</a></li>
        <li class="nav-item"><a href="/aruhaz.php" class="nav-link link-dark px-2"><i class="fa fa-dollar" style="color:#55e800"></i> Áruház</a></li>
        <li class="nav-item"><a href="/informacio.php" class="nav-link link-dark px-2"><i class="fa fa-info-circle" style="color:#479aff"></i> Rólunk</a></li>
        <li class="nav-item"><a href="/informacio.php#elerheto" class="nav-link link-dark px-2"> <i class="fa fa-info-circle" style="color:#479aff"></i> Elérhetőség</a></li>
        <li class="nav-item"><a href="/kosar.php" class="nav-link link-dark px-2 my-auto"><i class="fa fa-shopping-cart" style="color:#8385a6" aria-hidden="true"></i> Kosár</a></li>
        </ul>
        
        <div class="col-12 col-lg-auto mb-3 mb-lg-0">
        
        <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        ' . $_SESSION["nev"] . ' 
        </button>
        <ul class="dropdown-menu">
        ' . $admin . '
        <li><a class="dropdown-item" href="/profil.php">Profil</a></li>
        <li><a class="dropdown-item" href="/rendeleseim.php">Rendeléseim</a></li>
        <li><a class="dropdown-item" href="/logout.php">Kijelentkezés</a></li>
        </ul>
    </div>

        </div>
    </div>
    </header>';
} else {
    echo '
    <header class="py-3 mb-4 border-bottom">
    <div class="container d-flex flex-wrap justify-content-center">
        <a href="/" class="d-flex align-items-center mb-3 mb-lg-0 me-lg-auto text-dark text-decoration-none">
        <svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"/></svg>
        <b><span class="fs-4">TelefonFix</span></b>


        </a>

        <ul class="nav me-auto">
        <li class="nav-item"><a href="/" class="nav-link link-dark px-2 active" aria-current="page"> <i class="fa fa-home" style="color:black"></i> Főoldal</a></li>
        <li class="nav-item"><a href="/szerviz.php" class="nav-link link-dark px-2"><i class="fa fa-wrench" style="color:#a4a8a2"></i>Szerviz</a></li>
        <li class="nav-item"><a href="/aruhaz.php" class="nav-link link-dark px-2"><i class="fa fa-dollar" style="color:#55e800"></i> Áruház</a></li>
        <li class="nav-item"><a href="/informacio.php" class="nav-link link-dark px-2"><i class="fa fa-info-circle" style="color:#479aff"></i> Rólunk</a></li>
        <li class="nav-item"><a href="/informacio.php#elerheto" class="nav-link link-dark px-2"> <i class="fa fa-info-circle" style="color:#479aff"></i> Elérhetőség</a></li>
        <li class="nav-item"> <a href="/kosar.php" class="nav-link link-dark px-2 my-auto"><i class="fa fa-shopping-cart" style="color:#8385a6" aria-hidden="true"></i> Kosár</a></li>
    </ul>
    
        <div class="col-12 col-lg-auto mb-3 mb-lg-0">
        <a href="/belepes.php" class="btn btn-primary text-white">Bejelentkezés</a>
        <a href="/regisztracio.php" class="btn btn-primary text-white">Regisztráció</a>
        </div>
    </div>
    </header>';
}