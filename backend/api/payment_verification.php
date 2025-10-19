<?php
// backend/api/payment_verification.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

require_once dirname(__DIR__, 2) . '/includes/guard.php';
require_once dirname(__DIR__) . '/config/database.php';

guard_require_staff();

$db = new Database();
$conn = $db->getConnection();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listPaymentVerifications($conn);
        break;
    case 'details':
        getPaymentDetails($conn);
        break;
    case 'approve':
        approvePayment($conn);
        break;
    case 'reject':
        rejectPayment($conn);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function listPaymentVerifications($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                pv.id,
                pv.order_id,
                pv.method,
                pv.account_name,
                pv.account_number,
                pv.reference_number,
                pv.amount_reported,
                pv.screenshot_path,
                pv.status,
                pv.created_at,
                o.order_code,
                c.full_name as customer_name,
                c.email as customer_email,
                o.total_amount,
                p.deposit_rate,
                p.amount_due
            FROM payment_verifications pv
            JOIN orders o ON pv.order_id = o.id
            JOIN customers c ON o.customer_id = c.id
            JOIN payments p ON p.order_id = o.id
            ORDER BY 
                CASE pv.status 
                    WHEN 'PENDING' THEN 1 
                    WHEN 'APPROVED' THEN 2 
                    WHEN 'REJECTED' THEN 3 
                END,
                pv.created_at DESC
        ");
        $stmt->execute();
        $verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $verifications
        ]);
    } catch (PDOException $e) {
        error_log("List payment verifications error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to load payment verifications']);
    }
}

function getPaymentDetails($conn) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid verification ID']);
        return;
    }

    try {
        $stmt = $conn->prepare("
            SELECT 
                pv.*,
                o.order_code,
                o.total_amount,
                o.order_date,
                o.mode as delivery_mode,
                c.full_name as customer_name,
                c.email as customer_email,
                c.phone as customer_phone,
                p.deposit_rate,
                p.amount_due,
                oa.first_name,
                oa.last_name,
                oa.province,
                oa.city,
                oa.barangay,
                oa.street,
                GROUP_CONCAT(DISTINCT oi.name SEPARATOR ', ') as items
            FROM payment_verifications pv
            JOIN orders o ON pv.order_id = o.id
            JOIN customers c ON o.customer_id = c.id
            JOIN payments p ON p.order_id = o.id
            LEFT JOIN order_addresses oa ON oa.order_id = o.id
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE pv.id = ?
            GROUP BY pv.id
        ");
        $stmt->execute([$id]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$details) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Verification not found']);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $details
        ]);
    } catch (PDOException $e) {
        error_log("Get payment details error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to load payment details']);
    }
}

function approvePayment($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $verification_id = filter_var($input['id'] ?? 0, FILTER_VALIDATE_INT);
    
    if (!$verification_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid verification ID']);
        return;
    }

    try {
        $conn->beginTransaction();

        // Get verification details
        $stmt = $conn->prepare("SELECT order_id FROM payment_verifications WHERE id = ?");
        $stmt->execute([$verification_id]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verification) {
            throw new Exception('Verification not found');
        }

        // Update verification status
        $stmt = $conn->prepare("
            UPDATE payment_verifications 
            SET status = 'APPROVED' 
            WHERE id = ?
        ");
        $stmt->execute([$verification_id]);

        // Update payment status
        $stmt = $conn->prepare("
            UPDATE payments 
            SET status = 'VERIFIED', 
                verified_by = ?, 
                verified_at = NOW() 
            WHERE order_id = ?
        ");
        $stmt->execute([$_SESSION['staff']['id'] ?? $_SESSION['user']['id'], $verification['order_id']]);

        // Update order payment status
        $stmt = $conn->prepare("
            UPDATE orders 
            SET payment_status = 'Partially Paid',
                status = 'Processing'
            WHERE id = ?
        ");
        $stmt->execute([$verification['order_id']]);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment approved successfully'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Approve payment error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to approve payment']);
    }
}

function rejectPayment($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $verification_id = filter_var($input['id'] ?? 0, FILTER_VALIDATE_INT);
    $reason = trim($input['reason'] ?? '');
    
    if (!$verification_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid verification ID']);
        return;
    }

    try {
        $conn->beginTransaction();

        // Get verification details
        $stmt = $conn->prepare("SELECT order_id FROM payment_verifications WHERE id = ?");
        $stmt->execute([$verification_id]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verification) {
            throw new Exception('Verification not found');
        }

        // Update verification status
        $stmt = $conn->prepare("
            UPDATE payment_verifications 
            SET status = 'REJECTED' 
            WHERE id = ?
        ");
        $stmt->execute([$verification_id]);

        // Update payment status
        $stmt = $conn->prepare("
            UPDATE payments 
            SET status = 'REJECTED' 
            WHERE order_id = ?
        ");
        $stmt->execute([$verification['order_id']]);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment rejected'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Reject payment error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to reject payment']);
    }
}