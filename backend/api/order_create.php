<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/_bootstrap.php';

// Check if customer is logged in
if (!isset($_SESSION['customer']) || empty($_SESSION['customer'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Please login to place an order',
        'redirect' => '/RADS-TOOLING/customer/login.php'
    ]);
    exit;
}

$uid = $_SESSION['customer']['id'];

$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];

$pid = (int)($body['pid'] ?? 0);
$qty = max(1, (int)($body['qty'] ?? 1));
$subtotal = (float)($body['subtotal'] ?? 0);
$vat = (float)($body['vat'] ?? 0);
$total = (float)($body['total'] ?? 0);
$mode = ($body['mode'] ?? 'pickup') === 'delivery' ? 'delivery' : 'pickup';
$info = (array)($body['info'] ?? []);

if ($pid <= 0 || $total <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order payload.']);
    exit;
}

$pdo = db();
$pdo->beginTransaction();

try {
    // Create order
    $stmt = $pdo->prepare("INSERT INTO orders
        (order_code, customer_id, mode, status, subtotal, vat, total_amount)
        VALUES (CONCAT('RT', DATE_FORMAT(NOW(),'%y%m%d'), LPAD(FLOOR(RAND()*9999), 4, '0')),
                :cid, :mode, 'PENDING_PAYMENT', :sub, :vat, :tot)");
    
    $stmt->execute([
        ':cid' => $uid, 
        ':mode' => $mode, 
        ':sub' => $subtotal, 
        ':vat' => $vat, 
        ':tot' => $total
    ]);
    
    $order_id = (int)$pdo->lastInsertId();

    // Insert order item (you can pull real product info here if needed)
    $prodName = 'Selected Cabinet';
    $unitPrice = $subtotal / $qty;
    
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, name, unit_price, qty, line_total)
                           VALUES (:oid, :pid, :name, :price, :qty, :lt)");
    
    $stmt->execute([
        ':oid' => $order_id, 
        ':pid' => $pid, 
        ':name' => $prodName,
        ':price' => $unitPrice, 
        ':qty' => $qty, 
        ':lt' => $subtotal
    ]);

    // Save address/contact snapshot
    $stmt = $pdo->prepare("INSERT INTO order_addresses
        (order_id, type, first_name, last_name, phone, email, province, city, barangay, street, postal)
        VALUES (:oid, :type, :fn, :ln, :ph, :em, :pv, :ct, :br, :st, :po)");
    
    $stmt->execute([
        ':oid' => $order_id,
        ':type' => $mode === 'delivery' ? 'shipping' : 'billing',
        ':fn' => $info['first_name'] ?? '',
        ':ln' => $info['last_name'] ?? '',
        ':ph' => $info['phone'] ?? '',
        ':em' => $info['email'] ?? '',
        ':pv' => $info['province'] ?? '',
        ':ct' => $info['city'] ?? '',
        ':br' => $info['barangay'] ?? '',
        ':st' => $info['street'] ?? '',
        ':po' => $info['postal'] ?? ''
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully!',
        'order_id' => $order_id
    ]);

} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Order creation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order. Please try again.'
    ]);
}