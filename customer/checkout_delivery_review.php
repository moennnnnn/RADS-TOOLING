<?php
// /RADS-TOOLING/customer/checkout_delivery_review.php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/guard.php';
guard_require_customer();

$mode = $_POST['mode'] ?? '';
$pid  = $_POST['pid']  ?? '';

// Save submitted form in session (basic pass-through for the review UI)
$_SESSION['checkout'] = [
  'mode' => $mode,
  'pid'  => $pid,
  'first_name' => $_POST['first_name'] ?? null,
  'last_name'  => $_POST['last_name']  ?? null,
  'full_name'  => $_POST['full_name']  ?? null,
  'phone'      => $_POST['phone']      ?? null,
  'province'   => $_POST['province']   ?? null,
  'city'       => $_POST['city']       ?? null,
  'barangay'   => $_POST['barangay']   ?? null,
  'street'     => $_POST['street']     ?? null,
  'postal'     => $_POST['postal']     ?? null,
  'address'    => $_POST['address']    ?? null,
];

// TODO: Pull product info by $pid from your DB.
// For now, simple placeholders to avoid errors:
$productName = 'Selected Cabinet';
$price       = 10000.00; // sample
$qty         = 1;
$subtotal    = $price * $qty;
$vat         = round($subtotal * 0.12, 2);
$total       = $subtotal + $vat;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Review & Payment</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/checkout.css">
</head>
<body>
  <div class="cz-shell">
    <div class="cz-header">
      <a class="cz-back" href="/RADS-TOOLING/customer/checkout.php?pid=<?php echo urlencode($pid) ?>">← Back</a>
      <h2>Review & Payment</h2>
      <div class="cz-price">Total: ₱<span id="orderTotal"><?php echo number_format($total, 2) ?></span></div>
    </div>

    <div class="grid-2">
      <div class="card">
        <h3 class="mb8">Order Summary</h3>
        <div class="summary">
          <div class="summary-row"><span>Product</span><b><?php echo htmlspecialchars($productName) ?></b></div>
          <div class="summary-row"><span>Price</span><b>₱<?php echo number_format($price, 2) ?></b></div>
          <div class="summary-row"><span>Qty</span><b><?php echo $qty ?></b></div>
          <div class="summary-row"><span>Subtotal</span><b>₱<?php echo number_format($subtotal, 2) ?></b></div>
          <div class="summary-row"><span>VAT (12%)</span><b>₱<?php echo number_format($vat, 2) ?></b></div>
          <div class="summary-row total"><span>Total</span><b>₱<?php echo number_format($total, 2) ?></b></div>
        </div>
      </div>

      <div class="card">
  <h3 class="mb8">Fulfillment</h3>
  <div class="muted mb8">
    Mode: <b><?php echo htmlspecialchars(strtoupper($mode)) ?></b>
  </div>

  <?php if ($mode === 'delivery'): ?>
    <div class="muted">
      <?php
        $fn = $_SESSION['checkout']['first_name'] ?? '';
        $ln = $_SESSION['checkout']['last_name'] ?? '';
        $ph = $_SESSION['checkout']['phone'] ?? '';
        $pv = $_SESSION['checkout']['province'] ?? '';
        $ct = $_SESSION['checkout']['city'] ?? '';
        $br = $_SESSION['checkout']['barangay'] ?? '';
        $st = $_SESSION['checkout']['street'] ?? '';
        $po = $_SESSION['checkout']['postal'] ?? '';
      ?>
      <div><b><?php echo htmlspecialchars("$fn $ln") ?></b></div>
      <div><?php echo htmlspecialchars($ph) ?></div>
      <div><?php echo htmlspecialchars("$st, $br, $ct, $pv $po") ?></div>
    </div>

  <?php else: ?>
    <div class="muted">
      <?php
        $fn = $_SESSION['checkout']['first_name'] ?? '';
        $ln = $_SESSION['checkout']['last_name']  ?? '';
        $ph = $_SESSION['checkout']['phone']      ?? '';
        $em = $_SESSION['checkout']['email']      ?? '';
      ?>
      <div><b><?php echo htmlspecialchars("$ln, $fn") ?></b></div>
      <div><?php echo htmlspecialchars($ph) ?></div>
      <div><?php echo htmlspecialchars($em) ?></div>
    </div>
  <?php endif; ?>
</div>

    <div class="card">
      <h3 class="mb8">Payment</h3>

      <input type="hidden" id="paymentMethod" name="payment_method">
      <div class="pay-row mb8">
        <button type="button" class="pay-chip" data-pay="gcash">GCash</button>
        <button type="button" class="pay-chip" data-pay="bpi">BPI</button>
      </div>

      <div class="row gap8">
        <button type="button" class="rt-btn" id="btnChooseDeposit">Choose deposit (30/50/100%)</button>
        <button type="button" class="rt-btn rt-btn-dark" id="btnPayNow" disabled>Pay now</button>
      </div>

      <input type="hidden" id="depositRate" value="">
      <input type="hidden" id="paidFlag" value="0">

      <div class="right mt12">
        <button id="buyBtn" class="rt-btn rt-btn-dark" disabled>Place Order</button>
      </div>
    </div>
  </div>

  <!-- Deposit modal -->
  <div id="depositModal" class="rt-modal" hidden>
    <div class="rt-modal__dialog">
      <h3>Select deposit</h3>
      <div class="pay-row">
        <button type="button" class="pay-chip" data-dep="30">30%</button>
        <button type="button" class="pay-chip" data-dep="50">50%</button>
        <button type="button" class="pay-chip" data-dep="100">100%</button>
      </div>
      <div class="right mt12">
        <button class="rt-btn rt-btn-dark" data-close="#depositModal">Done</button>
      </div>
    </div>
  </div>

  <!-- QR modal -->
  <div id="qrModal" class="rt-modal" hidden>
    <div class="rt-modal__dialog">
      <h3>Scan to pay</h3>
      <p class="muted">Use the QR below for your selected method.</p>
      <div id="qrBox" class="qr-box">
        <span class="muted">QR placeholder</span>
      </div>
      <div class="right mt12">
        <button class="rt-btn rt-btn-dark" id="btnIpaid">I’ve paid</button>
      </div>
    </div>
  </div>

  <!-- Payment verification modal -->
  <div id="verifyModal" class="rt-modal" hidden>
    <div class="rt-modal__dialog">
      <h3>Payment Verification</h3>
      <p class="muted">Fill this out so we can verify your payment.</p>
      <div class="rt-field"><label>Account Name</label><input id="vpName" class="rt-input" type="text" required></div>
      <div class="rt-field"><label>Account Number</label><input id="vpNum" class="rt-input" type="text" required></div>
      <div class="rt-field"><label>Reference Number</label><input id="vpRef" class="rt-input" type="text" required></div>
      <div class="rt-field"><label>Amount Paid</label><input id="vpAmt" class="rt-input" type="number" step="0.01" required></div>
      <div class="rt-field"><label>Screenshot</label><input id="vpShot" class="rt-input" type="file" accept="image/*" required></div>
      <div class="right mt12">
        <button id="btnVerify" type="button" class="rt-btn rt-btn-dark">Done</button>
      </div>
    </div>
  </div>

  <!-- Final notice -->
  <div id="finalNotice" class="rt-modal" hidden>
    <div class="rt-modal__dialog">
      <h3>Payment is under verification</h3>
      <p class="muted">Your payment has been submitted for review. Please check your profile for updates.</p>
      <div class="right">
        <a class="rt-btn rt-btn-dark" href="/RADS-TOOLING/customer/orders.php">Go to Orders</a>
      </div>
    </div>
  </div>

  <script>
    // Minimal data pass for JS (readonly)
    window.RT_ORDER_TOTAL = <?php echo json_encode($total) ?>;
  </script>
  <script src="/RADS-TOOLING/assets/JS/checkout.js" defer></script>
</body>
</html>
