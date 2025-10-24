<?php
require_once __DIR__ . '/../../config/app.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            f.id,
            CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
            f.rating,
            f.comment,
            o.order_code,
            f.created_at,
            f.is_released
        FROM feedback f
        INNER JOIN customers c ON c.id = f.customer_id
        INNER JOIN orders o ON o.id = f.order_id
        ORDER BY f.created_at DESC
        LIMIT 200
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load feedback']);
}
