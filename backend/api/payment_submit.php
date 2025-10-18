<?php
declare(strict_types=1);
session_start();

// Set JSON header
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

// Check if order details exist in session
if (!isset($_SESSION['current_order'])) {
    echo json_encode(['success' => false, 'message' => 'Order session expired. Please restart checkout.']);
    exit;
}

// Validate required fields
$required_fields = ['account_name', 'account_number', 'reference_number', 'amount_paid'];
$errors = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $errors[] = "Field '$field' is required";
    }
}

// Validate file upload
if (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Payment screenshot is required';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Sanitize input data
$account_name = filter_var(trim($_POST['account_name']), FILTER_SANITIZE_STRING);
$account_number = filter_var(trim($_POST['account_number']), FILTER_SANITIZE_STRING);
$reference_number = filter_var(trim($_POST['reference_number']), FILTER_SANITIZE_STRING);
$amount_paid = filter_var($_POST['amount_paid'], FILTER_VALIDATE_FLOAT);

if ($amount_paid === false || $amount_paid <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit;
}

// Handle file upload
$upload_dir = '../uploads/payments/';
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
$max_size = 5 * 1024 * 1024; // 5MB

// Create directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file = $_FILES['payment_screenshot'];
$file_type = mime_content_type($file['tmp_name']);
$file_size = $file['size'];

// Validate file type
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG and PNG allowed.']);
    exit;
}

// Validate file size
if ($file_size > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
    exit;
}

// Generate unique filename
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = uniqid('payment_') . '_' . time() . '.' . $file_extension;
$file_path = $upload_dir . $new_filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    exit;
}

// Database connection
require_once '../config/database.php';

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get order details from session
    $order = $_SESSION['current_order'];
    
    // Insert order into database
    $sql_order = "INSERT INTO orders (
        customer_id, 
        product_id, 
        product_name,
        quantity, 
        unit_price,
        subtotal,
        vat,
        shipping_fee,
        total_amount,
        order_type,
        order_status,
        payment_status,
        payment_method,
        created_at
    ) VALUES (
        :customer_id,
        :product_id,
        :product_name,
        :quantity,
        :unit_price,
        :subtotal,
        :vat,
        :shipping,
        :total,
        :order_type,
        'pending',
        'verification_pending',
        :payment_method,
        NOW()
    )";
    
    $stmt_order = $pdo->prepare($sql_order);
    $stmt_order->execute([
        ':customer_id' => $_SESSION['customer_id'],
        ':product_id' => $order['pid'],
        ':product_name' => $order['product_name'],
        ':quantity' => $order['qty'],
        ':unit_price' => $order['price'],
        ':subtotal' => $order['subtotal'],
        ':vat' => $order['vat'],
        ':shipping' => $order['shipping'] ?? 0,
        ':total' => $order['total'],
        ':order_type' => $order['type'],
        ':payment_method' => $order['payment_method'] ?? 'GCash'
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Insert shipping address if delivery
    if ($order['type'] === 'delivery') {
        $sql_address = "INSERT INTO order_addresses (
            order_id,
            first_name,
            last_name,
            email,
            phone,
            province,
            city,
            barangay,
            postal_code,
            house_no,
            landmark,
            created_at
        ) VALUES (
            :order_id,
            :first_name,
            :last_name,
            :email,
            :phone,
            :province,
            :city,
            :barangay,
            :postal,
            :house_no,
            :landmark,
            NOW()
        )";
        
        $stmt_address = $pdo->prepare($sql_address);
        $stmt_address->execute([
            ':order_id' => $order_id,
            ':first_name' => $order['customer']['first_name'],
            ':last_name' => $order['customer']['last_name'],
            ':email' => $order['customer']['email'],
            ':phone' => $order['customer']['phone'],
            ':province' => $order['address']['province'] ?? '',
            ':city' => $order['address']['city'] ?? '',
            ':barangay' => $order['address']['barangay'] ?? '',
            ':postal' => $order['address']['postal'] ?? '',
            ':house_no' => $order['address']['house_no'] ?? '',
            ':landmark' => $order['address']['landmark'] ?? ''
        ]);
    } else {
        // Insert pickup details
        $sql_pickup = "INSERT INTO order_pickups (
            order_id,
            first_name,
            last_name,
            email,
            phone,
            pickup_location,
            created_at
        ) VALUES (
            :order_id,
            :first_name,
            :last_name,
            :email,
            :phone,
            'RADS Tooling Main Branch',
            NOW()
        )";
        
        $stmt_pickup = $pdo->prepare($sql_pickup);
        $stmt_pickup->execute([
            ':order_id' => $order_id,
            ':first_name' => $order['customer']['first_name'],
            ':last_name' => $order['customer']['last_name'],
            ':email' => $order['customer']['email'],
            ':phone' => $order['customer']['phone']
        ]);
    }
    
    // Insert payment verification record
    $sql_payment = "INSERT INTO payment_verifications (
        order_id,
        account_name,
        account_number,
        reference_number,
        amount_paid,
        screenshot_path,
        verification_status,
        submitted_at
    ) VALUES (
        :order_id,
        :account_name,
        :account_number,
        :reference_number,
        :amount_paid,
        :screenshot_path,
        'pending',
        NOW()
    )";
    
    $stmt_payment = $pdo->prepare($sql_payment);
    $stmt_payment->execute([
        ':order_id' => $order_id,
        ':account_name' => $account_name,
        ':account_number' => $account_number,
        ':reference_number' => $reference_number,
        ':amount_paid' => $amount_paid,
        ':screenshot_path' => $new_filename
    ]);
    
    // Commit transaction
    $pdo->commit();
    
    // Clear order from session
    unset($_SESSION['current_order']);
    
    // Send email notification (optional)
    sendEmailNotification($order['customer']['email'], $order_id, $reference_number);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Payment verification submitted successfully',
        'order_id' => $order_id,
        'reference_number' => $reference_number
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    // Delete uploaded file if database insert failed
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    error_log('Payment submission error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while processing your payment. Please try again.'
    ]);
}

/**
 * Send email notification to customer
 */
function sendEmailNotification($email, $order_id, $reference_number) {
    // This is optional - implement if you have email configured
    try {
        $subject = "Payment Verification Received - Order #" . $order_id;
        $message = "
        <html>
        <head>
            <title>Payment Verification Received</title>
        </head>
        <body>
            <h2>Thank you for your payment!</h2>
            <p>We have received your payment verification for Order #$order_id</p>
            <p>Reference Number: <strong>$reference_number</strong></p>
            <p>Your payment is currently being verified. We will notify you once it's approved.</p>
            <p>You can check the status in your profile page.</p>
            <br>
            <p>Best regards,<br>RADS Tooling Team</p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: RADS Tooling <noreply@radstooling.com>' . "\r\n";
        
        mail($email, $subject, $message, $headers);
    } catch (Exception $e) {
        // Log email error but don't fail the transaction
        error_log('Email notification failed: ' . $e->getMessage());
    }
}
?>