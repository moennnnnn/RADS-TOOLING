<?php
declare(strict_types=1);
session_start();

// Product params (fallback lang; palitan mo kung may DB fetch ka)
$pid   = (int)($_GET['pid'] ?? 0);
$name  = $_GET['name'] ?? 'Kitchen Cabinet';
$price = (float)($_GET['price'] ?? 0);
$qty   = (int)($_GET['qty'] ?? 1);

$sub   = $price * $qty;
$vat   = round($sub * 0.12, 2);   // 12% VAT
$ship  = 500.00;                  // Delivery shipping (fixed demo)
$grand = $sub + $vat + $ship;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Checkout — Delivery</title>
  <link rel="stylesheet" href="/assets/css/checkout.css"><!-- existing styles mo -->
  <style>
    /* === Add-on CSS (scoped) === */
    .co-shell{max-width:1200px;margin:0 auto;padding:12px 20px;font-family:Inter,system-ui,Arial;color:#0f172a}
    .co-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px}
    .co-h{margin:0 0 8px;font-size:14px;font-weight:800}
    .co-muted{font-size:12px;color:#6b7280}

    /* Top form spanning full width */
    .co-form{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .co-field{display:flex;flex-direction:column;gap:6px}
    .co-field label{font-size:12px;color:#374151}
    .co-field input{height:36px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;outline:none}
    .co-field input:focus{border-color:#377dff}

    /* Bottom row: Summary (left) + Payment (right) */
    .co-row{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:16px}
    @media (max-width:1024px){.co-form{grid-template-columns:1fr} .co-row{grid-template-columns:1fr}}

    .co-sum .line{display:flex;justify-content:space-between;margin:6px 0;font-size:13px}
    .co-sum .total{font-weight:800;border-top:1px dashed #e5e7eb;padding-top:8px}

    .co-payhead{display:flex;align-items:center;gap:10px;margin-bottom:8px}
    .co-badge{font-size:12px;background:#f3f4f6;padding:2px 8px;border-radius:999px}
    .co-badge.ok{background:#e7f8ec;color:#05603a}
    .co-qr{display:grid;grid-template-columns:1fr;gap:8px} /* stacked gaya ng ref */
    .co-btn{height:36px;border:1px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer}
    .co-btn:hover{border-color:#377dff}
    .co-buy{height:38px;border:none;border-radius:10px;background:#111;color:#fff;font-weight:800;cursor:pointer;width:160px}
    .co-buy[disabled]{opacity:.5;cursor:not-allowed}

    /* Modal */
    .co-back{position:fixed;inset:0;background:rgba(0,0,0,.4);display:none;align-items:center;justify-content:center;z-index:9999}
    .co-modal{width:520px;max-width:calc(100% - 24px);background:#fff;border-radius:12px;border:1px solid #e5e7eb}
    .co-mhd{padding:10px 14px;border-bottom:1px solid #e5e7eb;font-weight:800}
    .co-mbd{padding:14px}
    .co-mft{padding:12px 14px;border-top:1px solid #e5e7eb;display:flex;gap:10px;justify-content:flex-end}
    .co-mfield{display:flex;flex-direction:column;gap:6px;margin-bottom:10px}
    .co-mfield input{height:36px;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px}
    .co-pbtn{height:36px;padding:0 12px;border-radius:8px;border:1px solid #d1d5db;background:#fff;cursor:pointer}
    .co-pbtn.primary{background:#111;color:#fff;border-color:#111}
  </style>
</head>
<body>
  <div class="co-shell">
    <!-- ===== Top: Form (full width) ===== -->
    <div class="co-card">
      <div class="co-h">DELIVERY — Enter your Name and Address</div>

      <form id="deliverForm" method="post" action="/backend/api/order_create.php">
        <input type="hidden" name="type" value="delivery">
        <input type="hidden" name="pid" value="<?= $pid ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
        <input type="hidden" name="price" value="<?= number_format($price,2,'.','') ?>">
        <input type="hidden" name="qty" value="<?= $qty ?>">
        <input type="hidden" name="vat" value="<?= number_format($vat,2,'.','') ?>">
        <input type="hidden" name="shipping_fee" value="<?= number_format($ship,2,'.','') ?>">
        <input type="hidden" name="total" value="<?= number_format($grand,2,'.','') ?>">
        <input type="hidden" id="payMethod" name="payment_method" value="">
        <input type="hidden" id="payVerified" name="payment_verified" value="0">

        <div class="co-form">
          <div class="co-field"><label>First Name</label><input name="first_name" required></div>
          <div class="co-field"><label>Last Name</label><input name="last_name" required></div>

          <div class="co-field"><label>Email</label><input type="email" name="email" required></div>
          <div class="co-field"><label>Phone Number</label><input name="phone" inputmode="numeric" maxlength="11" pattern="\d{11}" placeholder="09xxxxxxxxx" required></div>

          <div class="co-field"><label>State/Province</label><input name="province" required></div>
          <div class="co-field"><label>City/Municipality</label><input name="city" required></div>

          <div class="co-field"><label>Postal Code</label><input name="postal" required></div>
          <div class="co-field"><label>Barangay/District</label><input name="barangay" required></div>

          <div class="co-field"><label>Landmark</label><input name="landmark" required></div>
          <div class="co-field"><label>House No.</label><input name="house_no" required></div>
        </div>

        <!-- ===== Bottom: Summary + Payment ===== -->
        <div class="co-row">
          <!-- Summary -->
          <div class="co-card co-sum">
            <div class="co-h">Order Summary</div>
            <div class="line"><span><?= htmlspecialchars($name) ?> <span class="co-muted">× <?= $qty ?></span></span><strong>₱ <?= number_format($sub,2) ?></strong></div>
            <div class="line"><span>VAT (12%)</span><span>₱ <?= number_format($vat,2) ?></span></div>
            <div class="line"><span>Shipping Fee</span><span>₱ <?= number_format($ship,2) ?></span></div>
            <div class="line total"><span>Total</span><span>₱ <?= number_format($grand,2) ?></span></div>
          </div>

          <!-- Payment -->
          <div class="co-card">
            <div class="co-h">Payment</div>
            <div class="co-payhead">
              <div class="co-muted">Choose QR code</div>
              <span id="payBadge" class="co-badge">Awaiting verification</span>
            </div>

            <div class="co-qr" style="margin-bottom:10px">
              <button type="button" class="co-btn" id="btnGCash">GCash</button>
              <button type="button" class="co-btn" id="btnInsta">InstaPay</button>
            </div>

            <div style="display:flex;gap:8px;align-items:center;justify-content:flex-end">
              <a class="co-btn" style="padding:0 12px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;height:38px"
                 href="/customer/products.php">Back</a>
              <button class="co-buy" id="btnBuy" type="submit" disabled>BUY NOW</button>
            </div>
            <div class="co-muted" style="margin-top:6px">BUY NOW unlocks after successful payment verification.</div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- ===== Verify Payment Modal (GCash/InstaPay) ===== -->
  <div id="coBack" class="co-back" role="dialog" aria-modal="true">
    <div class="co-modal">
      <div id="coModalTitle" class="co-mhd">Verifying Payment</div>
      <div class="co-mbd">
        <div class="co-mfield"><label>Amount</label><input id="vpAmount" value="<?= number_format($grand,2,'.','') ?>"></div>
        <div class="co-mfield"><label>Account / Number</label><input id="vpNumber" placeholder="e.g. 09xxxxxxxxx or bank ref"></div>
        <div class="co-mfield"><label>Account Name</label><input id="vpName" placeholder="Juan Dela Cruz"></div>
        <div class="co-mfield"><label>Transaction / Reference No.</label><input id="vpRef" placeholder="eg. 7GJ4X..."></div>

      </div>
      <div class="co-mft">
        <button class="co-pbtn" id="vpCancel">Cancel</button>
        <button class="co-pbtn primary" id="vpConfirm">Confirm Payment</button>
      </div>
    </div>
  </div>

  <script>
    (function(){
      const back   = document.getElementById('coBack');
      const title  = document.getElementById('coModalTitle');
      const buyBtn = document.getElementById('btnBuy');
      const badge  = document.getElementById('payBadge');
      const mthd   = document.getElementById('payMethod');
      const ok     = document.getElementById('payVerified');

      function openModal(label){
        title.textContent = 'Verifying Payment (' + label + ')';
        back.style.display = 'flex';
      }

      document.getElementById('btnGCash').addEventListener('click', ()=>{
        mthd.value = 'gcash';
        openModal('GCash');
      });
      document.getElementById('btnInsta').addEventListener('click', ()=>{
        mthd.value = 'instapay';
        openModal('InstaPay');
      });

      document.getElementById('vpCancel').addEventListener('click', ()=> back.style.display='none');
      back.addEventListener('click', (e)=>{ if(e.target===back) back.style.display='none'; });

      document.getElementById('vpConfirm').addEventListener('click', ()=>{
        const amt = document.getElementById('vpAmount').value.trim();
        const num = document.getElementById('vpNumber').value.trim();
        const nam = document.getElementById('vpName').value.trim();
        const ref = document.getElementById('vpRef').value.trim();
        if(!amt || !num || !nam || !ref){
          alert('Please complete all fields.');
          return;
        }
        ok.value = '1';
        badge.textContent = (mthd.value==='gcash' ? 'GCash' : 'InstaPay') + ' ✓ Verified';
        badge.classList.add('ok');
        buyBtn.disabled = false;
        back.style.display = 'none';
      });
    })();
  </script>
</body>
</html>
