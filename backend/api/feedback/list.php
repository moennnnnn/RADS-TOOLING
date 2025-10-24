<?php
// backend/api/feedback/list.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

try {
    if (empty($_SESSION['user']) || ($_SESSION['user']['aud'] ?? '') !== 'admin') {
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Unauthorized']);
        exit;
    }

    $db = Database::getInstance()->getConnection();

    $sql = "
      SELECT f.id, f.rating, f.comment, f.status, f.created_at,
             o.id AS order_id, o.order_code,
             c.id AS customer_id, CONCAT(c.first_name,' ',c.last_name) AS customer_name
      FROM feedback f
      JOIN orders o    ON o.id = f.order_id
      JOIN customers c ON c.id = f.customer_id
      ORDER BY f.created_at DESC
    ";
    $rows = $db->query($sql)->fetchAll();

    echo json_encode(['success'=>true,'data'=>$rows]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
