// /RADS-TOOLING/assets/JS/checkout.js

(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  // ===== Helpers =====
  function openModal(id) { const el = $(id); if (el) el.hidden = false; }
  function closeModal(id) { const el = $(id); if (el) el.hidden = true; }
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-close]');
    if (btn) closeModal(btn.getAttribute('data-close'));
  });

  // ===== Shared: phone + hidden field =====
  function wirePhone() {
    const local = $('#phoneLocal');
    const full = $('#phoneFull');
    if (!local || !full) return;
    const sync = () => {
      const digits = (local.value || '').replace(/\D+/g, '').slice(0, 10);
      local.value = digits;
      full.value = digits.length ? ('+63' + digits) : '';
    };
    local.addEventListener('input', sync);
    sync();
  }

  // ===== PSGC =====
  // Optional helper (kept for compatibility)
async function safeFetchJSON(url, fallback = []) {
  try {
    const res = await fetch(url, { cache: 'no-store' });
    if (!res.ok) return fallback;
    return await res.json();
  } catch {
    return fallback;
  }
}

/* ============================
   PSGC loader (stable version)
   ============================ */
async function loadPSGC() {
  const provSel   = document.querySelector('#province');
  const citySel   = document.querySelector('#city');
  const brgySel   = document.querySelector('#barangaySelect');

  const provInput = document.querySelector('#provinceInput');
  const cityInput = document.querySelector('#cityInput');
  const brgyInput = document.querySelector('#barangayInput');

  const pVal = document.querySelector('#provinceVal');
  const cVal = document.querySelector('#cityVal');
  const bVal = document.querySelector('#barangayVal');

  if (!provSel || !citySel || !brgySel) return;

  // Keep selects visible; show text inputs only when needed
  function showText(field, on) {
    if (field === 'province') {
      provSel.disabled = on;
      if (provInput) { provInput.hidden = !on; provInput.disabled = !on; provInput.required = !!on; }
    }
    if (field === 'city') {
      citySel.disabled = on;
      if (cityInput) { cityInput.hidden = !on; cityInput.disabled = !on; cityInput.required = !!on; }
    }
    if (field === 'barangay') {
      brgySel.disabled = on;
      if (brgyInput) { brgyInput.hidden = !on; brgyInput.disabled = !on; brgyInput.required = !!on; }
    }
  }

  // Mirror text inputs into hidden fields
  provInput?.addEventListener('input', () => { if (pVal) pVal.value = (provInput.value || '').trim(); });
  cityInput?.addEventListener('input', () => { if (cVal) cVal.value = (cityInput.value || '').trim(); });
  brgyInput?.addEventListener('input', () => { if (bVal) bVal.value = (brgyInput.value || '').trim(); });

  // Small fetch helper
  async function getJSON(url) {
    try {
      const r = await fetch(url, { cache: 'no-store' });
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return await r.json();
    } catch {
      return null;
    }
  }

  // Provinces
  async function fetchProvinces() {
    // Try your PHP proxy first
    let j = await getJSON('/RADS-TOOLING/backend/api/psgc.php?action=provinces');
    if (Array.isArray(j) && j.length) return j.map(x => x.name || x);

    // Fallback to PSGC Cloud
    j = await getJSON('https://psgc.cloud/api/provinces');
    if (Array.isArray(j) && j.length) return j.map(x => x.name).filter(Boolean);

    return [];
  }

  // Cities/Municipalities
  async function fetchCities(provinceName) {
    // 1) local endpoint (already filtered)
    let j = await getJSON('/RADS-TOOLING/backend/api/psgc.php?action=cities&province=' + encodeURIComponent(provinceName));
    if (Array.isArray(j) && j.length) return j.map(x => x.name || x);

    // 2) PSGC Cloud → find province code then call nested endpoint
    const provList = await getJSON('https://psgc.cloud/api/provinces');
    if (Array.isArray(provList) && provList.length) {
      const found = provList.find(p => (p.name || '').toLowerCase() === (provinceName || '').toLowerCase());
      if (found && found.code) {
        const data = await getJSON(`https://psgc.cloud/api/provinces/${found.code}/cities-municipalities`);
        if (Array.isArray(data) && data.length) return data.map(x => x.name).filter(Boolean);
      }
    }

    return [];
  }

  // Resolve city/municipality PSGC code (helps get accurate barangays)
  async function resolveCityOrMunicipalityCode(cityName, provinceName) {
    const norm = s => (s || '').toLowerCase().trim();

    // Try /cities
    let j = await getJSON('https://psgc.cloud/api/cities');
    if (Array.isArray(j) && j.length) {
      let hit = j.find(x => norm(x.name) === norm(cityName) &&
                            (!provinceName || norm(x.province_name) === norm(provinceName)));
      if (!hit) hit = j.find(x => norm(x.name) === norm(cityName));
      if (hit && hit.code) return { type: 'city', code: hit.code };
    }

    // Try /municipalities
    j = await getJSON('https://psgc.cloud/api/municipalities');
    if (Array.isArray(j) && j.length) {
      let hit = j.find(x => norm(x.name) === norm(cityName) &&
                            (!provinceName || norm(x.province_name) === norm(provinceName)));
      if (!hit) hit = j.find(x => norm(x.name) === norm(cityName));
      if (hit && hit.code) return { type: 'municipality', code: hit.code };
    }

    // Last resort: nested under province
    const provs = await getJSON('https://psgc.cloud/api/provinces');
    if (Array.isArray(provs) && provs.length) {
      const p = provs.find(x => norm(x.name) === norm(provinceName));
      if (p && p.code) {
        const cm = await getJSON(`https://psgc.cloud/api/provinces/${p.code}/cities-municipalities`);
        if (Array.isArray(cm) && cm.length) {
          const hit = cm.find(x => norm(x.name) === norm(cityName));
          if (hit && hit.code) return { type: 'cm', code: hit.code };
        }
      }
    }

    return null;
  }

  // Barangays for a given city/municipality name
  async function fetchBarangays(cityName, provinceName) {
    // 0) Try your local PHP proxy first
    let j = await getJSON('/RADS-TOOLING/backend/api/psgc.php?action=barangays&city=' + encodeURIComponent(cityName));
    if (Array.isArray(j) && j.length) return j.map(x => x.name || x);

    // 1) Resolve code via PSGC Cloud and hit nested endpoint
    const resolved = await resolveCityOrMunicipalityCode(cityName, provinceName);
    if (resolved && resolved.code) {
      const urls = [];
      if (resolved.type === 'city') {
        urls.push(`https://psgc.cloud/api/cities/${resolved.code}/barangays`);
      } else if (resolved.type === 'municipality') {
        urls.push(`https://psgc.cloud/api/municipalities/${resolved.code}/barangays`);
      }
      // generic fallback
      urls.push(`https://psgc.cloud/api/cities-municipalities/${resolved.code}/barangays`);

      for (const u of urls) {
        j = await getJSON(u);
        if (Array.isArray(j) && j.length) return j.map(x => x.name).filter(Boolean);
      }
    }

    // 2) Final fallback: none (we'll show manual text input)
    return [];
  }

  /* ---------- Bootstrapping UI ---------- */
  const provinces = await fetchProvinces();
  if (!provinces.length) {
    // Full fallback: manual type for all 3 levels
    showText('province', true);
    showText('city', true);
    showText('barangay', true);
    return;
  }

  provSel.innerHTML =
    '<option value="">Select province</option>' +
    provinces.sort((a, b) => a.localeCompare(b)).map(n => `<option value="${n}">${n}</option>`).join('');
  provSel.disabled = false;

  // Province change -> load cities
  provSel.addEventListener('change', async () => {
    const pv = provSel.value;
    if (pVal) pVal.value = pv;

    // Reset city/brgy UI
    citySel.innerHTML = '<option value="">Select city/municipality</option>';
    brgySel.innerHTML = '<option value="">Select barangay</option>';
    citySel.disabled = !pv;
    brgySel.disabled = true;

    // Hide manual inputs first
    if (cityInput) { cityInput.hidden = true; cityInput.disabled = true; cityInput.required = false; }
    if (brgyInput) { brgyInput.hidden = true; brgyInput.disabled = true; brgyInput.required = false; }

    if (!pv) return;

    const cities = await fetchCities(pv);

    if (!cities.length) {
      // No list available → allow manual city + barangay
      citySel.disabled = true;
      if (cityInput) { cityInput.hidden = false; cityInput.disabled = false; cityInput.required = true; }

      brgySel.disabled = true;
      if (brgyInput) { brgyInput.hidden = false; brgyInput.disabled = false; brgyInput.required = true; }
      return;
    }

    // We have cities → use select
    citySel.innerHTML =
      '<option value="">Select city/municipality</option>' +
      cities.sort((a, b) => a.localeCompare(b)).map(n => `<option value="${n}">${n}</option>`).join('');
    citySel.disabled = false;
  });

  // City change -> load barangays
  citySel.addEventListener('change', async () => {
    const cv = citySel.value;
    if (cVal) cVal.value = cv;

    brgySel.innerHTML = '<option value="">Select barangay</option>';
    brgySel.disabled = !cv;

    if (brgyInput) { brgyInput.hidden = true; brgyInput.disabled = true; brgyInput.required = false; }
    if (!cv) return;

    // pass province for accurate matching
    const pv = provSel ? provSel.value : '';
    const brgys = await fetchBarangays(cv, pv);

    if (!brgys.length) {
      brgySel.disabled = true;
      if (brgyInput) { brgyInput.hidden = false; brgyInput.disabled = false; brgyInput.required = true; }
      return;
    }

    brgySel.innerHTML =
      '<option value="">Select barangay</option>' +
      brgys.sort((a, b) => a.localeCompare(b)).map(n => `<option value="${n}">${n}</option>`).join('');
    brgySel.disabled = false;
  });

  // Mirror barangay select to hidden
  brgySel?.addEventListener('change', () => {
    if (bVal) bVal.value = brgySel.value || '';
  });
}





  // ===== Validate-and-continue (Delivery / Pick-up) =====
  function wireContinue() {
    const continueBtn = $('#btnContinue');
    if (!continueBtn) return;

    continueBtn.addEventListener('click', () => {
      const form = continueBtn.closest('form');
      if (!form) return;

      let ok = true;
      $$('input[required], select[required]', form).forEach(el => {
        const valid = !!el.value;
        el.style.borderColor = valid ? '' : '#ef4444';
        if (!valid) ok = false;
      });

      if (!ok) {
        openModal('#invalidModal');
        return;
      }
      form.submit();
    });
  }

  function wireClear() {
    const clear = $('#btnClear');
    if (!clear) return;
    clear.addEventListener('click', () => {
      const form = clear.closest('form');
      form && form.reset();
      // reset red borders
      $$('input,select', form).forEach(el => el.style.borderColor = '');
    });
  }

  // ===== Review page: payment chips, deposit, QR, verify, place order =====
  function wirePayment() {
    const methodInput = $('#paymentMethod');
    const depositRate = $('#depositRate');
    const paidFlag = $('#paidFlag');

    // payment method chips
    $$('.pay-chip[data-pay]').forEach(chip => {
      chip.addEventListener('click', () => {
        $$('.pay-chip[data-pay]').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        if (methodInput) methodInput.value = chip.dataset.pay || '';
        // enable "Choose deposit"
        $('#btnChooseDeposit')?.removeAttribute('disabled');
      });
    });

    // deposit selection
    const depBtn = $('#btnChooseDeposit');
    depBtn?.addEventListener('click', () => openModal('#depositModal'));

    $$('#depositModal .pay-chip[data-dep]').forEach(chip => {
      chip.addEventListener('click', () => {
        $$('#depositModal .pay-chip[data-dep]').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        depositRate && (depositRate.value = chip.dataset.dep || '');
        // allow Pay now when method + deposit picked
        if (methodInput?.value && depositRate?.value) {
          $('#btnPayNow')?.removeAttribute('disabled');
        }
      });
    });

    // Pay now -> show QR
    $('#btnPayNow')?.addEventListener('click', () => {
      // put QR based on method
      const pay = (methodInput?.value || '').toLowerCase();
      const qrBox = $('#qrBox');
      if (qrBox) {
        qrBox.innerHTML = pay === 'gcash'
          ? '<img src="/RADS-TOOLING/public/uploads/qrs/gcash.png" alt="GCash QR" style="max-width:100%;height:200px;object-fit:contain">'
          : '<img src="/RADS-TOOLING/public/uploads/qrs/bpi.png" alt="BPI QR" style="max-width:100%;height:200px;object-fit:contain">';
      }
      openModal('#qrModal');
    });

    // I’ve paid -> open verification modal
    $('#btnIpaid')?.addEventListener('click', () => {
      closeModal('#qrModal');
      openModal('#verifyModal');
    });

    // Verify modal - minimal validation
    $('#btnVerify')?.addEventListener('click', () => {
      const name = $('#vpName'), num = $('#vpNum'), ref = $('#vpRef'), amt = $('#vpAmt'), shot = $('#vpShot');
      const reqs = [name, num, ref, amt, shot];
      let ok = true;
      reqs.forEach(el => {
        const good = !!(el && el.value);
        el.style.borderColor = good ? '' : '#ef4444';
        if (!good) ok = false;
      });
      if (!ok) return;

      // Mark as paid -> enable Place Order
      paidFlag && (paidFlag.value = '1');
      $('#buyBtn')?.removeAttribute('disabled');
      closeModal('#verifyModal');
    });

    // Place Order (frontend stub)
    $('#buyBtn')?.addEventListener('click', async () => {
      // TODO: POST to /backend/api/order_create.php with all payload (pid, qty, totals, address, payment info)
      // For now show final notice only:
      openModal('#finalNotice');
    });
  }

  // ===== Init per page =====
  document.addEventListener('DOMContentLoaded', () => {
    wirePhone();
    loadPSGC();
    wireContinue();
    wireClear();
    wirePayment();
  });
})();
