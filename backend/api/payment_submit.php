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

  // Ensure payments row exists, then update status/amount
  $pdo->prepare("
    INSERT INTO payments (order_id, method, deposit_rate, amount_due, amount_paid, status)
    VALUES (:oid, :m, 0, 0, :paid, 'PENDING_VERIFICATION')
    ON DUPLICATE KEY UPDATE amount_paid=VALUES(amount_paid), status='PENDING_VERIFICATION'
  ")->execute([':oid' => $order_id, ':m' => $method, ':paid' => $amount_paid]);

  $pdo->commit();
  recalc_order_payment($pdo, $order_id);
  ok(['message' => 'Payment submitted for verification.']);
} catch (Throwable $e) {
  $pdo->rollBack();
  fail('DB error: ' . $e->getMessage(), 500);
}
