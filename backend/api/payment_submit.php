<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../lib/payments.php';
$uid = require_customer_id();

$order_id        = (int)($_POST['order_id'] ?? 0);
$account_name    = trim((string)($_POST['account_name'] ?? ''));
$account_number  = trim((string)($_POST['account_number'] ?? ''));
$reference       = trim((string)($_POST['reference_number'] ?? ''));
$amount_paid     = (float)($_POST['amount_paid'] ?? 0);

if (!$order_id) fail('Missing order_id.');
if (!$account_name || !$reference || $amount_paid <= 0) fail('Incomplete verification data.');

$pdo = db();
$stmt = $pdo->prepare("SELECT o.customer_id, IFNULL(p.method,'gcash') AS method
                       FROM orders o
                       LEFT JOIN payments p ON p.order_id=o.id
                       WHERE o.id=:id");
$stmt->execute([':id' => $order_id]);
$row = $stmt->fetch();
if (!$row || (int)$row['customer_id'] !== $uid) fail('Order not found.', 404);

$method = (string)$row['method'] ?: 'gcash';

$upload_dir = __DIR__ . '/../../uploads/payments';
if (!is_dir($upload_dir)) {
  if (!@mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
    fail('Cannot create payments upload folder.');
  }
}

$shot_path = null;
if (!empty($_FILES['screenshot']['tmp_name'])) {
  $orig = $_FILES['screenshot']['name'] ?? 'proof.jpg';
  $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION) ?: 'jpg');
  if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) $ext = 'jpg';
  $fname = 'proof_' . $order_id . '_' . time() . '.' . $ext;
  $dest  = rtrim($upload_dir, '/') . '/' . $fname;
  if (!move_uploaded_file($_FILES['screenshot']['tmp_name'], $dest)) {
    fail('Failed to save screenshot.');
  }
  $shot_path = 'uploads/payments/' . $fname;
}

// ---- ENFORCE MINIMUM AMOUNTS ----
// Get order total & sum of VERIFIED payments
$st = $pdo->prepare("SELECT total_amount AS total FROM orders WHERE id=:id");
$st->execute([':id' => $order_id]);
$ord = $st->fetch(PDO::FETCH_ASSOC);
if (!$ord) fail('Order not found.', 404);

$total = (float)($ord['total'] ?? 0);

// Sum of VERIFIED payments
$st = $pdo->prepare("SELECT COALESCE(SUM(amount_paid),0) AS paid FROM payments WHERE order_id=:id AND UPPER(status)='VERIFIED'");
$st->execute([':id' => $order_id]);
$paid = (float)($st->fetch(PDO::FETCH_ASSOC)['paid'] ?? 0);

$remaining = max(0.0, round($total - $paid, 2));
if ($remaining <= 0) fail('Order is already fully paid.');

// If first payment, enforce chosen deposit_rate (from `payments`)
$st = $pdo->prepare("SELECT COALESCE(deposit_rate,0) AS deposit_rate FROM payments WHERE order_id=:id");
$st->execute([':id' => $order_id]);
$depRow = $st->fetch(PDO::FETCH_ASSOC);
$chosenRate = (int)($depRow['deposit_rate'] ?? 0);

// Determine minimum allowed this time
$minThisPayment = 0.0;

if ($paid <= 0 && in_array($chosenRate, [30, 50, 100], true)) {
  // First payment must meet chosen deposit
  $minThisPayment = round($total * ($chosenRate / 100), 2);
} else {
  // Subsequent payments must be >= 30% of remaining (except if paying full remaining)
  $minThisPayment = round($remaining * 0.30, 2);
}

// Allow paying the full remaining even if it's < minThisPayment (e.g., last few pesos)
$payingFull = abs($amount_paid - $remaining) < 0.01;

if (!$payingFull && $amount_paid + 0.001 < $minThisPayment) {
  fail('Minimum payment is ₱' . number_format($minThisPayment, 2) . '. Your remaining balance is ₱' . number_format($remaining, 2) . '.');
}

$pdo->beginTransaction();
try {
  // Insert verification row
  $stmt = $pdo->prepare("INSERT INTO payment_verifications
    (order_id, method, account_name, account_number, reference_number, amount_reported, screenshot_path, status)
    VALUES (:oid, :m, :an, :ac, :ref, :amt, :img, 'PENDING')");
  $stmt->execute([
    ':oid' => $order_id,
    ':m' => $method,
    ':an' => $account_name,
    ':ac' => $account_number ?: null,
    ':ref' => $reference,
    ':amt' => $amount_paid,
    ':img' => $shot_path
  ]);

  // Ensure payments row exists, but DO NOT add to amount_paid here.
  // amount_paid will be added ONLY on admin APPROVE to avoid double counting.
  $pdo->prepare("
  INSERT INTO payments (order_id, method, deposit_rate, amount_due, amount_paid, status)
  VALUES (:oid, :m, 0, 0, 0, 'PENDING')
  ON DUPLICATE KEY UPDATE method=VALUES(method), status='PENDING'
")->execute([':oid' => $order_id, ':m' => $method]);


  $pdo->commit();
  ok(['message' => 'Payment submitted for verification.']);
} catch (Throwable $e) {
  $pdo->rollBack();
  fail('DB error: ' . $e->getMessage(), 500);
}
