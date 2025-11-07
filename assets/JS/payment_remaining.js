// /RADS-TOOLING/assets/JS/payment_remaining.js
// Payment handler for remaining balance with flexible amount options

(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  let selectedMethod = null;
  let selectedPercent = 100; // Default to 100%
  let selectedAmount = window.RT_PAYMENT?.option_100 || 0;

  const ORDER_ID = window.RT_PAYMENT?.order_id || 0;
  const REMAINING_BALANCE = window.RT_PAYMENT?.remaining_balance || 0;

  console.log('💳 Payment Remaining: Loaded', window.RT_PAYMENT);
  console.log('💰 Initial selectedPercent:', selectedPercent);
  console.log('💰 Initial selectedAmount:', selectedAmount);

  // ===== Modal Management =====
  function openModal(id) {
    const modal = $(id);
    if (!modal) return;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeModal(id) {
    const modal = $(id);
    if (!modal) return;
    modal.hidden = true;
    document.body.style.overflow = '';
  }

  function closeAllSteps() {
    $$('.rt-step').forEach(step => step.hidden = true);
  }

  function showStep(stepId) {
    closeAllSteps();
    const step = $(stepId);
    if (step) {
      step.hidden = false;
    }
  }

  // ===== Modal Alert System =====
  function showModalAlert(title, message, type = 'error') {
    const existing = $('#customAlertModal');
    if (existing) existing.remove();

    const iconMap = {
      error: '❌',
      success: '✅',
      warning: '⚠️',
      info: 'ℹ️'
    };

    const colorMap = {
      error: '#e74c3c',
      success: '#27ae60',
      warning: '#f39c12',
      info: '#2f5b88'
    };

    const modal = document.createElement('div');
    modal.id = 'customAlertModal';
    modal.className = 'rt-modal';
    modal.innerHTML = `
      <div class="rt-modal__backdrop"></div>
      <div class="rt-card rt-step" style="max-width: 450px; display: block;">
        <div style="text-align: center; margin-bottom: 20px;">
          <span style="font-size: 48px;">${iconMap[type]}</span>
        </div>
        <h3 style="color: ${colorMap[type]}; margin-bottom: 16px; text-align: center;">
          ${title}
        </h3>
        <p style="color: #666; margin-bottom: 24px; line-height: 1.6; text-align: center;">
          ${message}
        </p>
        <div class="rt-actions" style="justify-content: center;">
          <button class="rt-btn main" style="min-width: 120px;" onclick="document.getElementById('customAlertModal').remove()">OK</button>
        </div>
      </div>
    `;
    modal.hidden = false;
    document.body.appendChild(modal);

    modal.querySelector('.rt-modal__backdrop')?.addEventListener('click', () => {
      modal.remove();
    });
  }

  // ===== Format Number Helper =====
  function formatMoney(amount) {
    const num = parseFloat(amount) || 0;
    return '₱' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  // Close modal handlers
  document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('[data-close]');
    if (closeBtn) {
      closeModal('#rtModal');
      closeAllSteps();
    }

    const backBtn = e.target.closest('[data-back]');
    if (backBtn) {
      const targetStep = backBtn.getAttribute('data-back');
      showStep(targetStep);
    }
  });

  // ===== Helper: Round to 2 decimals =====
  function round2(num) {
    return Math.round(num * 100) / 100;
  }

  // ===== Helper: Update UI with selected amount =====
  function updateAmountDisplay() {
    const amountDisplay = $('#payNowAmountText');
    const payBtn = $('#payNowBtn');

    if (amountDisplay) {
      amountDisplay.textContent = formatMoney(selectedAmount);
    }

    if (payBtn) {
      payBtn.innerHTML = `<span class="material-symbols-rounded">payments</span> Pay ${formatMoney(selectedAmount)}`;
    }

    // Update the hidden field for form submission
    const finalAmountField = $('#finalAmountPaid');
    if (finalAmountField) {
      finalAmountField.value = selectedAmount.toFixed(2);
    }

    console.log('💰 Updated display: percent=' + selectedPercent + '%, amount=' + formatMoney(selectedAmount));
  }

  // ===== PAYMENT AMOUNT SELECTION =====
  $$('[data-percent]').forEach(option => {
    option.addEventListener('click', () => {
      // Get the selected percent
      selectedPercent = parseInt(option.getAttribute('data-percent'), 10);

      // Calculate payment amount: round to 2 decimals
      selectedAmount = round2(REMAINING_BALANCE * (selectedPercent / 100));

      console.log('✅ Selected:', selectedPercent + '% = ' + formatMoney(selectedAmount));

      // Remove active from all options
      $$('[data-percent]').forEach(opt => opt.classList.remove('active'));

      // Mark this one active
      option.classList.add('active');

      // Update display
      updateAmountDisplay();
    });
  });

  // ===== Initialize: Set 100% as default =====
  window.addEventListener('DOMContentLoaded', () => {
    const default100 = $('[data-percent="100"]');
    if (default100) {
      default100.classList.add('active');
    }
    updateAmountDisplay();
  });

  // ===== Step 1: Start Payment =====
  $('#payNowBtn')?.addEventListener('click', () => {
    // Check if balance is 0 (fully paid)
    if (REMAINING_BALANCE <= 0) {
      showModalAlert('Already Paid', 'This order is already fully paid.', 'info');
      return;
    }

    if (!ORDER_ID) {
      showModalAlert('Error', 'Invalid order ID.', 'error');
      return;
    }

    if (!selectedAmount || selectedAmount <= 0) {
      showModalAlert('Error', 'Please select a payment amount.', 'error');
      return;
    }

    console.log('💳 Starting payment for:', selectedPercent + '% = ' + formatMoney(selectedAmount));

    openModal('#rtModal');
    showStep('#methodModal');
  });

  // ===== Step 2: Select Payment Method =====
  $$('.pay-chip[data-pay]').forEach(chip => {
    chip.addEventListener('click', () => {
      $$('.pay-chip[data-pay]').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      selectedMethod = chip.getAttribute('data-pay');
      $('#btnProceedToQR').disabled = false;
      console.log('✅ Selected method:', selectedMethod);
    });
  });

  $('#btnProceedToQR')?.addEventListener('click', async () => {
    if (!selectedMethod) {
      showModalAlert('Error', 'Please select a payment method.', 'error');
      return;
    }

    $('#paymentMethod').value = selectedMethod;

    // Update QR modal amount display
    const qrAmount = $('#qrAmount');
    if (qrAmount) {
      qrAmount.textContent = formatMoney(selectedAmount);
    }

    // Fetch QR code
    try {
      const response = await fetch('/RADS-TOOLING/backend/api/content_mgmt.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: 'action=get_payment_qr'
      });

      const result = await response.json();
      console.log('📥 QR code response:', result);

      if (!result || !result.success) {
        showModalAlert('QR Code Error', 'Could not load QR code. Please try again.', 'error');
        return;
      }

      const qrUrl = selectedMethod === 'gcash'
        ? result.data?.gcash_qr
        : result.data?.bpi_qr;

      if (!qrUrl) {
        showModalAlert('QR Code Not Found', 'QR code not available. Please contact support.', 'error');
        return;
      }

      const qrBox = $('#qrBox');
      qrBox.innerHTML = `<img src="${qrUrl}" alt="${selectedMethod.toUpperCase()} QR" style="max-width: 300px;">`;

      showStep('#qrModal');

    } catch (err) {
      console.error('❌ QR fetch error:', err);
      showModalAlert('Network Error', 'Could not load QR code.', 'error');
    }
  });

  // ===== Step 3: I've Paid =====
  $('#btnIpaid')?.addEventListener('click', () => {
    // Make sure the hidden field has the correct amount
    const finalAmountField = $('#finalAmountPaid');
    if (finalAmountField) {
      finalAmountField.value = selectedAmount.toFixed(2);
    }

    showStep('#verifyModal');
  });

  // ===== Step 4: Submit Verification =====
  $('#btnConfirmVerification')?.addEventListener('click', async () => {
    const form = $('#verifyForm');
    if (!form) return;

    const formData = new FormData(form);

    // Validate required fields
    const accountName = formData.get('account_name');
    const reference = formData.get('reference_number');
    const screenshot = formData.get('screenshot');

    if (!accountName || !reference || !screenshot || screenshot.size === 0) {
      showModalAlert('Missing Information', 'Please fill in all required fields and upload a screenshot.', 'warning');
      return;
    }

    // Ensure amount_paid is set to the selected amount
    formData.set('amount_paid', selectedAmount.toFixed(2));

    // Add payment type and percent for remaining balance payments
    formData.set('payment_type', 'remaining');
    formData.set('percent', selectedPercent.toString());
    formData.set('pay_amount', selectedAmount.toFixed(2));

    console.log('📤 Submitting payment:', {
      order_id: ORDER_ID,
      payment_type: 'remaining',
      percent: selectedPercent,
      pay_amount: selectedAmount,
      amount_paid: selectedAmount,
      method: selectedMethod,
      account_name: accountName,
      reference: reference
    });

    // Disable button
    const btn = $('#btnConfirmVerification');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-rounded">hourglass_empty</span> Submitting...';

    try {
      const response = await fetch('/RADS-TOOLING/backend/api/payment_submit.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const result = await response.json();
      console.log('📥 Payment submission response:', result);

      if (!result || !result.success) {
        showModalAlert('Submission Failed', result?.message || 'Payment verification failed.', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
      }

      showModalAlert('Payment Submitted!', `Your payment of ${formatMoney(selectedAmount)} is under verification. Check your orders page for approval status.`, 'success');

      setTimeout(() => {
        showStep('#finalNotice');
      }, 2000);

    } catch (err) {
      console.error('❌ Payment submit error:', err);
      showModalAlert('Network Error', 'Could not submit payment verification.', 'error');
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  });

  // ===== Step 5: Go to Orders =====
  $('#btnGoOrders')?.addEventListener('click', () => {
    location.href = '/RADS-TOOLING/customer/orders.php';
  });

  // ===== Numeric Input Validation =====
  const accountNum = $('#vpNum');
  if (accountNum) {
    accountNum.addEventListener('input', (e) => {
      e.target.value = e.target.value.replace(/\D/g, '');
    });
  }

  console.log('✅ Payment remaining balance script loaded');
  console.log('💰 Default selected amount:', formatMoney(selectedAmount));
})();
