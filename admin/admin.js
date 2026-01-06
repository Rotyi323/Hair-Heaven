// Admin UI – inline JS nélkül (CSP-kompatibilis)

(function () {
  const placeholderImg = '/assets/img/placeholder.png';

  // --- Termék űrlap elemei
  const pfTitle = document.getElementById('pfTitle');
  const pId     = document.getElementById('p_id');
  const pBrand  = document.getElementById('p_brand');
  const pName   = document.getElementById('p_name');
  const pType   = document.getElementById('p_type');
  const pDesc   = document.getElementById('p_desc');
  const pPrice  = document.getElementById('p_price');
  const pActive = document.getElementById('p_active');
  const pFeat   = document.getElementById('p_feat');
  const pImage  = document.getElementById('p_image');
  const pPrev   = document.getElementById('p_preview');
  const btnResetProduct = document.getElementById('btnResetProduct');

  // --- Szolgáltatás űrlap elemei
  const sfTitle = document.getElementById('sfTitle');
  const sId     = document.getElementById('s_id');
  const sName   = document.getElementById('s_name');
  const sMin    = document.getElementById('s_minutes');
  const sPrice  = document.getElementById('s_price');
  const sDesc   = document.getElementById('s_desc');
  const sActive = document.getElementById('s_active');
  const btnResetService = document.getElementById('btnResetService');

  // --- Lista eseménykezelők (edit/delete)

  // Termék szerkesztés gombok
  document.getElementById('prodTable')?.addEventListener('click', (ev) => {
    const btn = ev.target.closest('.js-edit-product');
    if (!btn) return;

    try {
      const p = JSON.parse(btn.dataset.product || '{}');
      pfTitle.textContent = 'Termék módosítása #' + (p.id || '');
      pId.value     = p.id || '';
      pBrand.value  = p.brand || '';
      pName.value   = p.name || '';
      pType.value   = p.type || 'other';
      pDesc.value   = p.description || '';
      pPrice.value  = p.price || '';
      pActive.checked = String(p.is_active) === '1';
      pFeat.checked   = String(p.is_featured) === '1';
      pPrev.src     = p.image || placeholderImg;
      pImage.value  = '';
      window.scrollTo({top: 0, behavior: 'smooth'});
    } catch (e) {
      alert('Nem sikerült betölteni a terméket szerkesztésre.');
    }
  });

  // Termék törlés megerősítés
  document.getElementById('prodTable')?.addEventListener('submit', (ev) => {
    if (ev.target.matches('.js-del-product')) {
      if (!confirm('Biztosan törlöd?')) ev.preventDefault();
    }
  });

  // Termék előnézet file-ból
  pImage?.addEventListener('change', (ev) => {
    const f = ev.target.files && ev.target.files[0];
    pPrev.src = f ? URL.createObjectURL(f) : placeholderImg;
  });

  // Termék űrlap reset
  btnResetProduct?.addEventListener('click', () => {
    pfTitle.textContent = 'Új termék';
    pId.value=''; pBrand.value=''; pName.value='';
    pType.value='other'; pDesc.value=''; pPrice.value='';
    pActive.checked = true; pFeat.checked = false;
    pImage.value=''; pPrev.src = placeholderImg;
  });

  // Szolgáltatás szerkesztés gombok
  document.getElementById('srvTable')?.addEventListener('click', (ev) => {
    const btn = ev.target.closest('.js-edit-service');
    if (!btn) return;

    try {
      const s = JSON.parse(btn.dataset.service || '{}');
      sfTitle.textContent = 'Szolgáltatás módosítása #' + (s.id || '');
      sId.value   = s.id || '';
      sName.value = s.name || '';
      sMin.value  = s.duration_minutes || '';
      sPrice.value= s.price || '';
      sDesc.value = s.description || '';
      sActive.checked = String(s.is_active) === '1';
      window.scrollTo({top: 0, behavior: 'smooth'});
    } catch (e) {
      alert('Nem sikerült betölteni a szolgáltatást szerkesztésre.');
    }
  });

  // Szolgáltatás törlés megerősítés
  document.getElementById('srvTable')?.addEventListener('submit', (ev) => {
    if (ev.target.matches('.js-del-service')) {
      if (!confirm('Biztosan törlöd?')) ev.preventDefault();
    }
  });

  // Szolgáltatás űrlap reset
  btnResetService?.addEventListener('click', () => {
    sfTitle.textContent = 'Új szolgáltatás';
    sId.value=''; sName.value=''; sMin.value=''; sPrice.value='';
    sDesc.value=''; sActive.checked=true;
  });

})();
