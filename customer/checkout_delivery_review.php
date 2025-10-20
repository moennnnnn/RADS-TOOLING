<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../backend/config/app.php';

// Get form data
$mode = $_POST['mode'] ?? 'pickup';
$pid  = (int)($_POST['pid'] ?? 0);

// Get customer contact info
$first    = $_POST['first_name'] ?? '';
$last     = $_POST['last_name'] ?? '';
$email    = $_POST['email'] ?? '';
$phone    = $_POST['phone'] ?? '';

// Address (for delivery only)
$province = $_POST['province'] ?? '';
$city     = $_POST['city'] ?? '';
$barangay = $_POST['barangay'] ?? '';
$postal   = $_POST['postal'] ?? '';
$street   = $_POST['street'] ?? '';

// Fetch product details from database
$product = null;
if ($pid > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'released'");
    $stmt->execute([$pid]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Redirect back if no product found
if (!$product) {
    header('Location: /RADS-TOOLING/customer/products.php');
    exit;
}

// Calculate prices
$qty       = 1; // Default quantity
$basePrice = (float)($product['base_price'] ?? $product['price'] ?? 0);
$subtotal  = $basePrice * $qty;
$vat       = $subtotal * 0.12; // 12% VAT
$shipping  = 0;

// Add shipping for delivery (you can make this dynamic based on location)
if ($mode === 'delivery') {
    $shipping = 500; // Example flat rate, or calculate based on location
}

$total = $subtotal + $vat + $shipping;

$user = $_SESSION['user'] ?? null;
$customerName = htmlspecialchars($user['name'] ?? $user['username'] ?? 'Customer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Review & Payment - Rads Tooling</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="../assets/CSS/Homepage.css">
  <link rel="stylesheet" href="../assets/CSS/chat-widget.css">
  <link rel="stylesheet" href="../assets/CSS/about.css">
  <link rel="stylesheet" href="../assets/CSS/checkout.css">
  <style>
    .review-wrap{max-width:1100px;margin:24px auto;padding:0 16px}
    .checkout-title{font-size:28px;margin-bottom:20px;font-weight:700}
    .checkout-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}
    @media (max-width:860px){.checkout-grid{grid-template-columns:1fr}}
    .checkout-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
    .card-title{font-size:18px;font-weight:700;margin-bottom:12px}
    .review-list{margin:0;padding:0;list-style:none}
    .review-list li{display:flex;justify-content:space-between;border-bottom:1px dashed #e5e7eb;padding:8px 0}
    .review-list li:last-child{border:0}
    .sum-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0}
    .sum-row.total{font-size:20px;font-weight:700;border-top:2px solid #111827;margin-top:8px;padding-top:12px}
    .rt-actions{margin-top:16px}
  </style>
</head>
<body>
<div class="page-wrapper">
  <header class="navbar">
    <div class="navbar-container">
      <div class="navbar-brand">
        <a class="logo-link" href="/RADS-TOOLING/customer/homepage.php">
          <span class="logo-text">R</span>ADS <span class="logo-text">T</span>OOLING
        </a>
      </div>
      <div class="navbar-actions">
        <a class="cart-button" href="/RADS-TOOLING/cart.php">
          <span class="material-symbols-rounded">shopping_cart</span>
          <span id="cartCount" class="cart-badge">0</span>
        </a>
      </div>
    </div>
    <nav class="navbar-menu">
      <a href="/RADS-TOOLING/customer/homepage.php" class="nav-menu-item">Home</a>
      <a href="/RADS-TOOLING/customer/about.php" class="nav-menu-item">About</a>
      <a href="/RADS-TOOLING/customer/products.php" class="nav-menu-item">Products</a>
    </nav>
  </header>

  <main class="checkout-main review-wrap">
    <h1 class="checkout-title">Review & Payment</h1>
    <div class="checkout-grid">
      <!-- LEFT: Order Details -->
      <section class="checkout-card">
        <h3 class="card-title">Order Summary</h3>
        <ul class="review-list">
          <li><span>Product</span><strong><?= htmlspecialchars($product['name']) ?></strong></li>
          <li><span>Unit Price</span><strong>₱ <?= number_format($basePrice,2) ?></strong></li>
          <li><span>Quantity</span><strong><?= $qty ?></strong></li>
        </ul>

        <h3 class="card-title" style="margin:20px 0 12px">Contact Information</h3>
        <ul class="review-list">
          <li><span>Name</span><strong><?= htmlspecialchars("$first $last") ?></strong></li>
          <li><span>Email</span><strong><?= htmlspecialchars($email) ?></strong></li>
          <li><span>Phone</span><strong><?= htmlspecialchars($phone) ?></strong></li>
        </ul>

        <?php if ($mode === 'delivery'): ?>
        <h3 class="card-title" style="margin:20px 0 12px">Delivery Address</h3>
        <ul class="review-list">
          <li><span>Province</span><strong><?= htmlspecialchars($province) ?></strong></li>
          <li><span>City/Municipality</span><strong><?= htmlspecialchars($city) ?></strong></li>
          <li><span>Barangay</span><strong><?= htmlspecialchars($barangay) ?></strong></li>
          <li><span>Postal Code</span><strong><?= htmlspecialchars($postal) ?></strong></li>
          <li><span>Street</span><strong><?= htmlspecialchars($street) ?></strong></li>
        </ul>
        <?php else: ?>
        <div style="margin-top:20px;padding:12px;background:#f0f9ff;border-radius:8px;border:1px solid #bae6fd">
          <p style="margin:0;color:#0369a1;font-weight:600">📍 Pick-up Location:</p>
          <p style="margin:4px 0 0;color:#075985">Green Breeze, Piela, Dasmariñas, Cavite</p>
        </div>
        <?php endif; ?>

        <div class="rt-actions">
          <button type="button" class="rt-btn main" id="inlineBuyBtn">Pay Now</button>
        </div>
      </section>

      <!-- RIGHT: Summary -->
      <aside class="checkout-card">
        <h3 class="card-title">Price Summary</h3>
        <div class="sum-row"><span>Subtotal</span><strong>₱ <?= number_format($subtotal,2) ?></strong></div>
        <div class="sum-row"><span>VAT (12%)</span><span>₱ <?= number_format($vat,2) ?></span></div>
        <?php if ($mode === 'delivery'): ?>
          <div class="sum-row"><span>Shipping</span><span>₱ <?= number_format($shipping,2) ?></span></div>
        <?php endif; ?>
        <div class="sum-row total"><strong>Total</strong><strong>₱ <?= number_format($total,2) ?></strong></div>
      </aside>
    </div>
  </main>

  <!-- PAYMENT WIZARD MODALS -->
  <div class="rt-modal" id="rtModal" hidden>
    <div class="rt-modal__backdrop"></div>

    <!-- STEP 1: Payment Method -->
    <div class="rt-card rt-step" id="methodModal" hidden>
      <h3>Select Payment Method</h3>
      <div class="rt-list">
        <button class="rt-list__item pay-chip" data-pay="gcash">GCash <span class="rt-arrow">→</span></button>
        <button class="rt-list__item pay-chip" data-pay="bpi">BPI <span class="rt-arrow">→</span></button>
      </div>
      <div class="rt-actions">
        <button class="rt-btn ghost" data-close>Close</button>
        <button class="rt-btn main" id="btnChooseDeposit" disabled>Next</button>
      </div>
    </div>

    <!-- STEP 2: Deposit Amount -->
    <div class="rt-card rt-step" id="depositModal" hidden>
      <h3>Select Payment Amount</h3>
      <div class="rt-sub">Total Amount: <b id="totalLabel">₱<?= number_format($total,2) ?></b></div>

      <div class="rt-chips">
        <button class="rt-chip pay-chip" data-dep="30">30%<small id="amt30">₱<?= number_format($total * 0.30,2) ?></small></button>
        <button class="rt-chip pay-chip" data-dep="50">50%<small id="amt50">₱<?= number_format($total * 0.50,2) ?></small></button>
        <button class="rt-chip pay-chip" data-dep="100">100%<small id="amt100">₱<?= number_format($total,2) ?></small></button>
      </div>

      <div class="rt-actions">
        <button class="rt-btn ghost" data-back="#methodModal">Back</button>
        <button class="rt-btn main" id="btnPayNow" disabled>Proceed to Pay</button>
      </div>
    </div>

    <!-- STEP 3: QR Code -->
    <div class="rt-card rt-step" id="qrModal" hidden>
      <h3>Scan QR Code to Pay</h3>
      <div class="rt-qrwrap">
        <div id="qrBox" class="rt-qr">QR Code</div>
      </div>
      <div class="rt-sub">Amount to pay: <b id="amountDueLabel">₱0.00</b></div>
      <div class="rt-small">Scan with your selected app</div>

      <div class="rt-actions">
        <button class="rt-btn ghost" data-back="#depositModal">Back</button>
        <button class="rt-btn main" id="btnIpaid">I've Completed Payment</button>
      </div>
    </div>

    <!-- STEP 4: Verify Payment -->
    <div class="rt-card rt-step" id="verifyModal" hidden>
      <h3>Verify Your Payment</h3>
      <div class="rt-form">
        <label>Account Name
          <input type="text" id="vpName" placeholder="Enter your account name" required>
        </label>
        <label>Account Number
          <input type="text" id="vpNum" placeholder="Enter your account number" required>
        </label>
        <label>Reference Number
          <input type="text" id="vpRef" placeholder="Enter transaction reference number" required>
        </label>
        <label>Amount Paid
          <input type="number" id="vpAmt" step="0.01" placeholder="0.00" required>
        </label>
        <label>Payment Screenshot
          <input type="file" id="vpShot" accept="image/*" required>
        </label>
      </div>
      <div class="rt-actions">
        <button class="rt-btn ghost" data-back="#qrModal">Back</button>
        <button class="rt-btn main" id="btnVerify">Submit Verification</button>
      </div>
    </div>

    <!-- STEP 5: Success -->
    <div class="rt-card rt-step" id="finalNotice" hidden>
      <h3>Payment Submitted</h3>
      <p class="rt-sub">
        Your payment is <b>under verification</b>. Please check your Profile → Orders to see if it's approved.
      </p>
      <div class="rt-actions">
        <button class="rt-btn main" id="btnGoOrders">Go to My Orders</button>
        <button class="rt-btn ghost" data-close>Close</button>
      </div>
    </div>
  </div>

  <!-- Hidden fields for JS -->
  <input type="hidden" id="paymentMethod">
  <input type="hidden" id="depositRate">

  <footer class="footer"><div class="footer-bottom"><p>© 2025 RADS TOOLING INC.</p></div></footer>
</div>

<!-- Bootstrap order data for JS -->
<script>
window.RT_ORDER = <?= json_encode([
  'pid'      => (int)($product['id'] ?? 0),
  'qty'      => (int)($qty ?? 1),
  'subtotal' => (float)($subtotal ?? 0),
  'vat'      => (float)($vat ?? 0),
  'total'    => (float)($total ?? 0),
  'mode'     => $mode ?? 'delivery',
  'info'     => (($mode ?? 'delivery') === 'delivery')
    ? [
        'delivery' => [
          'first_name' => $first_name ?? '',
          'last_name'  => $last_name  ?? '',
          'email'      => $email      ?? '',
          'phone'      => $phone      ?? '',
          'province'   => $province   ?? '',
          'city'       => $city       ?? '',
          'barangay'   => $barangay   ?? '',
          'postal'     => $postal     ?? '',
          'street'     => $street     ?? '',
        ]
      ]
    : [
        'pickup' => [
          'first_name' => $first_name ?? '',
          'last_name'  => $last_name  ?? '',
          'phone'      => $phone      ?? '',
        ]
      ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
console.log('RT_ORDER:', window.RT_ORDER); // quick check
</script>
<script src="/RADS-TOOLING/assets/JS/checkout.js" defer></script>
</body>
</html>