<?php
declare(strict_types=1);
session_start();
$uid = $_SESSION['user_id'] ?? null; // optional

// Basic product from query (fallback kung di mo muna kukunin sa DB)
$pid   = (int)($_GET['pid'] ?? 0);
$name  = $_GET['name'] ?? 'Cabinet';
$price = (float)($_GET['price'] ?? 0);
$qty   = (int)($_GET['qty'] ?? 1);
$sub   = $price * $qty;
$vat   = round($sub * 0.12, 2); // PH VAT 12%
$total = $sub + $vat;

?>
<!doctype html>
<html lang="en"><head>
  <meta charset="utf-8">
  <title>Checkout — Delivery</title>
  <link rel="stylesheet" href="/assets/css/checkout.css">
</head><body>
  <div class="rt-grid rt-grid-2" style="max-width:1100px;margin:24px auto;padding:0 16px">
    <div class="rt-card">
      <h3>Delivery details</h3>
      <form method="post" action="/backend/api/order_create.php">
        <input type="hidden" name="type" value="delivery">
        <input type="hidden" name="pid" value="<?= $pid ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
        <input type="hidden" name="price" value="<?= number_format($price,2,'.','') ?>">
        <input type="hidden" name="qty" value="<?= $qty ?>">
        <input type="hidden" name="vat" value="<?= number_format($vat,2,'.','') ?>">

        <div class="rt-field">
          <label>First Name</label>
          <input class="rt-input" name="first_name" required>
        </div>
        <div class="rt-field">
          <label>Last Name</label>
          <input class="rt-input" name="last_name" required>
        </div>
        <div class="rt-field">
          <label>Email</label>
          <input class="rt-input" type="email" name="email" required>
        </div>

        <div class="rt-field">
          <label>Phone Number <span class="rt-kbd">+63</span></label>
          <input class="rt-input" name="phone" inputmode="numeric" maxlength="11" pattern="\\d{11}" placeholder="09xxxxxxxxx" required>
        </div>

        <div class="rt-field"><label>Region/Province</label><input class="rt-input" name="province" required></div>
        <div class="rt-field"><label>City/Municipality</label><input class="rt-input" name="city" required></div>
        <div class="rt-field"><label>Barangay/Street</label><input class="rt-input" name="street" required></div>
        <div class="rt-field"><label>Postal Code</label><input class="rt-input" name="postal" required></div>
        <div class="rt-row" style="margin-top:8px">
          <a class="rt-btn rt-btn-outline" href="/customer/product.php?pid=<?= $pid ?>">Back</a>
          <button class="rt-btn rt-btn-dark" type="submit">Continue to Payment</button>
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
</body></html>
