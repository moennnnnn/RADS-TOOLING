<?php
// /RADS-TOOLING/customer/payment_remaining_balance.php
// Payment page for remaining balance on existing orders

declare(strict_types=1);
session_start();

require_once __DIR__ . '/../backend/config/app.php';

// Check if user is logged in
if (empty($_SESSION['user']) || ($_SESSION['user']['aud'] ?? '') !== 'customer') {
    header('Location: /RADS-TOOLING/customer/cust_login.php');
    exit;
}

$customerId = (int)($_SESSION['user']['id'] ?? 0);
$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    header('Location: /RADS-TOOLING/customer/orders.php');
    exit;
}

// Fetch order details
try {
    $stmt = $pdo->prepare("
        SELECT o.*,
               COALESCE(
                   (SELECT SUM(pv.amount_reported)
                    FROM payment_verifications pv
                    WHERE pv.order_id = o.id
                    AND UPPER(COALESCE(pv.status,'')) IN ('VERIFIED','APPROVED')
                   ), 0
               ) as amount_paid
        FROM orders o
        WHERE o.id = ? AND o.customer_id = ?
        LIMIT 1
    ");
    $stmt->execute([$orderId, $customerId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: /RADS-TOOLING/customer/orders.php');
        exit;
    }

    $total = (float)($order['total_amount'] ?? 0);
    $amountPaid = (float)($order['amount_paid'] ?? 0);
    $remainingBalance = round(max(0, $total - $amountPaid), 2);

    $isFullyPaid = $remainingBalance <= 0.01;

    if (strtolower($order['status']) === 'cancelled') {
        // Cannot pay for cancelled order
        header('Location: /RADS-TOOLING/customer/orders.php');
        exit;
    }

} catch (Exception $e) {
    error_log("Payment remaining balance error: " . $e->getMessage());
    header('Location: /RADS-TOOLING/customer/orders.php');
    exit;
}

$user = $_SESSION['user'] ?? null;
$customerName = htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Customer');

// Calculate payment options
$option30 = round($remainingBalance * 0.30, 2);
$option50 = round($remainingBalance * 0.50, 2);
$option100 = $remainingBalance;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Pay Remaining Balance - Rads Tooling</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">

  <link rel="stylesheet" href="../assets/CSS/Homepage.css">
  <link rel="stylesheet" href="../assets/CSS/checkout.css">
  <link rel="stylesheet" href="../assets/CSS/checkout_modal.css">

  <style>
    * {
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
    }

    .material-symbols-rounded {
      font-family: 'Material Symbols Rounded' !important;
      font-weight: normal;
      font-style: normal;
      font-size: 24px;
      line-height: 1;
      letter-spacing: normal;
      text-transform: none;
      display: inline-block;
      white-space: nowrap;
      word-wrap: normal;
      direction: ltr;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      text-rendering: optimizeLegibility;
      font-feature-settings: 'liga';
      vertical-align: middle;
    }

    .review-wrap {
      max-width: 1400px !important;
      margin: 32px auto;
      padding: 0 24px;
    }

    .checkout-title {
      font-size: 32px !important;
      margin-bottom: 24px;
      font-weight: 700;
      color: #111827;
    }

    .checkout-grid {
      display: grid;
      grid-template-columns: 1.8fr 1fr;
      gap: 32px;
      align-items: start;
    }

    @media (max-width: 1024px) {
      .checkout-grid {
        grid-template-columns: 1fr;
      }
    }

    .checkout-card {
      background: #fff;
      border: 2px solid #e5e7eb;
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .card-title {
      font-size: 20px !important;
      font-weight: 700;
      margin-bottom: 20px;
      color: #111827;
      padding-bottom: 12px;
      border-bottom: 2px solid #f3f4f6;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card-title .material-symbols-rounded {
      font-size: 26px;
      color: #2f5b88;
    }

    .review-list {
      margin: 0;
      padding: 0;
      list-style: none;
    }

    .review-list li {
      padding: 12px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #f3f4f6;
      font-size: 15px;
    }

    .review-list li:last-child {
      border-bottom: none;
    }

    .review-list li span:first-child {
      color: #6b7280;
      font-weight: 500;
    }

    .review-list li span:last-child {
      color: #111827;
      font-weight: 600;
    }

    .review-list li.highlight {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%);
      padding: 16px;
      border-radius: 12px;
      border: 2px solid #fbbf24;
      margin-top: 12px;
    }

    .review-list li.highlight span:last-child {
      color: #b45309;
      font-size: 20px;
      font-weight: 700;
    }

    .rt-chips {
      display: flex;
      gap: 12px;
      margin: 20px 0;
    }

    .rt-chip {
      flex: 1;
      padding: 20px 16px;
      background: #f9fafb;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      text-align: center;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .rt-chip:hover {
      border-color: #2f5b88;
      background: #f0f9ff;
    }

    .rt-chip.active {
      border-color: #2f5b88;
      background: #e0f2fe;
      box-shadow: 0 4px 12px rgba(47, 91, 136, 0.2);
    }

    .rt-chip span {
      font-size: 18px;
      font-weight: 700;
      color: #111827;
    }

    .rt-chip small {
      font-size: 14px;
      color: #6b7280;
      font-weight: 500;
    }

    .amount-display {
      background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
      border: 2px solid #0ea5e9;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      margin: 24px 0;
    }

    .amount-display .label {
      font-size: 14px;
      color: #075985;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .amount-display .value {
      font-size: 32px;
      color: #0c4a6e;
      font-weight: 700;
    }

    .rt-btn {
      padding: 16px 32px;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      justify-content: center;
    }

    .rt-btn.main {
      background: linear-gradient(135deg, #2f5b88 0%, #1e3a5f 100%) !important;
      color: white !important;
      width: 100%;
    }

    .rt-btn.main:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(47, 91, 136, 0.4);
    }

    .rt-btn.main:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .rt-btn.ghost {
      background: white !important;
      border: 2px solid #e5e7eb !important;
      color: #4b5563 !important;
    }

    .rt-btn.ghost:hover {
      border-color: #2f5b88 !important;
      color: #2f5b88 !important;
    }

    .warning-banner {
      background: linear-gradient(135deg, #fff3cd 0%, #fef3c7 100%);
      border: 2px solid #fbbf24;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 24px;
      display: flex;
      align-items: start;
      gap: 12px;
    }

    .warning-banner .material-symbols-rounded {
      font-size: 28px;
      color: #b45309;
    }

    .warning-banner .content h4 {
      margin: 0 0 8px 0;
      font-weight: 700;
      color: #92400e;
      font-size: 16px;
    }

    .warning-banner .content p {
      margin: 0;
      color: #92400e;
      font-size: 14px;
      line-height: 1.6;
    }

    .fully-paid-banner {
      background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
      border: 2px solid #10b981;
      color: #065f46;
    }

    .fully-paid-banner .material-symbols-rounded {
      color: #059669;
    }

    /* Modals (reuse existing styles) */
    .rt-modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .rt-card.rt-step {
      background: white;
      border-radius: 20px;
      max-width: 500px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      padding: 28px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .rt-card h3 {
      font-size: 24px !important;
      font-weight: 700 !important;
      margin: 0 0 20px 0;
      color: #111827;
    }

    .rt-sub {
      color: #6b7280;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .rt-list {
      margin: 0;
      padding: 0;
      list-style: none;
    }

    .rt-list__item {
      position: relative;
      padding: 18px 24px;
      background: #f9fafb;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
      font-size: 16px;
      font-weight: 600;
      color: #111827;
    }

    .rt-list__item:hover {
      border-color: #2f5b88;
      background: #f0f9ff;
    }

    .rt-list__item.active {
      border-color: #2f5b88;
      background: #e0f2fe;
    }

    .rt-arrow {
      color: #2f5b88;
      font-size: 20px;
      font-weight: 700;
    }

    .rt-actions {
      margin-top: 24px;
      display: flex;
      gap: 12px;
    }

    .rt-qr {
      background: #f9fafb;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      padding: 24px;
      text-align: center;
      min-height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .rt-qr img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
    }

    .rt-form input,
    .rt-form textarea {
      width: 100%;
      padding: 14px 18px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-size: 15px;
      margin-bottom: 16px;
      transition: all 0.2s ease;
    }

    .rt-form input:focus,
    .rt-form textarea:focus {
      border-color: #2f5b88 !important;
      outline: none;
      box-shadow: 0 0 0 4px rgba(47, 91, 136, 0.1);
    }

    .rt-form label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #374151;
      font-size: 14px;
    }

    @media (max-width: 640px) {
      .review-wrap {
        padding: 0 16px;
      }

      .checkout-title {
        font-size: 24px !important;
      }

      .checkout-card {
        padding: 24px;
      }

      .rt-chips {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
<div class="main-wrapper">
  <nav class="navbar">
    <div class="navbar-container">
      <a href="/RADS-TOOLING/customer/homepage.php" class="navbar-logo">
        <span class="logo-text">R</span>ADS <span class="logo-text">T</span>OOLING
      </a>
      <ul class="navbar-menu">
        <li><a href="/RADS-TOOLING/customer/homepage.php">Home</a></li>
        <li><a href="/RADS-TOOLING/customer/products.php">Products</a></li>
        <li><a href="/RADS-TOOLING/customer/orders.php" class="active">My Orders</a></li>
      </ul>
      <div class="navbar-user">
        <span class="material-symbols-rounded">account_circle</span>
        <span><?php echo $customerName; ?></span>
      </div>
    </div>
  </nav>

  <main>
    <div class="review-wrap">
      <h1 class="checkout-title">
        <span class="material-symbols-rounded" style="vertical-align: middle; font-size: 36px;">payments</span>
        Pay Remaining Balance
      </h1>

      <?php if ($isFullyPaid): ?>
      <div class="warning-banner fully-paid-banner">
        <span class="material-symbols-rounded">check_circle</span>
        <div class="content">
          <h4>Order Fully Paid</h4>
          <p>This order has been fully paid. No remaining balance.</p>
        </div>
      </div>
      <?php else: ?>
      <div class="warning-banner">
        <span class="material-symbols-rounded">info</span>
        <div class="content">
          <h4>Remaining Balance Payment</h4>
          <p>You are paying the remaining balance for Order #<?php echo htmlspecialchars($order['order_code']); ?>. Choose how much you want to pay now.</p>
        </div>
      </div>
      <?php endif; ?>

      <div class="checkout-grid">
        <div>
          <div class="checkout-card">
            <div class="card-title">
              <span class="material-symbols-rounded">receipt</span>
              Order Summary
            </div>
            <ul class="review-list">
              <li>
                <span>Order Code:</span>
                <span><?php echo htmlspecialchars($order['order_code']); ?></span>
              </li>
              <li>
                <span>Total Order Amount:</span>
                <span>₱<?php echo number_format($total, 2); ?></span>
              </li>
              <li>
                <span>Amount Already Paid:</span>
                <span style="color: #27ae60;">₱<?php echo number_format($amountPaid, 2); ?></span>
              </li>
              <li class="highlight">
                <span>Remaining Balance:</span>
                <span>₱<?php echo number_format($remainingBalance, 2); ?></span>
              </li>
            </ul>
          </div>

          <?php if (!$isFullyPaid): ?>
          <div class="checkout-card" style="margin-top: 24px;">
            <div class="card-title">
              <span class="material-symbols-rounded">account_balance_wallet</span>
              Select Payment Amount
            </div>
            <p class="rt-sub">Choose how much of the remaining balance you want to pay now</p>

            <div class="rt-chips">
              <button class="rt-chip pay-option" data-percent="30" data-amount="<?php echo $option30; ?>">
                <span>30%</span>
                <small>₱<?php echo number_format($option30, 2); ?></small>
              </button>
              <button class="rt-chip pay-option" data-percent="50" data-amount="<?php echo $option50; ?>">
                <span>50%</span>
                <small>₱<?php echo number_format($option50, 2); ?></small>
              </button>
              <button class="rt-chip pay-option active" data-percent="100" data-amount="<?php echo $option100; ?>">
                <span>100%</span>
                <small>₱<?php echo number_format($option100, 2); ?></small>
              </button>
            </div>

            <div class="amount-display">
              <div class="label">You will pay now:</div>
              <div class="value" id="payNowAmountText">₱<?php echo number_format($option100, 2); ?></div>
            </div>

            <!-- Hidden fields for JavaScript -->
            <input type="hidden" id="orderId" value="<?php echo $orderId; ?>">
            <input type="hidden" id="remainingBalance" value="<?php echo $remainingBalance; ?>">

            <button class="rt-btn main" id="payNowBtn">
              <span class="material-symbols-rounded">payments</span>
              Pay ₱<?php echo number_format($option100, 2); ?>
            </button>
          </div>
          <?php endif; ?>

          <div style="margin-top: 24px;">
            <button class="rt-btn ghost" onclick="location.href='/RADS-TOOLING/customer/orders.php'">
              <span class="material-symbols-rounded">arrow_back</span>
              Back to Orders
            </button>
          </div>
        </div>

        <aside>
          <div class="checkout-card">
            <div class="card-title">
              <span class="material-symbols-rounded">help</span>
              Payment Guide
            </div>
            <div style="color: #6b7280; font-size: 14px; line-height: 1.8;">
              <strong style="color: #111827; display: block; margin-bottom: 8px;">How to Pay:</strong>
              1. Select payment amount (30%, 50%, or 100%)<br>
              2. Choose payment method (GCash or BPI)<br>
              3. Scan QR code with your app<br>
              4. Upload payment screenshot<br>
              5. Wait for admin verification
            </div>
          </div>

          <div class="checkout-card" style="margin-top: 24px;">
            <div class="card-title">
              <span class="material-symbols-rounded">lock</span>
              Secure Payment
            </div>
            <div style="color: #6b7280; font-size: 14px; line-height: 1.8;">
              • Choose flexible payment options<br>
              • Secure payment verification<br>
              • Track payment status in orders
            </div>
          </div>
        </aside>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="footer-bottom">
      <p>© 2025 RADS TOOLING INC. All rights reserved.</p>
    </div>
  </footer>
</div>

<!-- PAYMENT WIZARD MODALS -->
<div class="rt-modal" id="rtModal" hidden>
  <div class="rt-card rt-step" id="methodModal" hidden>
    <h3>Select Payment Method</h3>
    <p class="rt-sub">Choose your preferred payment option</p>
    <div class="rt-list">
      <button class="rt-list__item pay-chip" data-pay="gcash">
        <span>GCash</span>
        <span class="rt-arrow">→</span>
      </button>
      <button class="rt-list__item pay-chip" data-pay="bpi">
        <span>BPI</span>
        <span class="rt-arrow">→</span>
      </button>
    </div>
    <div class="rt-actions">
      <button class="rt-btn ghost" data-close>Cancel</button>
      <button class="rt-btn main" id="btnProceedToQR" disabled style="flex: 2;">Proceed</button>
    </div>
  </div>

  <div class="rt-card rt-step" id="qrModal" hidden>
    <h3>Scan QR Code to Pay</h3>
    <div class="rt-qr" id="qrBox">Loading QR Code...</div>
    <div class="rt-sub" style="font-size: 18px; margin: 16px 0; text-align: center;">
      Amount to pay: <b style="color: #e74c3c;" id="qrAmount">₱0.00</b>
    </div>
    <div style="font-size: 14px; color: #6b7280; text-align: center;">Scan with your selected payment app</div>

    <div class="rt-actions">
      <button class="rt-btn ghost" data-back="#methodModal">Back</button>
      <button class="rt-btn main" id="btnIpaid" style="flex: 2;">I've Completed Payment</button>
    </div>
  </div>

  <div class="rt-card rt-step" id="verifyModal" hidden>
    <h3>Verify Your Payment</h3>
    <p class="rt-sub">Please provide your payment details for verification</p>
    <form class="rt-form" id="verifyForm">
      <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
      <input type="hidden" name="amount_paid" id="finalAmountPaid" value="<?php echo $option100; ?>">
      <input type="hidden" id="paymentMethod" name="method" value="">

      <div>
        <label>Account Name <span style="color: #e74c3c;">*</span></label>
        <input type="text" name="account_name" id="vpName" placeholder="Name on payment account" required>
      </div>

      <div>
        <label>Account Number / Mobile Number</label>
        <input type="text" name="account_number" id="vpNum" placeholder="e.g., 09171234567">
      </div>

      <div>
        <label>Reference Number <span style="color: #e74c3c;">*</span></label>
        <input type="text" name="reference_number" id="vpRef" placeholder="Transaction reference number" required>
      </div>

      <div>
        <label>Upload Screenshot <span style="color: #e74c3c;">*</span></label>
        <input type="file" name="screenshot" id="vpScreenshot" accept="image/*" required>
        <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">Upload proof of payment (JPG, PNG)</p>
      </div>
    </form>

    <div class="rt-actions">
      <button class="rt-btn ghost" data-back="#qrModal">Back</button>
      <button class="rt-btn main" id="btnConfirmVerification" style="flex: 2;">Submit Payment</button>
    </div>
  </div>

  <div class="rt-card rt-step" id="finalNotice" hidden>
    <div style="text-align: center; margin-bottom: 20px;">
      <span style="font-size: 64px;">✅</span>
    </div>
    <h3 style="text-align: center; color: #27ae60;">Payment Submitted!</h3>
    <p style="text-align: center; color: #666; margin-bottom: 24px;">
      Your payment is being verified. You will be notified once approved.
    </p>
    <div class="rt-actions" style="justify-content: center;">
      <button class="rt-btn main" id="btnGoOrders">View My Orders</button>
    </div>
  </div>
</div>

<script>
window.RT_PAYMENT = <?php echo json_encode([
  'order_id' => $orderId,
  'order_code' => $order['order_code'],
  'remaining_balance' => $remainingBalance,
  'total' => $total,
  'amount_paid' => $amountPaid,
  'is_fully_paid' => $isFullyPaid,
  'option_30' => $option30,
  'option_50' => $option50,
  'option_100' => $option100
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
console.log('✅ RT_PAYMENT:', window.RT_PAYMENT);
</script>
<script src="/RADS-TOOLING/assets/JS/payment_remaining.js" defer></script>
</body>
</html>
