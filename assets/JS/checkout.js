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

    brgySel.addEventListener('change', () => {
      if (bVal) bVal.value = brgySel.value;
    });
  }

  // ===== Continue Button =====
  function wireContinue() {
    const btn = $('#btnContinue');
    if (!btn) return;

    btn.addEventListener('click', () => {
      const form = $('#deliveryForm') || $('#pickupForm');
      if (!form) return;

      const invalids = [];
      Array.from(form.elements).forEach(el => {
        if (el.hasAttribute('required') && !el.disabled && !el.value) {
          el.style.borderColor = '#ef4444';
          invalids.push(el);
        } else if (el.style.borderColor === 'rgb(239, 68, 68)') {
          el.style.borderColor = '';
        }
      });

      if (invalids.length) {
        openModal('#invalidModal');
        return;
      }

      form.submit();
    });
  }

  // ===== Clear Button =====
  function wireClear() {
    const btn = $('#btnClear');
    if (!btn) return;
    btn.addEventListener('click', () => {
      const form = $('#deliveryForm') || $('#pickupForm');
      if (form) form.reset();
      $$('input,select').forEach(el => el.style.borderColor = '');
    });
  }

  // ===== Payment Workflow =====
  function wirePayment() {
    let ORDER_ID = null;
    let ORDER_CODE = null;
    let AMOUNT_DUE = 0;

    const methodInput = $('#paymentMethod');
    const depositRate = $('#depositRate');

    // Step 1: Open payment modal
    $('#inlineBuyBtn')?.addEventListener('click', () => {
      openModal('#rtModal');
      showStep('#methodModal');
    });

    // Step 2: Select Payment Method
    $$('.pay-chip[data-pay]').forEach(chip => {
      chip.addEventListener('click', () => {
        $$('.pay-chip[data-pay]').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        if (methodInput) methodInput.value = chip.dataset.pay;
        $('#btnChooseDeposit')?.removeAttribute('disabled');
      });
    });

    $('#btnChooseDeposit')?.addEventListener('click', () => {
      showStep('#depositModal');
    });

    // Select Deposit Rate
    $$('.pay-chip[data-dep]').forEach(chip => {
      chip.addEventListener('click', () => {
        $$('.pay-chip[data-dep]').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        if (depositRate) depositRate.value = chip.dataset.dep;
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
          pid: Number(window.RT_ORDER?.pid || 0),
          qty: Number(window.RT_ORDER?.qty || 1),
          subtotal: Number(window.RT_ORDER?.subtotal || 0),
          vat: Number(window.RT_ORDER?.vat || 0),
          total: Number(window.RT_ORDER?.total || 0),
          mode: String(window.RT_ORDER?.mode || 'pickup'),
          info: window.RT_ORDER?.info || {}
        };

        if (!payload.pid || !payload.total) {
          alert('Missing pid/total in RT_ORDER.');
          console.warn('RT_ORDER invalid:', payload);
          return;
        }

        try {
          const url = `${location.origin}/RADS-TOOLING/backend/api/order_create.php`;

          const r1 = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
          });

          const raw1 = await r1.text();
          console.debug('[order_create] status:', r1.status, 'ok:', r1.ok, 'raw:', raw1);

          if (!r1.ok) {
            alert(`HTTP ${r1.status} from order_create.php:\n` + raw1.slice(0, 400));
            return;
          }

          let result;
          try { result = JSON.parse(raw1); }
          catch {
            alert('Non-JSON from order_create.php:\n' + raw1.slice(0, 400));
            return;
          }

          if (!result?.success) {
            alert(result?.message || 'Could not create order.');
            return;
          }

          const id = (result?.data?.order_id ?? result?.order_id) || null;
          const code = (result?.data?.order_code ?? result?.order_code) || null;

          if (!id) {
            alert('Order create returned no order_id.');
            console.warn('order_create result:', result);
            return;
          }
          ORDER_ID = id;
          ORDER_CODE = code;

        } catch (err) {
          console.error('[order_create] fetch error:', err);
          alert('Network error creating order (fetch failed).');
          return;
        }
      }

      // 2) Save payment decision (method + deposit rate)
      try {
        const url2 = `${location.origin}/RADS-TOOLING/backend/api/payment_decision.php`;
        const r2 = await fetch(url2, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          body: JSON.stringify({ order_id: ORDER_ID, method: method, deposit_rate: dep })
        });
        const raw2 = await r2.text();
        console.debug('[payment_decision]', r2.status, raw2);
        if (!r2.ok) { alert(`HTTP ${r2.status}: ${raw2.slice(0, 300)}`); return; }
        const result2 = JSON.parse(raw2);

        if (!result2 || !result2.success) {
          alert(result2?.message || 'Could not set payment terms.');
          return;
        }
        AMOUNT_DUE = result2.data.amount_due || 0;
      } catch (err) {
        alert('Network error setting payment terms.');
        return;
      }

      // 3) Fetch QR code from content_mgmt API
      try {
        const url3 = `${location.origin}/RADS-TOOLING/backend/api/content_mgmt.php`;
        const r3 = await fetch(url3, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          body: 'action=get_payment_qr'
        });
        const raw3 = await r3.text();
        console.debug('[get_payment_qr]', r3.status, raw3);
        let result3; 
        try { result3 = JSON.parse(raw3); } catch { result3 = null; }

        const qrBox = $('#qrBox');
        if (qrBox) {
          if (result3 && result3.success && result3.data) {
            const qrData = method === 'gcash' ? result3.data.gcash : result3.data.bpi;
            
            if (qrData && qrData.image_path) {
              const imageUrl = `/RADS-TOOLING/${qrData.image_path}`;
              console.log(`✅ Displaying ${method.toUpperCase()} QR:`, imageUrl);
              
              // 🔥 NEW: Create QR with zoom capability
              qrBox.innerHTML = `
                <img 
                  src="${imageUrl}?v=${Date.now()}" 
                  alt="${method.toUpperCase()} QR" 
                  style="width:100%;height:100%;object-fit:contain;cursor:pointer;padding:8px;" 
                  onclick="window.openQrZoom('${imageUrl}')"
                  onerror="this.parentElement.innerHTML='<span style=\\'color:#e74c3c;\\'>❌ Failed to load QR image</span>'"
                >`;
            } else {
              console.warn(`⚠️ No ${method.toUpperCase()} QR found in database`);
              qrBox.innerHTML = '<span style="color:#999">No QR configured.</span>';
            }
          } else {
            console.error('❌ Invalid API response:', result3);
            qrBox.innerHTML = '<span style="color:#999">No QR configured.</span>';
          }
        }
      } catch (err) {
        console.error('💥 QR fetch error:', err);
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

  // 🔥 NEW: QR Zoom Functions
  window.openQrZoom = function(qrUrl) {
    const modal = $('#qrZoomModal');
    const img = $('#zoomQrImage');
    
    if (!modal || !img) {
      console.warn('⚠️ QR Zoom modal elements not found');
      return;
    }
    
    img.src = qrUrl;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Close on backdrop click
    const handleBackdropClick = (e) => {
      if (e.target === modal) {
        window.closeQrZoom();
      }
    };
    modal.addEventListener('click', handleBackdropClick);
    
    console.log('🔍 QR Zoom opened:', qrUrl);
  };

  window.closeQrZoom = function() {
    const modal = $('#qrZoomModal');
    if (!modal) return;
    
    modal.classList.remove('show');
    document.body.style.overflow = '';
    console.log('✅ QR Zoom closed');
  };

  // ESC key to close zoom
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const zoomModal = $('#qrZoomModal');
      if (zoomModal && zoomModal.classList.contains('show')) {
        window.closeQrZoom();
      }
    }
  });

  // ===== Initialize Everything =====
  document.addEventListener('DOMContentLoaded', () => {
    wirePhone();
    loadPSGC();
    wireContinue();
    wireClear();
    wirePayment();
    
    console.log('✅ Checkout.js loaded with QR Zoom feature');
  });
})();