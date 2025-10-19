<?php
require_once __DIR__ . '/_bootstrap.php';

$uid = require_customer_id();

$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];

$pid      = (int)($body['pid'] ?? 0);
$qty      = max(1, (int)($body['qty'] ?? 1));
$subtotal = (float)($body['subtotal'] ?? 0);
$vat      = (float)($body['vat'] ?? 0);
$total    = (float)($body['total'] ?? 0);
$mode     = ($body['mode'] ?? 'pickup') === 'delivery' ? 'delivery' : 'pickup';
$info     = (array)($body['info'] ?? []);

if ($pid <= 0 || $total <= 0) fail('Invalid order payload.');

$pdo = db();
$pdo->beginTransaction();

try {
  // Create order
  $stmt = $pdo->prepare("INSERT INTO orders
    (order_code, customer_id, mode, status, subtotal, vat, total_amount)
    VALUES (CONCAT('RT', DATE_FORMAT(NOW(),'%y%m%d'), LPAD(FLOOR(RAND()*9999), 4, '0')),
            :cid, :mode, 'PENDING_PAYMENT', :sub, :vat, :tot)");
  $stmt->execute([
    ':cid'=>$uid, ':mode'=>$mode, ':sub'=>$subtotal, ':vat'=>$vat, ':tot'=>$total
  ]);
  $order_id = (int)$pdo->lastInsertId();

  // (You can pull real product info here if needed)
  $prodName = 'Selected Cabinet';
  $unitPrice = $subtotal / $qty;
  $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, name, unit_price, qty, line_total)
                         VALUES (:oid, :pid, :name, :price, :qty, :lt)");
  $stmt->execute([
    ':oid'=>$order_id, ':pid'=>$pid, ':name'=>$prodName,
    ':price'=>$unitPrice, ':qty'=>$qty, ':lt'=>$subtotal
  ]);

  // Save address/contact snapshot
  $stmt = $pdo->prepare("INSERT INTO order_addresses
    (order_id, type, first_name, last_name, phone, email, province, city, barangay, street, postal)
    VALUES (:oid, :type, :fn, :ln, :ph, :em, :pv, :ct, :br, :st, :po)");
  $stmt->execute([
    ':oid'=>$order_id,
    ':type'=>$mode,
    ':fn'=>$info['first_name'] ?? ($info['full_name'] ?? ''),
    ':ln'=>$info['last_name'] ?? '',
    ':ph'=>$info['phone'] ?? '',
    ':em'=>$info['email'] ?? '',
    ':pv'=>$info['province'] ?? '',
    ':ct'=>$info['city'] ?? '',
    ':br'=>$info['barangay'] ?? '',
    ':st'=>$info['street'] ?? ($info['address'] ?? ''),
    ':po'=>$info['postal'] ?? ''
  ]);

  $code = $pdo->query("SELECT order_code FROM orders WHERE id={$order_id}")->fetchColumn();

  $pdo->commit();
  ok(['order_id'=>$order_id, 'order_code'=>$code]);
} catch (Throwable $e) {
  $pdo->rollBack();
  fail('DB error: ' . $e->getMessage(), 500);
}
