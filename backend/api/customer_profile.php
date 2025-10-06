<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../includes/guard.php';

header('Content-Type: application/json');

guard_require_customer();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $db = Database::getInstance()->getConnection();

    if ($action === 'get_profile') {
        $customerId = $_SESSION['customer_id'];

        $stmt = $db->prepare("
            SELECT id, username, full_name, email, phone, address, 
                   profile_image, created_at
            FROM customers 
            WHERE id = ?
        ");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer) {
            echo json_encode([
                'success' => true,
                'data' => $customer
            ]);
        } else {
            throw new Exception('Customer not found');
        }
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
