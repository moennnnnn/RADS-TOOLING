<?php
// backend/api/feedback/create.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

try {
    if (empty($_SESSION['user']) || ($_SESSION['user']['aud'] ?? '') !== 'customer') {
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Unauthorized']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];

    $orderId = (int)($data['order_id'] ?? 0);
    $rating  = (int)($data['rating']   ?? 0);
    $comment = trim($data['comment']   ?? '');

    if ($orderId <= 0 || $rating < 1 || $rating > 5) {
        throw new Exception('Invalid payload');
    }

    $db  = Database::getInstance()->getConnection();

    // validate ownership ng order
    $stmt = $db->prepare("SELECT id, customer_id FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order || (int)$order['customer_id'] !== (int)$_SESSION['user']['id']) {
        throw new Exception('Order not found');
    }

    // upsert: iwas duplicate feedback per order
    $stmt = $db->prepare("SELECT id FROM feedback WHERE order_id = ? LIMIT 1");
    $stmt->execute([$orderId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $db->prepare("UPDATE feedback SET rating=?, comment=?, status='pending', created_at=NOW() WHERE id=?");
        $stmt->execute([$rating, $comment, $existing['id']]);
        $id = $existing['id'];
    } else {
        $stmt = $db->prepare("INSERT INTO feedback(order_id, customer_id, rating, comment) VALUES(?,?,?,?)");
        $stmt->execute([$orderId, $_SESSION['user']['id'], $rating, $comment]);
        $id = $db->lastInsertId();
    }

    echo json_encode(['success'=>true,'data'=>['id'=>$id]]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
