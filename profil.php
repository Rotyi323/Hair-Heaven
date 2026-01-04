<?php
// Hair Heaven – Profilom
require_once __DIR__ . '/biztonsag.php';
session_start();
require_once __DIR__ . '/connect.php';

$cspNonce = $GLOBALS['CSP_NONCE'] ?? '';
$mysqli   = db();

// --- csak bejelentkezve ---
if (empty($_SESSION['belepve']) || empty($_SESSION['user_id'])) {
  header('Location: /belepes.php');
  exit;
}
$userId = (int)$_SESSION['user_id'];

// --- akt. user betöltése ---
$user = null;
if ($mysqli) {
  $stmt = $mysqli->prepare("SELECT id, username, email, avatar FROM users WHERE id = ? LIMIT 1");
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();
}
if (!$user) { http_response_code(404); exit('Felhasználó nem található.'); }

// későbbi törléshez megőrizzük a jelenlegi avatar elérési útját
$oldAvatar = $user['avatar'] ?? null;

// --- üzenetek (flash-szerű) ---
$okMsg = $errMsg = '';

// --- POST feldolgozás (mentés) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_validate();

  // 1) opcionális avatar feltöltés
  $newAvatarPath = null; // relatív web útvonal (pl. /uploads/avatars/u1_...jpg)
  if (!empty($_FILES['avatar']['name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
    $f = $_FILES['avatar'];

    // Fájl limit + MIME ellenőrzés
    if ($f['error'] !== UPLOAD_ERR_OK) {
      $errMsg = 'Avatar feltöltési hiba.';
    } elseif ($f['size'] > 5 * 1024 * 1024) {
      $errMsg = 'Túl nagy fájl (max 5 MB).';
    } else {
      $fi = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($fi, $f['tmp_name']);
      finfo_close($fi);

      $ext = null;
      if ($mime === 'image/jpeg')  $ext = 'jpg';
      if ($mime === 'image/png')   $ext = 'png';
      if ($mime === 'image/webp')  $ext = 'webp';

      if (!$ext) {
        $errMsg = 'Csak JPG/PNG/WebP engedélyezett.';
      } else {
        // Célkönyvtár
        $absRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? __DIR__, '/');
        $dirAbs  = $absRoot . '/uploads/avatars';
        if (!is_dir($dirAbs)) @mkdir($dirAbs, 0775, true);

        // Biztonságos fájlnév
        $fname = 'u'.$userId.'_'.time().'.'.$ext;
        $dstAbs = $dirAbs . '/' . $fname;

        if (!@move_uploaded_file($f['tmp_name'], $dstAbs)) {
          $errMsg = 'Nem sikerült menteni a fájlt.';
        } else {
          // Webes elérési út (relatív)
          $newAvatarPath = '/uploads/avatars/'.$fname;
        }
      }
    }
  }

  // 2) opcionális jelszócsere
  $pwOk = true;
  $pwCurrent = trim((string)($_POST['current_password'] ?? ''));
  $pw1 = trim((string)($_POST['new_password'] ?? ''));
  $pw2 = trim((string)($_POST['new_password_confirm'] ?? ''));

  $changePassword = ($pw1 !== '' || $pw2 !== '' || $pwCurrent !== '');
  if ($changePassword) {
    if (mb_strlen($pw1) < 8) {
      $pwOk = false; $errMsg = 'Az új jelszó legalább 8 karakter legyen.';
    } elseif ($pw1 !== $pw2) {
      $pwOk = false; $errMsg = 'Az új jelszavak nem egyeznek.';
    } else {
      // Ellenőrizzük a jelenlegi jelszót
      $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE id=? LIMIT 1");
      $stmt->bind_param('i', $userId);
      $stmt->execute();
      $hash = (string)($stmt->get_result()->fetch_assoc()['password_hash'] ?? '');
      $stmt->close();
      if ($hash === '' || !password_verify($pwCurrent, $hash)) {
        $pwOk = false; $errMsg = 'A jelenlegi jelszó helytelen.';
      }
    }
  }

  // Ha nincs hiba: update(ek)
  if ($errMsg === '' && $mysqli) {
    $mysqli->begin_transaction();
    try {
      if ($newAvatarPath !== null) {
        $stmt = $mysqli->prepare("UPDATE users SET avatar=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param('si', $newAvatarPath, $userId);
        $stmt->execute();
        $stmt->close();

        // session frissítés a navbárhoz
        $_SESSION['avatar'] = $newAvatarPath;
      }

      if ($changePassword && $pwOk) {
        $newHash = password_hash($pw1, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE users SET password_hash=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param('si', $newHash, $userId);
        $stmt->execute();
        $stmt->close();
      }

      $mysqli->commit();
      $okMsg = 'Változtatások mentve.';

      // Új adat visszatöltése (előnézethez is)
      $stmt = $mysqli->prepare("SELECT id, username, email, avatar FROM users WHERE id = ? LIMIT 1");
      $stmt->bind_param('i', $userId);
      $stmt->execute();
      $user = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      // --- RÉGI AVATAR TÖRLÉSE BIZTONSÁGOSAN ---
      // Csak akkor törlünk, ha:
      //  - volt régi avatar,
      //  - lett új avatar (tehát tényleg cserélt),
      //  - és a régi az /uploads/avatars/ mappában van.
      if ($newAvatarPath && $oldAvatar && strpos($oldAvatar, '/uploads/avatars/') === 0) {
        $absRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? __DIR__, '/');
        $oldAbs  = $absRoot . $oldAvatar;
        if (is_file($oldAbs)) @unlink($oldAbs);
      }

    } catch (Throwable $e) {
      $mysqli->rollback();
      // error_log('Profil mentés hiba: '.$e->getMessage());
      $errMsg = 'Váratlan hiba történt a mentés közben.';
    }
  }
}

// Avatar megjelenítés (fallback)
$avatarUrl = !empty($user['avatar']) ? $user['avatar'] : '/assets/img/avatar-placeholder.png';
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profilom – Hair Heaven</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    .profile-card{ background:#fff; border-radius:16px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .avatar-preview{ width:200px; height:200px; border-radius:25%; object-fit:cover; border:4px solid #fff; box-shadow:0 8px 24px rgba(0,0,0,.12); }
    @media (max-width:576px){ .avatar-preview{ width:130px; height:130px; } }
    .form-section-title{ font-weight:800; margin-bottom:.5rem; }
  </style>
</head>
<body>

<?php $activePage = 'profile'; include __DIR__ . '/navbar.php'; ?>

<div class="container my-4">
  <h1 class="page-title mb-3">Profilom</h1>

  <?php if ($okMsg): ?>
    <div class="alert alert-success"><?= e($okMsg) ?></div>
  <?php endif; ?>
  <?php if ($errMsg): ?>
    <div class="alert alert-danger"><?= e($errMsg) ?></div>
  <?php endif; ?>

  <form class="profile-card" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- AVATAR -->
    <div class="row g-4 align-items-center">
      <div class="col-auto">
        <img id="avatarPreview" src="<?= e($avatarUrl) ?>" alt="Avatar" class="avatar-preview">
      </div>
      <div class="col">
        <div class="form-section-title">Profilkép</div>
        <div class="text-muted mb-2">JPG / PNG / WebP • max 5 MB</div>
        <input class="form-control" type="file" name="avatar" id="avatar" accept="image/png,image/jpeg,image/webp">
      </div>
    </div>

    <hr class="my-4">

    <!-- PASSWORD -->
    <div class="row g-3">
      <div class="col-12">
        <div class="form-section-title">Jelszócsere</div>
        <div class="text-muted mb-2">Új jelszó megadásához add meg a jelenlegi jelszavad is. Minimum 8 karakter.</div>
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Jelenlegi jelszó</label>
        <input type="password" class="form-control" name="current_password" autocomplete="current-password">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Új jelszó</label>
        <input type="password" class="form-control" name="new_password" autocomplete="new-password" minlength="8">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Új jelszó megerősítése</label>
        <input type="password" class="form-control" name="new_password_confirm" autocomplete="new-password" minlength="8">
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button class="btn btn-cta text-white" type="submit">
        <i class="fa-solid fa-floppy-disk me-1"></i> Mentés
      </button>
      <button class="btn btn-outline-secondary" type="button" id="discardBtn">
        <i class="fa-solid fa-rotate-left me-1"></i> Módosítások elvetése
      </button>
    </div>
  </form>
</div>

<script nonce="<?= e($cspNonce) ?>">
// Avatar előnézet
document.getElementById('avatar')?.addEventListener('change', function(){
  const [file] = this.files;
  if (!file) return;
  const url = URL.createObjectURL(file);
  document.getElementById('avatarPreview').src = url;
});

// Elvetés = oldal újratöltése (visszaállítja az eredeti állapotot)
document.getElementById('discardBtn')?.addEventListener('click', () => { location.reload(); });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
