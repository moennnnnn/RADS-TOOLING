<?php
// backend/api/payment_verification.php
// ✅ FIXED: Proper error handling, removed missing dependencies
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

// ✅ FIXED: Remove missing dependencies, inline require only what exists
require_once dirname(__DIR__) . '/config/database.php';

// ✅ FIXED: Simplified auth check
function guard_require_staff() {
    if (!empty($_SESSION['staff'])) return;
    if (!empty($_SESSION['user']) && ($_SESSION['user']['aud'] ?? null) === 'staff') return;
    if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) return;
    
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

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

function listPaymentVerifications($conn)
{
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

function getPaymentDetails($conn)
{
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

// ✅ NEW: Helper function to recalculate order payment status
function recalc_order_payment($conn, int $orderId): void
{
    try {
        // Get payment info
        $stmt = $conn->prepare("
            SELECT total_amount 
            FROM orders 
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) return;
        
        $totalAmount = (float)$order['total_amount'];
        
        // Get total paid amount
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(amount_paid), 0) as total_paid
            FROM payment_installments
            WHERE order_id = ? AND status = 'PAID'
        ");
        $stmt->execute([$orderId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPaid = (float)($result['total_paid'] ?? 0);
        
        // Determine payment status
        $paymentStatus = 'Unpaid';
        if ($totalPaid >= $totalAmount) {
            $paymentStatus = 'Fully Paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'Partially Paid';
        }
        
        // Update order
        $stmt = $conn->prepare("
            UPDATE orders 
            SET payment_status = ? 
            WHERE id = ?
        ");
        $stmt->execute([$paymentStatus, $orderId]);
        
    } catch (PDOException $e) {
        error_log("Recalc order payment error: " . $e->getMessage());
    }
}

function approvePayment($conn)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $verification_id = filter_var($input['id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$verification_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid verification ID']);
        return;
    }

    try {
        $conn->beginTransaction();

        // 1) Get verification details
        $stmt = $conn->prepare("SELECT order_id, amount_reported, status FROM payment_verifications WHERE id = ?");
        $stmt->execute([$verification_id]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$verification) {
            $conn->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Verification not found']);
            return;
        }
        
        // ✅ FIXED: Check if already approved
        if ($verification['status'] === 'APPROVED') {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Payment already approved']);
            return;
        }
        
        $orderId = (int)$verification['order_id'];
        $amountReported = (float)($verification['amount_reported'] ?? 0);

        // 2) Mark verification APPROVED
        // ✅ ULTRA FIXED: Better session ID extraction with all possible formats
        $approverId = 1; // Default fallback
        
        if (!empty($_SESSION['staff']['id'])) {
            $approverId = (int)$_SESSION['staff']['id'];
        } elseif (!empty($_SESSION['user']['id'])) {
            $approverId = (int)$_SESSION['user']['id'];
        } elseif (!empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id'])) {
            $approverId = (int)$_SESSION['admin_id'];
        } else {
            // Try to get any owner/admin from database as fallback
            try {
                $userStmt = $conn->prepare("SELECT id FROM admin_users WHERE role = 'Owner' LIMIT 1");
                $userStmt->execute();
                $adminUser = $userStmt->fetch(PDO::FETCH_ASSOC);
                if ($adminUser) {
                    $approverId = (int)$adminUser['id'];
                }
            } catch (Exception $e) {
                error_log("Could not get approver ID: " . $e->getMessage());
                // Keep default of 1
            }
        }
        
        $stmt = $conn->prepare("
            UPDATE payment_verifications
            SET status = 'APPROVED',
                approved_by = :uid,
                approved_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':uid' => $approverId, ':id' => $verification_id]);

        // 3) Add to payments.amount_paid and mark VERIFIED
        $stmt = $conn->prepare("
            UPDATE payments
            SET amount_paid = COALESCE(amount_paid, 0) + :amt,
                status = 'VERIFIED',
                verified_by = :uid,
                verified_at = NOW()
            WHERE order_id = :oid
        ");
        $stmt->execute([
            ':amt' => $amountReported,
            ':uid' => $approverId,
            ':oid' => $orderId
        ]);

        // 4) Mark next open installment as PAID
        $stmt = $conn->prepare("
            SELECT id
            FROM payment_installments
            WHERE order_id = :oid 
            AND UPPER(status) IN ('PENDING','UNPAID')
            ORDER BY installment_number ASC
            LIMIT 1
        ");
        $stmt->execute([':oid' => $orderId]);
        $nextId = $stmt->fetchColumn();

        if ($nextId) {
            $stmt = $conn->prepare("
                UPDATE payment_installments
                SET status = 'PAID', 
                    amount_paid = amount_due, 
                    verified_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([':id' => $nextId]);
        }

        // 5) Update order status to Processing
        $stmt = $conn->prepare("UPDATE orders SET status = 'Processing' WHERE id = :oid");
        $stmt->execute([':oid' => $orderId]);

        // 6) Recalculate order payment status
        recalc_order_payment($conn, $orderId);

        // 7) Commit
        $conn->commit();

        echo json_encode([
            'success' => true,
            'data' => [
                'order_id' => $orderId,
                'message' => 'Payment approved successfully'
            ]
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Approve payment error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to approve payment: ' . $e->getMessage()]);
    }
}

function rejectPayment($conn)
{
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
        $stmt = $conn->prepare("SELECT order_id, amount_reported, status FROM payment_verifications WHERE id = ?");
        $stmt->execute([$verification_id]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verification) {
            $conn->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Verification not found']);
            return;
        }
        
        // ✅ FIXED: Check if already rejected
        if ($verification['status'] === 'REJECTED') {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Payment already rejected']);
            return;
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

        // Recalculate payment status
        recalc_order_payment($conn, (int)$verification['order_id']);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Payment rejected successfully'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Reject payment error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to reject payment: ' . $e->getMessage()]);
    }
}