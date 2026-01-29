<?php
session_start();
require_once __DIR__ . '/biztonsag.php';
require_once __DIR__ . '/connect.php';

$cspNonce = $GLOBALS['CSP_NONCE'] ?? '';
$mysqli = db();

$isLogged = !empty($_SESSION['belepve']) && !empty($_SESSION['user_id']);
$userId = (int) ($_SESSION['user_id'] ?? 0);

function e($s)
{
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

// Szolgáltatások lista
$services = [];
if ($mysqli) {
  $res = $mysqli->query("
    SELECT id, name, duration_minutes, price, description
    FROM services
    WHERE is_active=1
    ORDER BY id ASC
  ");
  while ($row = $res->fetch_assoc())
    $services[] = $row;
}
?>
<!doctype html>
<html lang="hu">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Szolgáltatások – Hair Heaven</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="/assets/hairheaven.css">
  <style>
    .service-card {
      background: #fff;
      border-radius: 14px;
      padding: 18px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
    }

    .price-tag {
      font-weight: 800;
      font-size: 1.1rem;
    }

    .muted {
      color: #6c6a75;
    }
  </style>
</head>

<body>

  <?php $activePage = 'services';
  include __DIR__ . '/navbar.php'; ?>

  <div class="container my-4">
    <div class="d-flex align-items-end justify-content-between mb-3">
      <div>
        <h1 class="page-title mb-1">Szolgáltatásaink</h1>
        <div class="text-muted">Foglalj időpontot kedvenc kezelésedre, pár kattintással.</div>
      </div>
    </div>

    <?php if (!$isLogged): ?>
      <div class="alert alert-warning fw-semibold">
        Időpont foglalásához
        <a class="link-underline link-underline-opacity-0" href="/belepes.php"> jelentkezz be</a> vagy
        <a class="link-underline link-underline-opacity-0" href="/regisztracio.php">regisztrálj</a>.
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <?php foreach ($services as $s): ?>
        <div class="col-12 col-lg-4">
          <div class="service-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <h5 class="mb-0"><?= e($s['name']) ?></h5>
              <span class="price-tag"><?= number_format((float) $s['price'], 0, ',', ' ') ?> Ft</span>
            </div>
            <div class="muted mb-3">
              <i class="fa-regular fa-clock me-1"></i> <?= (int) $s['duration_minutes'] ?> perc
            </div>
            <?php if (!empty($s['description'])): ?>
              <p class="mb-3"><?= e($s['description']) ?></p>
            <?php endif; ?>

            <form class="booking-form" data-service="<?= (int) $s['id'] ?>">
              <?= csrf_field() ?>
              <div class="row g-2 align-items-end">
                <div class="col-7">
                  <label class="form-label mb-1">Dátum</label>
                  <input type="date" class="form-control form-control-sm js-date" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-5">
                  <label class="form-label mb-1">Idő</label>
                  <select class="form-select form-select-sm js-time" disabled>
                    <option value="">--:--</option>
                  </select>
                </div>
              </div>
              <button type="submit" class="btn btn-cta text-white w-100 mt-3" <?= $isLogged ? '' : 'disabled' ?>>
                <i class="fa-regular fa-calendar-check me-1"></i> Időpontot foglalok
              </button>
              <?php if (!$isLogged): ?>
                <div class="small text-muted mt-2">Bejelentkezés után tudsz foglalni.</div>
              <?php endif; ?>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script nonce="<?= e($cspNonce) ?>">
    function fillSelect(select, values) {
      select.innerHTML = '';
      if (!values || values.length === 0) {
        const opt = document.createElement('option');
        opt.value = ''; opt.textContent = '--:--';
        select.appendChild(opt);
        select.disabled = true;
        return;
      }
      values.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v; opt.textContent = v;
        select.appendChild(opt);
      });
      select.disabled = false;
    }

    async function loadSlots(serviceId, ymd, selectEl) {
      const url = `/api/booking_slots.php?service_id=${encodeURIComponent(serviceId)}&date=${encodeURIComponent(ymd)}`;
      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data && data.ok) fillSelect(selectEl, data.slots);
        else fillSelect(selectEl, []);
      } catch (e) { fillSelect(selectEl, []); }
    }

    document.querySelectorAll('.booking-form').forEach(form => {
      const serviceId = form.dataset.service;
      const dateInp = form.querySelector('.js-date');
      const timeSel = form.querySelector('.js-time');
      const btn = form.querySelector('button[type="submit"]');

      dateInp?.addEventListener('change', () => {
        const ymd = dateInp.value;
        const d = new Date(ymd + 'T00:00:00');
        const wd = d.getDay(); // 0=vas, 6=szo
        if (isNaN(d.getTime()) || wd === 0 || wd === 6) { fillSelect(timeSel, []); return; }
        loadSlots(serviceId, ymd, timeSel);
      });

      form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        if (!dateInp.value || !timeSel.value) { return; }

        const fd = new FormData(form);
        fd.set('service_id', serviceId);
        fd.set('date', dateInp.value);
        fd.set('time', timeSel.value);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Foglalás...';

        try {
          const res = await fetch('/api/booking_create.php', { method: 'POST', body: fd });
          const data = await res.json();
          if (data && data.ok) {
            await loadSlots(serviceId, dateInp.value, timeSel);
            alert('Foglalás rögzítve ✔');
            localStorage.setItem('hh_booking_changed', String(Date.now()));
          } else {
            alert(data?.msg || 'Váratlan hiba.');
          }
        } catch (e) {
          alert('Váratlan hiba.');
        } finally {
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-regular fa-calendar-check me-1"></i> Időpontot foglalok';
        }
      });
    });

    // Másik lapon történt módosítás -> frissítjük a látható selecteket
    window.addEventListener('storage', (ev) => {
      if (ev.key === 'hh_booking_changed') {
        document.querySelectorAll('.booking-form').forEach(form => {
          const serviceId = form.dataset.service;
          const dateInp = form.querySelector('.js-date');
          const timeSel = form.querySelector('.js-time');
          if (dateInp?.value) loadSlots(serviceId, dateInp.value, timeSel);
        });
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>