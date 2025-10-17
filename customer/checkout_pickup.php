<?php
declare(strict_types=1);
session_start();
$pid   = (int)($_GET['pid'] ?? 0);
$name  = $_GET['name'] ?? 'Cabinet';
$price = (float)($_GET['price'] ?? 0);
$qty   = (int)($_GET['qty'] ?? 1);
$sub   = $price * $qty;
$vat   = round($sub * 0.12, 2);
$total = $sub + $vat;
?>
<!doctype html>
<html lang="en"><head>
  <meta charset="utf-8">
  <title>Checkout — Pick up</title>
  <link rel="stylesheet" href="/assets/css/checkout.css">
</head><body>
  <div class="rt-grid rt-grid-2" style="max-width:1100px;margin:24px auto;padding:0 16px">
    <div class="rt-card">
      <h3>Pick up details & signature</h3>
      <form id="pickupForm">
        <input type="hidden" name="pid" value="<?= $pid ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
        <input type="hidden" name="price" value="<?= number_format($price,2,'.','') ?>">
        <input type="hidden" name="qty" value="<?= $qty ?>">
        <input type="hidden" name="vat" value="<?= number_format($vat,2,'.','') ?>">

        <div class="rt-field"><label>Full Name</label><input class="rt-input" name="full_name" required></div>
        <div class="rt-field"><label>Email</label><input class="rt-input" type="email" name="email" required></div>
        <div class="rt-field"><label>Phone (11 digits)</label><input class="rt-input" name="phone" inputmode="numeric" maxlength="11" pattern="\\d{11}" required></div>

        <label class="rt-muted" style="margin:8px 0 6px">Signature</label>
        <canvas id="sig" class="rt-sign"></canvas>
        <div class="rt-actions" style="margin-top:8px">
          <button type="button" id="sigClear" class="rt-btn rt-btn-outline">Clear</button>
          <button type="submit" id="sigSubmit" class="rt-btn rt-btn-dark">Continue to Payment</button>
        </div>
      </form>
    </div>

    <aside class="rt-card">
      <h3>Order Summary</h3>
      <div class="rt-row"><span><?= htmlspecialchars($name) ?> × <?= $qty ?></span><strong>₱ <?= number_format($sub,2) ?></strong></div>
      <div class="rt-row"><span>VAT (12%)</span><span>₱ <?= number_format($vat,2) ?></span></div>
      <hr>
      <div class="rt-row"><strong>Total</strong><strong>₱ <?= number_format($total,2) ?></strong></div>
    </aside>
  </div>

  <script>
    // Simple signature pad
    const canvas = document.getElementById('sig');
    const ctx = canvas.getContext('2d');
    let drawing=false, last={x:0,y:0};
    const resize=()=>{const r=canvas.getBoundingClientRect();canvas.width=r.width;canvas.height=r.height;ctx.lineWidth=2;ctx.lineCap='round';ctx.strokeStyle='#111827'};resize();addEventListener('resize',resize);
    const pos=(e)=>{const r=canvas.getBoundingClientRect();const p=e.touches?e.touches[0]:e;return{x:p.clientX-r.left,y:p.clientY-r.top}};
    canvas.addEventListener('pointerdown',e=>{drawing=true;last=pos(e)});
    canvas.addEventListener('pointermove',e=>{if(!drawing)return;const p=pos(e);ctx.beginPath();ctx.moveTo(last.x,last.y);ctx.lineTo(p.x,p.y);ctx.stroke();last=p});
    addEventListener('pointerup',()=>drawing=false);
    document.getElementById('sigClear').onclick=()=>{ctx.clearRect(0,0,canvas.width,canvas.height)};
    document.getElementById('pickupForm').onsubmit=async (e)=>{
      e.preventDefault();
      const png = canvas.toDataURL('image/png');
      const fd = new FormData(e.target);
      fd.append('signature_png', png);
      const r = await fetch('/backend/api/pickup_sign_upload.php',{method:'POST',body:fd});
      const j = await r.json();
      if(!j.success){alert(j.message||'Upload failed');return;}
      // proceed to payment page with created order id
      window.location.href = `/customer/payment.php?order=${encodeURIComponent(j.order_id)}`;
    };
  </script>
</body></html>
