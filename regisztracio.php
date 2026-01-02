<?php
session_start();
require_once __DIR__ . '/biztonsag.php';
require_once __DIR__ . '/connect.php';

$mysqli = db(); // mysqli | null

if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

/* -------- CSRF (ha nincs közös csrf_field/csrf_verify, lokális token) -------- */
$use_local_csrf = !function_exists('csrf_field');
if ($use_local_csrf) {
  if (empty($_SESSION['_token_reg'])) {
    $_SESSION['_token_reg'] = bin2hex(random_bytes(32));
  }
  function local_csrf_field(){
    return '<input type="hidden" name="_token" value="'.e($_SESSION['_token_reg'] ?? '').'">';
  }
}

/* -------- Állapot -------- */
$errors   = [];
$username = '';
$email    = '';

/* -------- POST feldolgozás -------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // CSRF
  if (function_exists('csrf_verify')) {
    if (!csrf_verify()) $errors[] = 'Biztonsági hiba (CSRF). Kérlek próbáld újra.';
  } else {
    $token = $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['_token_reg'] ?? '', $token)) {
      $errors[] = 'Biztonsági hiba (CSRF). Kérlek frissítsd az oldalt és próbáld újra.';
    }
  }

  // Beolvasás
  $username = (string)($_POST['username'] ?? '');
  $email    = trim((string)($_POST['email'] ?? ''));
  $pass     = (string)($_POST['password'] ?? '');
  $pass2    = (string)($_POST['password_confirm'] ?? '');

  // --- Felhasználónév szabályok ---
  // - hossz: 3–100 karakter (szóköz megengedett)
  // - megengedett karakterek: betűk (ékezetes is), számok, pont, aláhúzás, kötőjel, szóköz
  // - legalább 3 betűnek kell szerepelnie benne (így nem lehet csak whitespace/dísz)
  $username_trim = trim($username);
  if ($username_trim === '' || mb_strlen($username, 'UTF-8') < 3 || mb_strlen($username, 'UTF-8') > 100) {
    $errors[] = 'A felhasználónév kötelező (3–100 karakter).';
  } elseif (!preg_match('/^[\p{L}0-9._\- ]+$/u', $username)) {
    $errors[] = 'A felhasználónév csak betűt, számot, pontot, aláhúzást, kötőjelet és szóközt tartalmazhat.';
  } else {
    // legalább 3 betű ellenőrzése (Unicode)
    preg_match_all('/\p{L}/u', $username, $m);
    if (count($m[0] ?? []) < 3) {
      $errors[] = 'A felhasználónévben legalább 3 betűnek kell szerepelnie.';
    }
  }

  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
    $errors[] = 'Adj meg érvényes e-mail címet (max. 190 karakter).';
  }

  if (mb_strlen($pass) < 8) {
    $errors[] = 'A jelszó legalább 8 karakter legyen.';
  }
  if ($pass !== $pass2) {
    $errors[] = 'A két jelszó nem egyezik.';
  }

  if (!$mysqli) {
    $errors[] = 'Az adatbázis jelenleg nem elérhető. Kérlek próbáld később.';
  }

  if (empty($errors) && $mysqli) {
    try {
      // Egyediség ellenőrzése: username VAGY email foglalt?
      $stmt = $mysqli->prepare("SELECT username, email FROM users WHERE username = ? OR email = ? LIMIT 1");
      $stmt->bind_param('ss', $username, $email);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($row = $res->fetch_assoc()) {
        if (strcasecmp($row['username'], $username) === 0) $errors[] = 'Ez a felhasználónév már foglalt.';
        if (strcasecmp($row['email'],    $email)    === 0) $errors[] = 'Ezzel az e-mail címmel már regisztráltak.';
      }
      $stmt->close();

      if (empty($errors)) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $role = 'customer'; // enum('owner','customer')
        $stmt = $mysqli->prepare("
          INSERT INTO users (username, email, password_hash, role, created_at, updated_at)
          VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param('ssss', $username, $email, $hash, $role);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['_token_reg']);
        header('Location: /belepes.php?registered=1');
        echo '<!doctype html><meta http-equiv="refresh" content="0;url=/belepes.php?registered=1">';
        exit;
      }

    } catch (Throwable $ex) {
      $msg = strtolower($ex->getMessage());
      if (strpos($msg, 'duplicate') !== false || strpos($msg, 'unique') !== false) {
        $errors[] = 'A megadott felhasználónév vagy e-mail már használatban van.';
      } else {
        $errors[] = 'Váratlan hiba történt a mentés közben.';
      }
    }
  }
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hair Heaven – Regisztráció</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    :root{ --hh-primary:#c76df0; --hh-dark:#1c1a27; --hh-muted:#6c6a75; --hh-bg:#faf7ff; }
    body{ background:var(--hh-bg); color:var(--hh-dark); }
    .navbar{ background:#fff; box-shadow:0 6px 20px rgba(0,0,0,.06); }
    .navbar-brand{ font-weight:800; letter-spacing:.5px; color:var(--hh-dark); }
    .navbar-brand .dot{ color:var(--hh-primary); }
    .nav-link{ font-weight:600; color:var(--hh-dark); }
    .nav-link:hover, .nav-link.active{ color:var(--hh-primary); }

    .auth-card{ background:#fff; border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .form-control{ border-radius:10px; }
    .page-title{ font-weight:800; letter-spacing:.3px; }
    .muted{ color:var(--hh-muted); }
  </style>
  <link rel="stylesheet" href="/assets/hairheaven.css">

</head>
<body>

<?php $activePage = ''; include __DIR__ . '/navbar.php'; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <h1 class="page-title mb-2">Regisztráció</h1>
      <div class="muted mb-4">Hozz létre fiókot az időpontfoglaláshoz és rendeléshez.</div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <strong>Hoppá!</strong> Kérlek javítsd az alábbi hibákat:
          <ul class="mb-0 mt-2">
            <?php foreach ($errors as $err): ?>
              <li><?= e($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="auth-card">
        <form method="post" action="/regisztracio.php" novalidate>
          <?php
            if (function_exists('csrf_field')) { echo csrf_field(); }
            else { echo local_csrf_field(); }
          ?>

          <div class="mb-3">
            <label for="username" class="form-label">Felhasználónév</label>
            <input
              type="text"
              class="form-control"
              id="username"
              name="username"
              value="<?= e($username) ?>"
              required
              minlength="3"
              maxlength="100"
              pattern="[A-Za-z0-9._\- ÁÉÍÓÖŐÚÜŰáéíóöőúüű]+"
              placeholder="pl. Anna Kovács">
            <div class="form-text">Megengedett: betűk (ékezetes is), számok, pont, aláhúzás, kötőjel, szóköz. Legalább 3 betű legyen benne.</div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">E-mail cím</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= e($email) ?>" required maxlength="190"
                   placeholder="te@pelda.hu">
          </div>

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label for="password" class="form-label">Jelszó</label>
              <input type="password" class="form-control" id="password" name="password"
                     required minlength="8" maxlength="100" autocomplete="new-password"
                     placeholder="Legalább 8 karakter">
            </div>
            <div class="col-12 col-md-6">
              <label for="password_confirm" class="form-label">Jelszó megerősítése</label>
              <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                     required minlength="8" maxlength="100" autocomplete="new-password"
                     placeholder="Írd be újra a jelszót">
            </div>
          </div>

          <div class="d-grid mt-4">
            <button class="btn btn-cta text-white btn-lg" type="submit">
              <i class="fa-solid fa-user-plus me-1"></i> Fiók létrehozása
            </button>
          </div>
        </form>

        <div class="text-center mt-3">
          Már van fiókod? <a href="/belepes.php">Jelentkezz be</a>.
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="py-4 border-top bg-white">
  <div class="container d-flex flex-column flex-lg-row align-items-center justify-content-between">
    <div><strong>Hair Heaven</strong> &middot; Premium hair & scalp care</div>
    <div class="text-muted">© <?= date('Y') ?> Hair Heaven · Minden jog fenntartva</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// kliens oldali kényelmi ellenőrzés; szerveroldali a mérvadó
(function(){
  const p1 = document.getElementById('password');
  const p2 = document.getElementById('password_confirm');
  function checkMatch(){
    if (p1.value && p2.value && p1.value !== p2.value) {
      p2.setCustomValidity('A két jelszó nem egyezik');
    } else {
      p2.setCustomValidity('');
    }
  }
  p1.addEventListener('input', checkMatch);
  p2.addEventListener('input', checkMatch);
})();
</script>
</body>
</html>
