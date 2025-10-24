<?php
// backend/api/feedback/release.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

try {
    if (empty($_SESSION['user']) || ($_SESSION['user']['aud'] ?? '') !== 'admin') {
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Unauthorized']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
    $id   = (int)($data['id'] ?? 0);

    if ($id <= 0) throw new Exception('Invalid id');

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE feedback SET status='released' WHERE id=?");
    $stmt->execute([$id]);

    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
