// /RADS-TOOLING/assets/JS/checkout.js

(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  // ===== Modal Management =====
  function openModal(id) {
    const el = $(id);
    if (!el) return;
    el.hidden = false;
  }

  function closeModal(id) {
    const el = $(id);
    if (!el) return;
    el.hidden = true;
  }

  function closeAllSteps() {
    $$('.rt-step').forEach(step => step.hidden = true);
  }

  function showStep(stepId) {
    closeAllSteps();
    const step = $(stepId);
    if (step) step.hidden = false;
  }

  // Close modal on backdrop click or close button
  document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('[data-close]');
    if (closeBtn) {
      const targetModal = closeBtn.getAttribute('data-close');
      if (targetModal) {
        closeModal(targetModal);
      } else {
        // Close entire modal system
        closeModal('#rtModal');
        closeAllSteps();
      }
    }

    // Back button navigation
    const backBtn = e.target.closest('[data-back]');
    if (backBtn) {
      const targetStep = backBtn.getAttribute('data-back');
      showStep(targetStep);
    }
  });

  // ===== Phone Number Handler =====
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

  // ===== PSGC Address Loader =====
  async function loadPSGC() {
    const provSel = $('#province');
    const citySel = $('#city');
    const brgySel = $('#barangaySelect');
    const provInput = $('#provinceInput');
    const cityInput = $('#cityInput');
    const brgyInput = $('#barangayInput');
    const pVal = $('#provinceVal');
    const cVal = $('#cityVal');
    const bVal = $('#barangayVal');

    if (!provSel || !citySel || !brgySel) return;

    function showText(field, on) {
      if (field === 'province' && provInput) {
        provSel.disabled = on;
        provInput.hidden = !on;
        provInput.disabled = !on;
        provInput.required = !!on;
      }
      if (field === 'city' && cityInput) {
        citySel.disabled = on;
        cityInput.hidden = !on;
        cityInput.disabled = !on;
        cityInput.required = !!on;
      }
      if (field === 'barangay' && brgyInput) {
        brgySel.disabled = on;
        brgyInput.hidden = !on;
        brgyInput.disabled = !on;
        brgyInput.required = !!on;
      }
    }

    // Mirror text inputs
    provInput?.addEventListener('input', () => { if (pVal) pVal.value = (provInput.value || '').trim(); });
    cityInput?.addEventListener('input', () => { if (cVal) cVal.value = (cityInput.value || '').trim(); });
    brgyInput?.addEventListener('input', () => { if (bVal) bVal.value = (brgyInput.value || '').trim(); });

    async function getJSON(url) {
      try {
        const r = await fetch(url, { cache: 'no-store' });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return await r.json();
      } catch {
        return null;
      }
    }

    // Fetch provinces
    async function fetchProvinces() {
      let j = await getJSON('/RADS-TOOLING/backend/api/psgc.php?action=provinces');
      if (Array.isArray(j) && j.length) return j.map(x => x.name || x);

      j = await getJSON('https://psgc.cloud/api/provinces');
      if (Array.isArray(j) && j.length) return j.map(x => x.name).filter(Boolean);

      return [];
    }

    // Fetch cities
    async function fetchCities(provinceName) {
      let j = await getJSON('/RADS-TOOLING/backend/api/psgc.php?action=cities&province=' + encodeURIComponent(provinceName));
      if (Array.isArray(j) && j.length) return j.map(x => x.name || x);

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

    // Fetch barangays
    async function fetchBarangays(cityName, provinceName) {
      let j = await getJSON('/RADS-TOOLING/backend/api/psgc.php?action=barangays&city=' + encodeURIComponent(cityName));
      if (Array.isArray(j) && j.length) return j.map(x => x.name || x);

      // Resolve city code for accurate lookup
      const norm = s => (s || '').toLowerCase().trim();
      
      let cityData = await getJSON('https://psgc.cloud/api/cities');
      if (Array.isArray(cityData)) {
        let hit = cityData.find(x => norm(x.name) === norm(cityName));
        if (hit && hit.code) {
          j = await getJSON(`https://psgc.cloud/api/cities/${hit.code}/barangays`);
          if (Array.isArray(j) && j.length) return j.map(x => x.name).filter(Boolean);
        }
      }

      let munData = await getJSON('https://psgc.cloud/api/municipalities');
      if (Array.isArray(munData)) {
        let hit = munData.find(x => norm(x.name) === norm(cityName));
        if (hit && hit.code) {
          j = await getJSON(`https://psgc.cloud/api/municipalities/${hit.code}/barangays`);
          if (Array.isArray(j) && j.length) return j.map(x => x.name).filter(Boolean);
        }
      }

      return [];
    }

    // Bootstrap provinces
    const provinces = await fetchProvinces();
    if (!provinces.length) {
      showText('province', true);
      showText('city', true);
      showText('barangay', true);
      return;
    }

    provSel.innerHTML =
      '<option value="">Select province</option>' +
      provinces.sort((a, b) => a.localeCompare(b)).map(n => `<option value="${n}">${n}</option>`).join('');
    provSel.disabled = false;

    // Province change
    provSel.addEventListener('change', async () => {
      const pv = provSel.value;
      if (pVal) pVal.value = pv;

      citySel.innerHTML = '<option value="">Select city/municipality</option>';
      brgySel.innerHTML = '<option value="">Select barangay</option>';
      citySel.disabled = !pv;
      brgySel.disabled = true;

      if (cityInput) { cityInput.hidden = true; cityInput.disabled = true; cityInput.required = false; }
      if (brgyInput) { brgyInput.hidden = true; brgyInput.disabled = true; brgyInput.required = false; }

      if (!pv) return;

      const cities = await fetchCities(pv);

      if (!cities.length) {
        citySel.disabled = true;
        if (cityInput) { cityInput.hidden = false; cityInput.disabled = false; cityInput.required = true; }

        brgySel.disabled = true;
        if (brgyInput) { brgyInput.hidden = false; brgyInput.disabled = false; brgyInput.required = true; }
        return;
      }

      citySel.innerHTML =
        '<option value="">Select city/municipality</option>' +
        cities.sort((a, b) => a.localeCompare(b)).map(n => `<option value="${n}">${n}</option>`).join('');
      citySel.disabled = false;
    });

    // City change
    citySel.addEventListener('change', async () => {
      const cv = citySel.value;
      if (cVal) cVal.value = cv;

      brgySel.innerHTML = '<option value="">Select barangay</option>';
      brgySel.disabled = !cv;

      if (brgyInput) { brgyInput.hidden = true; brgyInput.disabled = true; brgyInput.required = false; }
      if (!cv) return;

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

    // Mirror barangay select
    brgySel?.addEventListener('change', () => {
      if (bVal) bVal.value = brgySel.value || '';
    });
  }

  // ===== Form Validation & Continue =====
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
      $$('input,select', form).forEach(el => el.style.borderColor = '');
    });
  }

  // ===== PAYMENT WIZARD =====
  function wirePayment() {
    const methodInput = $('#paymentMethod');
    const depositRate = $('#depositRate');

    let ORDER_ID = null;
    let ORDER_CODE = null;
    let AMOUNT_DUE = 0;

    // Open payment modal
    const inlineBtn = $('#inlineBuyBtn');
    inlineBtn?.addEventListener('click', () => {
      openModal('#rtModal');
      showStep('#methodModal');
    });

    // Step 1: Payment Method Selection
    $$('.pay-chip[data-pay]').forEach(chip => {
      chip.addEventListener('click', () => {
        $$('.pay-chip[data-pay]').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        if (methodInput) methodInput.value = chip.dataset.pay || '';
        $('#btnChooseDeposit')?.removeAttribute('disabled');
      });
    });

    // Go to deposit selection
    $('#btnChooseDeposit')?.addEventListener('click', () => {
      showStep('#depositModal');
    });

    // Step 2: Deposit Amount Selection
    $$('#depositModal .pay-chip[data-dep]').forEach(chip => {
      chip.addEventListener('click', () => {
        $$('#depositModal .pay-chip[data-dep]').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        depositRate && (depositRate.value = chip.dataset.dep || '');
        if (methodInput?.value && depositRate?.value) {
          $('#btnPayNow')?.removeAttribute('disabled');
        }
      });
    });

    // Step 3: Create Order & Show QR
    $('#btnPayNow')?.addEventListener('click', async () => {
      const method = (methodInput?.value || '').toLowerCase();
      const dep = parseInt(depositRate?.value || '0', 10);
      if (!method || !dep) return;

      // 1) Create order if not exists
      if (!ORDER_ID) {
        const payload = {
          pid: window.RT_ORDER?.pid || '',
          qty: window.RT_ORDER?.qty || 1,
          subtotal: window.RT_ORDER?.subtotal || 0,
          vat: window.RT_ORDER?.vat || 0,
          total: window.RT_ORDER?.total || 0,
          mode: window.RT_ORDER?.mode || 'pickup',
          info: window.RT_ORDER?.info || {}
        };

        try {
          const r1 = await fetch('/RADS-TOOLING/backend/api/order_create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
          });
          const result = await r1.json();

          if (!result || !result.success) {
            alert(result?.message || 'Could not create order.');
            return;
          }
          ORDER_ID = result.data.order_id;
          ORDER_CODE = result.data.order_code;
        } catch (err) {
          alert('Network error creating order.');
          return;
        }
      }

      // 2) Save payment decision (method + deposit rate)
      try {
        const r2 = await fetch('/RADS-TOOLING/backend/api/payment_decision.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({
            order_id: ORDER_ID,
            method: method,
            deposit_rate: dep
          })
        });
        const result2 = await r2.json();

        if (!result2 || !result2.success) {
          alert(result2?.message || 'Could not set payment terms.');
          return;
        }
        AMOUNT_DUE = result2.data.amount_due || 0;
      } catch (err) {
        alert('Network error setting payment terms.');
        return;
      }

      // 3) Fetch QR code
      try {
        const r3 = await fetch('/RADS-TOOLING/backend/api/payment_qr.php?method=' + encodeURIComponent(method), {
          credentials: 'same-origin'
        });
        const result3 = await r3.json();

        const qrBox = $('#qrBox');
        if (qrBox) {
          if (result3 && result3.success && result3.data && result3.data.qr_url) {
            qrBox.innerHTML = `<img src="${result3.data.qr_url}" alt="${method.toUpperCase()} QR" style="max-width:100%;height:200px;object-fit:contain">`;
          } else {
            qrBox.innerHTML = '<span style="color:#999">No QR configured.</span>';
          }
        }
      } catch (err) {
        const qrBox = $('#qrBox');
        if (qrBox) qrBox.innerHTML = '<span style="color:#999">Failed to load QR</span>';
      }

      // Update amount due label
      const amtLabel = $('#amountDueLabel');
      if (amtLabel) amtLabel.textContent = '₱' + AMOUNT_DUE.toLocaleString('en-PH', { minimumFractionDigits: 2 });

      // Show QR modal
      showStep('#qrModal');
    });

    // Step 4: I've Paid -> Verify Payment
    $('#btnIpaid')?.addEventListener('click', () => {
      showStep('#verifyModal');
    });

    // Step 5: Submit Verification
    $('#btnVerify')?.addEventListener('click', async () => {
      const name = $('#vpName');
      const num = $('#vpNum');
      const ref = $('#vpRef');
      const amt = $('#vpAmt');
      const shot = $('#vpShot');

      const reqs = [name, num, ref, amt, shot];
      let ok = true;
      reqs.forEach(el => {
        const good = !!(el && el.value);
        el.style.borderColor = good ? '' : '#ef4444';
        if (!good) ok = false;
      });

      if (!ok || !ORDER_ID) return;

      const form = new FormData();
      form.append('order_id', ORDER_ID);
      form.append('order_code', ORDER_CODE || '');
      form.append('amount_due', AMOUNT_DUE || 0);
      form.append('account_name', name.value);
      form.append('account_number', num.value);
      form.append('reference_number', ref.value);
      form.append('amount_paid', amt.value);
      form.append('screenshot', shot.files[0] || null);

      try {
        const r = await fetch('/RADS-TOOLING/backend/api/payment_submit.php', {
          method: 'POST',
          body: form,
          credentials: 'same-origin'
        });
        const result = await r.json();

        if (!result || !result.success) {
          alert(result?.message || 'Payment verification failed.');
          return;
        }

        // Success
        showStep('#finalNotice');
      } catch (err) {
        alert('Network error submitting payment.');
      }
    });

    // Go to Orders page
    $('#btnGoOrders')?.addEventListener('click', () => {
      location.href = '/RADS-TOOLING/customer/orders.php';
    });
  }

  // ===== Initialize Everything =====
  document.addEventListener('DOMContentLoaded', () => {
    wirePhone();
    loadPSGC();
    wireContinue();
    wireClear();
    wirePayment();
  });
})();