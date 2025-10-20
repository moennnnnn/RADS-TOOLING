<?php
// ==========================================
// INSTALLMENT PAYMENT SUBMISSION API
// ==========================================
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/includes/guard.php';
require_once dirname(__DIR__) . '/config/database.php';

// Authentication check
if (empty($_SESSION['user']) || ($_SESSION['user']['aud'] ?? '') !== 'customer') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$customerId = (int)$_SESSION['user']['id'];

$db = new Database();
$conn = $db->getConnection();

try {
    // Validate inputs
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $installmentId = filter_input(INPUT_POST, 'installment_id', FILTER_VALIDATE_INT);
    $method = strtolower(trim($_POST['method'] ?? ''));
    $accountName = trim($_POST['account_name'] ?? '');
    $accountNumber = trim($_POST['account_number'] ?? '');
    $referenceNumber = trim($_POST['reference_number'] ?? '');
    $amountPaid = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);
    
    // Validation
    if (!$orderId || !$installmentId) {
        throw new Exception('Invalid order or installment ID');
    }
    
    if (!in_array($method, ['gcash', 'bpi'])) {
        throw new Exception('Invalid payment method');
    }
    
    if (empty($accountName) || empty($referenceNumber)) {
        throw new Exception('Account name and reference number are required');
    }
    
    if (!$amountPaid || $amountPaid <= 0) {
        throw new Exception('Invalid amount paid');
    }
    
    // Verify order belongs to customer
    $verifyStmt = $conn->prepare("
        SELECT o.id, pi.amount_due, pi.status
        FROM orders o
        JOIN payment_installments pi ON pi.order_id = o.id
        WHERE o.id = :order_id 
        AND o.customer_id = :customer_id
        AND pi.id = :installment_id
    ");
    $verifyStmt->execute([
        ':order_id' => $orderId,
        ':customer_id' => $customerId,
        ':installment_id' => $installmentId
    ]);
    
    $verification = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$verification) {
        throw new Exception('Order or installment not found');
    }
    
    if ($verification['status'] === 'PAID') {
        throw new Exception('This installment has already been paid');
    }
    
    if ($amountPaid < $verification['amount_due']) {
        throw new Exception('Amount paid must be at least ₱' . number_format($verification['amount_due'], 2));
    }
    
    // Handle file upload
    $screenshotPath = null;
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 2) . '/uploads/installments/';
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF allowed');
        }
        
        // Check file size (5MB max)
        if ($_FILES['screenshot']['size'] > 5 * 1024 * 1024) {
            throw new Exception('File too large. Maximum 5MB allowed');
        }
        
        $fileName = 'installment_' . $installmentId . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($_FILES['screenshot']['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload screenshot');
        }
        
        $screenshotPath = 'uploads/installments/' . $fileName;
    } else {
        throw new Exception('Payment screenshot is required');
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Update installment record
    $updateStmt = $conn->prepare("
        UPDATE payment_installments
        SET 
            amount_paid = :amount_paid,
            payment_method = :method,
            reference_number = :reference_number,
            screenshot_path = :screenshot_path,
            status = 'PENDING',
            updated_at = NOW()
        WHERE id = :installment_id
    ");
    
    $updateStmt->execute([
        ':amount_paid' => $amountPaid,
        ':method' => $method,
        ':reference_number' => $referenceNumber,
        ':screenshot_path' => $screenshotPath,
        ':installment_id' => $installmentId
    ]);
    
    // Insert into payment_verifications for admin review
    $verificationStmt = $conn->prepare("
        INSERT INTO payment_verifications 
        (order_id, method, account_name, account_number, reference_number, amount_reported, screenshot_path, status)
        VALUES
        (:order_id, :method, :account_name, :account_number, :reference_number, :amount_paid, :screenshot_path, 'PENDING')
    ");
    
    $verificationStmt->execute([
        ':order_id' => $orderId,
        ':method' => $method,
        ':account_name' => $accountName,
        ':account_number' => $accountNumber,
        ':reference_number' => $referenceNumber,
        ':amount_paid' => $amountPaid,
        ':screenshot_path' => $screenshotPath
    ]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment submitted successfully! Wait for admin verification.'
    ]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Installment payment error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>