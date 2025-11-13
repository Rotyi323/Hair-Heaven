<?php
error_reporting(E_ERROR | E_PARSE); //Kijelentkezés
session_start();
session_destroy();
header("Location: /");
?>