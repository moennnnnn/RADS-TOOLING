<?php
// backend/api/testimonials.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = "
      SELECT f.rating, f.comment, f.created_at,
             CONCAT(c.first_name, ' ', c.last_name) AS customer_name
      FROM feedback f
      JOIN customers c ON c.id = f.customer_id
      WHERE f.status = 'released'
      ORDER BY f.created_at DESC
      LIMIT 100
    ";
    $rows = $db->query($sql)->fetchAll();
    echo json_encode(['success'=>true,'data'=>$rows]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
