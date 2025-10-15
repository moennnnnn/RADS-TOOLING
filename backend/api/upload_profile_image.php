<?php
// backend/api/upload_profile_image.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
$max_size = 5 * 1024 * 1024; // 5MB

$file_type = $_FILES['profile_image']['type'];
$file_size = $_FILES['profile_image']['size'];

if (!in_array($file_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, and PNG files are allowed']);
    exit;
}

if ($file_size > $max_size) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
    exit;
}

$upload_dir = dirname(__DIR__, 2) . '/assets/uploads/avatars/';

// Create directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
$filename = 'customer_' . $customer_id . '_' . time() . '.' . $extension;
$upload_path = $upload_dir . $filename;

if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
    try {
        // Delete old profile image if exists
        $stmt = $conn->prepare("SELECT profile_image FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        $old_image = $stmt->fetchColumn();

        if ($old_image && file_exists(dirname(__DIR__, 2) . '/' . $old_image)) {
            unlink(dirname(__DIR__, 2) . '/' . $old_image);
        }

        // Update database
        $relative_path = 'assets/uploads/avatars/' . $filename;
        $stmt = $conn->prepare("UPDATE customers SET profile_image = ? WHERE id = ?");
        $stmt->execute([$relative_path, $customer_id]);

        // Update session
        $_SESSION['user']['profile_image'] = $relative_path;

        echo json_encode([
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'data' => [
                'profile_image' => $relative_path
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Profile image update error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile picture']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
}
