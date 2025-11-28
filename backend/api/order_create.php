<?php
// /backend/api/order_create.php
// ✅ FINAL FIXED VERSION - handles nested payload structure + better error messages

declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/_bootstrap.php';

// ✅ IMPROVED: Better session check with detailed error
if (!isset($_SESSION['customer']) || empty($_SESSION['customer'])) {
    error_log('Order create failed: No customer session');
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please login to place an order',
        'redirect' => '/customer/login.php'
    ]);
    exit;
}

$uid = (int)$_SESSION['customer']['id'];

// Read and parse request body
$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];

// ✅ IMPROVED: Log received payload for debugging
error_log('Order create request from customer ' . $uid . ': ' . $raw);

// Extract basic order data
$pid = (int)($body['pid'] ?? 0);
$qty = max(1, (int)($body['qty'] ?? 1));
$subtotal = (float)($body['subtotal'] ?? 0);
$vat = (float)($body['vat'] ?? 0);
$total = (float)($body['total'] ?? 0);
$mode = ($body['mode'] ?? 'pickup') === 'delivery' ? 'delivery' : 'pickup';

// Extract customization data
$selectedCustomizations = $body['selectedCustomizations'] ?? [];
$isCustomized = !empty($selectedCustomizations);
$clientAddonsTotal = (float)($body['computedAddonsTotal'] ?? 0);
$clientComputedTotal = (float)($body['computedTotal'] ?? 0);

// ✅ FIXED: Extract info from nested structure
$rawInfo = (array)($body['info'] ?? []);

// Handle nested structure (delivery/pickup inside info)
if ($mode === 'delivery' && isset($rawInfo['delivery'])) {
    $info = (array)$rawInfo['delivery'];
} elseif ($mode === 'pickup' && isset($rawInfo['pickup'])) {
    $info = (array)$rawInfo['pickup'];
} else {
    // Fallback: try using info directly
    $info = $rawInfo;
}

// ✅ IMPROVED: Validate required fields
$errors = [];

if ($pid <= 0) {
    $errors[] = 'Invalid product ID';
}

if ($total <= 0) {
    $errors[] = 'Invalid order total';
}

if (empty($info['first_name'])) {
    $errors[] = 'First name is required';
}

if (empty($info['last_name'])) {
    $errors[] = 'Last name is required';
}

if (empty($info['phone'])) {
    $errors[] = 'Phone number is required';
}

if ($mode === 'delivery') {
    if (empty($info['province'])) $errors[] = 'Province is required';
    if (empty($info['city'])) $errors[] = 'City is required';
    if (empty($info['barangay'])) $errors[] = 'Barangay is required';
    if (empty($info['street'])) $errors[] = 'Street address is required';
}

if (!empty($errors)) {
    error_log('Order create validation failed: ' . implode(', ', $errors));
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed: ' . implode(', ', $errors),
        'errors' => $errors
    ]);
    exit;
}

// Start database transaction
$pdo = db();
$pdo->beginTransaction();

try {
    // ==========================================
    // STEP 1: GET ACTUAL PRODUCT NAME & VALIDATE CUSTOMIZATIONS    
    // ==========================================
    $prodName = 'Selected Cabinet'; // Default fallback
    $basePrice = 0.00;
    $serverAddonsTotal = 0.00;
    $validatedCustomizations = [];

    if ($pid > 0) {
        $nameStmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = :pid LIMIT 1");
        $nameStmt->execute([':pid' => $pid]);
        $productRow = $nameStmt->fetch(PDO::FETCH_ASSOC);

        if ($productRow && !empty($productRow['name'])) {
            $prodName = $productRow['name'];
            $basePrice = (float)$productRow['price'];
        } else {
            // Product not found
            throw new Exception('Product not found');
        }
    }

    // Validate customizations server-side
    if ($isCustomized && !empty($selectedCustomizations)) {
        foreach ($selectedCustomizations as $custom) {
            $type = $custom['type'] ?? '';
            $id = (int)($custom['id'] ?? 0);
            $label = $custom['label'] ?? '';
            $applies_to = $custom['applies_to'] ?? '';
            $clientPrice = (float)($custom['price'] ?? 0);
            $meta = $custom['meta'] ?? null;

            $serverPrice = 0.00;

            // Validate each customization type
            switch ($type) {
                case 'texture':
                    if ($id > 0) {
                        $stmt = $pdo->prepare("SELECT texture_name, base_price FROM textures WHERE id = ? AND is_active = 1 LIMIT 1");
                        $stmt->execute([$id]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row) {
                            $serverPrice = (float)$row['base_price'];
                            $label = $row['texture_name'];
                        }
                    }
                    break;

                case 'color':
                    if ($id > 0) {
                        $stmt = $pdo->prepare("SELECT color_name, base_price FROM colors WHERE id = ? AND is_active = 1 LIMIT 1");
                        $stmt->execute([$id]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row) {
                            $serverPrice = (float)$row['base_price'];
                            $label = $row['color_name'];
                        }
                    }
                    break;

                case 'handle':
                    if ($id > 0) {
                        $stmt = $pdo->prepare("SELECT handle_name, base_price FROM handle_types WHERE id = ? AND is_active = 1 LIMIT 1");
                        $stmt->execute([$id]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row) {
                            $serverPrice = (float)$row['base_price'];
                            $label = $row['handle_name'];
                        }
                    }
                    break;

                case 'size':
                    // Accept size adjustments from client
                    $serverPrice = $clientPrice;
                    break;
            }

            // Store validated customization
            $validatedCustomizations[] = [
                'type' => $type,
                'id' => $id,
                'code' => $custom['code'] ?? '',
                'label' => $label,
                'applies_to' => $applies_to,
                'price' => round($serverPrice, 2),
                'meta' => $meta
            ];

            $serverAddonsTotal += $serverPrice;
        }
    }

    // Server-computed totals
    $serverAddonsTotal = round($serverAddonsTotal, 2);
    $serverBaseTotal = round($basePrice * $qty, 2);
    $serverGrandTotal = round(($basePrice + $serverAddonsTotal) * $qty, 2);

    // Use server values (reject client manipulation)
    $subtotal = $serverGrandTotal / 1.12; // Remove VAT to get subtotal
    $vat = $serverGrandTotal - $subtotal;
    $total = $serverGrandTotal;

    // ==========================================
    // STEP 2: CREATE ORDER (with customization fields)
    // ==========================================

    $customizationsJson = !empty($validatedCustomizations) ? json_encode($validatedCustomizations) : null;

    $stmt = $pdo->prepare("INSERT INTO orders
        (order_code, customer_id, mode, status, payment_status, subtotal, vat, total_amount,
         addons_total, base_total, grand_total, customizations, is_customized, order_date)
        VALUES (
            CONCAT('RT', DATE_FORMAT(NOW(),'%y%m%d'), LPAD(FLOOR(RAND()*9999), 4, '0')),
            :cid, :mode, 'Pending', 'Pending', :sub, :vat, :tot,
            :addons, :base, :grand, :customs, :is_custom, NOW()
        )");

    $stmt->execute([
        ':cid'  => $uid,
        ':mode' => $mode,
        ':sub'  => $subtotal,
        ':vat'  => $vat,
        ':tot'  => $total,
        ':addons' => $serverAddonsTotal,
        ':base' => $serverBaseTotal,
        ':grand' => $serverGrandTotal,
        ':customs' => $customizationsJson,
        ':is_custom' => $isCustomized ? 1 : 0
    ]);

    $order_id = (int)$pdo->lastInsertId();

    if (!$order_id) {
        throw new Exception('Failed to create order');
    }

    // Fetch the generated order_code
    $stmt = $pdo->prepare("SELECT order_code FROM orders WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $order_id]);
    $order_code = (string)($stmt->fetchColumn() ?: '');

    // ==========================================
    // STEP 3: INSERT ORDER ITEM WITH CUSTOMIZATIONS
    // ==========================================
    $unitPrice = ($basePrice + $serverAddonsTotal);
    $lineTotal = $unitPrice * $qty;

    $stmt = $pdo->prepare("INSERT INTO order_items
        (order_id, product_id, name, unit_price, qty, line_total, item_customizations, addons_price, base_price)
        VALUES (:oid, :pid, :name, :price, :qty, :lt, :customs, :addons, :base)");

    $stmt->execute([
        ':oid' => $order_id,
        ':pid' => $pid,
        ':name' => $prodName,
        ':price' => $unitPrice,
        ':qty' => $qty,
        ':lt' => $lineTotal,
        ':customs' => $customizationsJson,
        ':addons' => $serverAddonsTotal,
        ':base' => $basePrice
    ]);

    // ==========================================
    // STEP 4: SAVE ADDRESS/CONTACT SNAPSHOT
    // ==========================================
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

    error_log('Order created successfully: #' . $order_id . ' (' . $order_code . ')');

    echo json_encode([
        'success'    => true,
        'message'    => 'Order created successfully!',
        'order_id'   => $order_id,
        'order_code' => $order_code
    ]);
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Order creation error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order: ' . $e->getMessage()
    ]);
}
