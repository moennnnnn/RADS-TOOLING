<?php
// /backend/api/get_product_handles.php
declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if (!$productId) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID is required'
    ]);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get handles assigned to this product
    $sql = "SELECT h.id, h.handle_name, h.handle_code, h.handle_image, h.base_price, h.description
            FROM handle_types h
            INNER JOIN product_handles ph ON ph.handle_id = h.id
            WHERE ph.product_id = :product_id AND h.is_active = 1
            ORDER BY ph.display_order ASC, h.handle_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':product_id' => $productId]);
    $handles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format handles with proper image paths
    $formattedHandles = [];
    foreach ($handles as $handle) {
        $imagePath = '';
        if (!empty($handle['handle_image'])) {
            // If path starts with uploads/, use as-is
            if (strpos($handle['handle_image'], 'uploads/') === 0) {
                $imagePath = '/' . $handle['handle_image'];
            } else {
                // Otherwise, assume it's in uploads/handles/
                $imagePath = '/uploads/handles/' . ltrim($handle['handle_image'], '/');
            }
        }

        $formattedHandles[] = [
            'id' => (int)$handle['id'],
            'name' => $handle['handle_name'],
            'handle_name' => $handle['handle_name'],
            'handle_code' => $handle['handle_code'],
            'preview' => $imagePath,  // This is what customize.js expects
            'handle_image' => $imagePath,
            'base_price' => (float)$handle['base_price'],
            'description' => $handle['description']
        ];
    }

    echo json_encode([
        'success' => true,
        'handles' => $formattedHandles,
        'count' => count($formattedHandles)
    ]);
} catch (PDOException $e) {
    error_log("Get product handles error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Throwable $e) {
    error_log("Get product handles error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
