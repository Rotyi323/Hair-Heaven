<?php
session_start();
require_once __DIR__ . '/../security.php';
try { csrf_validate(); } catch (Throwable $e) { http_response_code(400); exit('Bad CSRF'); }
if (empty($_SESSION['belepve'])) { header('Location: /login.php'); exit; }

unset($_SESSION['cart']);
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/cart.php'));

