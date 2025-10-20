<?php
require_once __DIR__ . '/_bootstrap.php';
$uid = require_customer_id();

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$order_id = (int)($body['order_id'] ?? 0);
$method   = strtolower(trim((string)($body['method'] ?? '')));
$deposit  = (int)($body['deposit_rate'] ?? 0);

if (!$order_id || !in_array($method, ['gcash', 'bpi'], true) || !in_array($deposit, [30, 50, 100], true)) {
  fail('Invalid payment decision payload.');
}

$pdo = db();
$stmt = $pdo->prepare("SELECT customer_id, total_amount FROM orders WHERE id=:id");
$stmt->execute([':id' => $order_id]);
$order = $stmt->fetch();
if (!$order || (int)$order['customer_id'] !== $uid) fail('Order not found.', 404);

$amount_due = round(((float)$order['total_amount']) * ($deposit / 100), 2);

try {
  // 1) Upsert payments row (optional lang sa flow mo)
  $stmt = $pdo->prepare("
    INSERT INTO payments (order_id, method, deposit_rate, amount_due, amount_paid, status)
    VALUES (:oid, :m, :d, :due, 0, 'PENDING')
    ON DUPLICATE KEY UPDATE
      method=VALUES(method),
      deposit_rate=VALUES(deposit_rate),
      amount_due=VALUES(amount_due),
      status='PENDING'
  ");
  $stmt->execute([':oid' => $order_id, ':m' => $method, ':d' => $deposit, ':due' => $amount_due]);

  // 2) Ensure installment plan exists: #1 (deposit, PENDING), #2 (remaining, UNPAID)
  $pdo->beginTransaction();

  // check if already created
  $chk = $pdo->prepare("SELECT COUNT(*) FROM payment_installments WHERE order_id=:oid");
  $chk->execute([':oid' => $order_id]);
  $count = (int)$chk->fetchColumn();

  $total = (float)$order['total_amount'];           // NOTE: kung column mo ay `total`, palitan mo sa SELECT sa itaas
  $depositAmount = round($total * ($deposit / 100), 2);
  $remaining     = max(0.0, round($total - $depositAmount, 2));

  if ($count === 0) {
    // create #1 (PENDING)
    $ins1 = $pdo->prepare("
      INSERT INTO payment_installments (order_id, installment_number, amount_due, amount_paid, status, due_date)
      VALUES (:oid, 1, :due, 0, 'PENDING', NULL)
    ");
    $ins1->execute([':oid' => $order_id, ':due' => $depositAmount]);

    // create #2 (UNPAID)
    if ($remaining > 0) {
      $ins2 = $pdo->prepare("
        INSERT INTO payment_installments (order_id, installment_number, amount_due, amount_paid, status, due_date)
        VALUES (:oid, 2, :due, 0, 'UNPAID', NULL)
      ");
      $ins2->execute([':oid' => $order_id, ':due' => $remaining]);
    }

    // flag order as installment
    $pdo->prepare("UPDATE orders SET is_installment=1 WHERE id=:oid")->execute([':oid' => $order_id]);
  } else {
    // if plan exists: make sure #1 is PENDING kung hindi pa nababayaran
    $pdo->prepare("
      UPDATE payment_installments
      SET status='PENDING'
      WHERE order_id=:oid AND installment_number=1 AND UPPER(status) NOT IN ('PAID','PENDING')
    ")->execute([':oid' => $order_id]);
  }

  $pdo->commit();

  ok(['amount_due' => $depositAmount]);
} catch (Throwable $e) {
  fail('DB error: ' . $e->getMessage(), 500);
}
