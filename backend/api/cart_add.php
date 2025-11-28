<?php
// /backend/api/cart_add.php
// POST /api/cart/add - Add item to cart with customizations
// Validates customizations and returns server-computed prices

declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/_bootstrap.php';

// Require customer authentication
if (!isset($_SESSION['customer']) || empty($_SESSION['customer'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to cart'
    ]);
    exit;
}

$customer_id = (int)$_SESSION['customer']['id'];

// Read request body
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!$body) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request body'
    ]);
    exit;
}

// Extract request data
$product_id = (int)($body['product_id'] ?? 0);
$quantity = max(1, (int)($body['qty'] ?? 1));
$selectedCustomizations = $body['selectedCustomizations'] ?? [];
$isCustomized = !empty($selectedCustomizations);

// Validate product ID
if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

try {
    $pdo = db();

    // ==========================================
    // STEP 1: Validate product exists
    // ==========================================
    $stmt = $pdo->prepare("SELECT id, name, price, is_customizable FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }

    $basePrice = (float)$product['price'];
    $productName = $product['name'];

    // ==========================================
    // STEP 2: Validate and compute customizations
    // ==========================================
    $validatedCustomizations = [];
    $addonsTotal = 0.00;

    if ($isCustomized && !empty($selectedCustomizations)) {
        foreach ($selectedCustomizations as $custom) {
            $type = $custom['type'] ?? ''; // 'texture', 'color', 'handle', 'size'
            $id = (int)($custom['id'] ?? 0);
            $code = $custom['code'] ?? '';
            $label = $custom['label'] ?? '';
            $applies_to = $custom['applies_to'] ?? ''; // 'door', 'body', 'inside'
            $clientPrice = (float)($custom['price'] ?? 0);
            $meta = $custom['meta'] ?? null;

            // Server-side validation and price lookup
            $serverPrice = 0.00;

            switch ($type) {
                case 'texture':
                    if ($id > 0) {
                        $stmt = $pdo->prepare("
                            SELECT t.id, t.texture_name, t.base_price, t.is_active
                            FROM textures t
                            WHERE t.id = ? AND t.is_active = 1
                            LIMIT 1
                        ");
                        $stmt->execute([$id]);
                        $texture = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($texture) {
                            $serverPrice = (float)$texture['base_price'];
                            $label = $texture['texture_name'];
                        } else {
                            throw new Exception("Invalid or inactive texture: ID {$id}");
                        }
                    }
                    break;

                case 'color':
                    if ($id > 0) {
                        $stmt = $pdo->prepare("
                            SELECT c.id, c.color_name, c.base_price, c.is_active
                            FROM colors c
                            WHERE c.id = ? AND c.is_active = 1
                            LIMIT 1
                        ");
                        $stmt->execute([$id]);
                        $color = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($color) {
                            $serverPrice = (float)$color['base_price'];
                            $label = $color['color_name'];
                        } else {
                            throw new Exception("Invalid or inactive color: ID {$id}");
                        }
                    }
                    break;

                case 'handle':
                    if ($id > 0) {
                        $stmt = $pdo->prepare("
                            SELECT h.id, h.handle_name, h.base_price, h.is_active
                            FROM handle_types h
                            WHERE h.id = ? AND h.is_active = 1
                            LIMIT 1
                        ");
                        $stmt->execute([$id]);
                        $handle = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($handle) {
                            $serverPrice = (float)$handle['base_price'];
                            $label = $handle['handle_name'];
                        } else {
                            throw new Exception("Invalid or inactive handle: ID {$id}");
                        }
                    }
                    break;

                case 'size':
                    // Size adjustments are computed based on product size config
                    // For now, accept the meta field which contains size details
                    if ($meta && is_array($meta)) {
                        // Could validate size ranges here
                        $serverPrice = $clientPrice; // Accept client calculation for size
                    }
                    break;

                default:
                    // Unknown type - skip
                    continue 2;
            }

            // Round to 2 decimals
            $serverPrice = round($serverPrice, 2);

            // Add to validated list
            $validatedCustomizations[] = [
                'type' => $type,
                'id' => $id,
                'code' => $code,
                'label' => $label,
                'applies_to' => $applies_to,
                'price' => $serverPrice,
                'meta' => $meta
            ];

            $addonsTotal += $serverPrice;
        }
    }

    // ==========================================
    // STEP 3: Compute totals (server authoritative)
    // ==========================================
    $addonsTotal = round($addonsTotal, 2);
    $itemTotal = round($basePrice + $addonsTotal, 2);
    $grandTotal = round($itemTotal * $quantity, 2);

    // ==========================================
    // STEP 4: Prepare cart item data
    // ==========================================
    $cartItem = [
        'product_id' => $product_id,
        'product_name' => $productName,
        'quantity' => $quantity,
        'base_price' => $basePrice,
        'addons_total' => $addonsTotal,
        'item_total' => $itemTotal,
        'grand_total' => $grandTotal,
        'is_customized' => $isCustomized,
        'customizations' => $validatedCustomizations,
        'added_at' => date('Y-m-d H:i:s')
    ];

    // ==========================================
    // STEP 5: Return success response
    // ==========================================
    echo json_encode([
        'success' => true,
        'message' => 'Item validated and ready for cart',
        'data' => $cartItem,
        'computed' => [
            'base_price' => $basePrice,
            'addons_total' => $addonsTotal,
            'item_total' => $itemTotal,
            'grand_total' => $grandTotal
        ]
    ]);
} catch (Exception $e) {
    error_log('Cart add error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    error_log('Cart add error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error while processing cart'
    ]);
}
