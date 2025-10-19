<?php
require_once __DIR__ . '/_bootstrap.php';
$uid = require_customer_id();

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$order_id = (int)($body['order_id'] ?? 0);
$method   = strtolower(trim((string)($body['method'] ?? '')));
$deposit  = (int)($body['deposit_rate'] ?? 0);

if (!$order_id || !in_array($method, ['gcash','bpi'], true) || !in_array($deposit, [30,50,100], true)) {
  fail('Invalid payment decision payload.');
}

$pdo = db();
$stmt = $pdo->prepare("SELECT customer_id, total_amount FROM orders WHERE id=:id");
$stmt->execute([':id'=>$order_id]);
$order = $stmt->fetch();
if (!$order || (int)$order['customer_id'] !== $uid) fail('Order not found.', 404);

$amount_due = round(((float)$order['total_amount']) * ($deposit/100), 2);

try {
  // requires UNIQUE KEY on payments.order_id
  $stmt = $pdo->prepare("
    INSERT INTO payments (order_id, method, deposit_rate, amount_due, amount_paid, status)
    VALUES (:oid, :m, :d, :due, 0, 'PENDING_VERIFICATION')
    ON DUPLICATE KEY UPDATE
      method=VALUES(method),
      deposit_rate=VALUES(deposit_rate),
      amount_due=VALUES(amount_due),
      status='PENDING_VERIFICATION'
  ");
  $stmt->execute([':oid'=>$order_id, ':m'=>$method, ':d'=>$deposit, ':due'=>$amount_due]);

  ok(['amount_due'=>$amount_due]);
} catch (Throwable $e) {
  fail('DB error: ' . $e->getMessage(), 500);
}
