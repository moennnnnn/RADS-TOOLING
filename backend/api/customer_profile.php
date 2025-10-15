<?php
// backend/api/customer_profile.php
require_once dirname(__DIR__, 2) . '/includes/guard.php';
require_once dirname(__DIR__, 2) . '/config/database.php';

header('Content-Type: application/json');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (empty($_SESSION['user']) || ($_SESSION['user']['aud'] ?? null) !== 'customer') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'code' => 'AUTH',
        'message' => 'Login required',
        'redirect' => '/RADS-TOOLING/customer/login.php'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$customer_id = $_SESSION['user']['id'];

// ... rest of the code continues

// GET - Fetch profile data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT id, username, full_name, email, phone, address, profile_image, created_at
            FROM customers 
            WHERE id = ?
        ");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch();

        if (!$customer) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
            exit;
        }

        // Get order statistics
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders
            FROM orders 
            WHERE customer_id = ?
        ");
        $stmt->execute([$customer_id]);
        $order_stats = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'stats' => $order_stats
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Fetch customer error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to load profile']);
    }
    exit;
}

// POST - Update profile information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate CSRF token
    if (!isset($input['csrf_token']) || $input['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }

    $full_name = trim($input['full_name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $address = trim($input['address'] ?? '');

    // Validation
    if (empty($full_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Full name is required']);
        exit;
    }

    if (strlen($full_name) < 3) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Full name must be at least 3 characters']);
        exit;
    }

    if (!empty($phone) && !preg_match('/^\+?[0-9\s\-()]{10,20}$/', $phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            UPDATE customers 
            SET full_name = ?, phone = ?, address = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$full_name, $phone, $address, $customer_id]);

        // Update session
        $_SESSION['user']['full_name'] = $full_name;

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'full_name' => $full_name,
                'phone' => $phone,
                'address' => $address
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Profile update error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
