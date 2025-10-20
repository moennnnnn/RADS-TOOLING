<?php
// ==========================================
// CUSTOMER ORDERS API - Enhanced with Installments
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
    echo json_encode(['success' => false, 'message' => 'Unauthorized', 'redirect' => '/RADS-TOOLING/customer/login.php']);
    exit;
}

$customerId = (int)$_SESSION['user']['id'];
$action = $_GET['action'] ?? '';

$db = new Database();
$conn = $db->getConnection();

try {
    if ($action === 'list') {
        listCustomerOrders($conn, $customerId);
    } elseif ($action === 'details') {
        getOrderDetails($conn, $customerId);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Customer Orders API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

// ==========================================
// LIST CUSTOMER ORDERS
// ==========================================
function listCustomerOrders($conn, $customerId) {
    $status = $_GET['status'] ?? 'all';
    
    // Base query
    $sql = "
        SELECT 
            o.id,
            o.order_code,
            o.order_date,
            o.total_amount,
            o.subtotal,
            o.vat,
            o.payment_status,
            o.status,
            o.mode,
            o.is_installment,
            p.deposit_rate,
            p.method as payment_method,
            p.status as payment_verification_status,
            GROUP_CONCAT(DISTINCT oi.name SEPARATOR ', ') as items
        FROM orders o
        LEFT JOIN payments p ON p.order_id = o.id
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.customer_id = :customer_id
    ";
    
    // Add status filter
    if ($status !== 'all') {
        $sql .= " AND LOWER(o.status) = :status";
    }
    
    $sql .= " GROUP BY o.id ORDER BY o.order_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    
    if ($status !== 'all') {
        $statusLower = strtolower($status);
        $stmt->bindParam(':status', $statusLower, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Enhance each order with installment data
    foreach ($orders as &$order) {
        if ($order['is_installment'] == 1) {
            // Get installments
            $installmentStmt = $conn->prepare("
                SELECT 
                    id,
                    installment_number,
                    amount_due,
                    amount_paid,
                    status,
                    due_date,
                    verified_at
                FROM payment_installments
                WHERE order_id = :order_id
                ORDER BY installment_number ASC
            ");
            $installmentStmt->execute([':order_id' => $order['id']]);
            $installments = $installmentStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $order['installments'] = $installments;
            $order['has_unpaid_installment'] = in_array('PENDING', array_column($installments, 'status'));
        } else {
            $order['installments'] = [];
            $order['has_unpaid_installment'] = false;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $orders
    ]);
}

// ==========================================
// GET ORDER DETAILS
// ==========================================
function getOrderDetails($conn, $customerId) {
    $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$orderId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        return;
    }
    
    // Get order info
    $orderStmt = $conn->prepare("
        SELECT 
            o.*,
            p.deposit_rate,
            p.method as payment_method,
            p.status as payment_verification_status,
            oa.first_name,
            oa.last_name,
            oa.phone,
            oa.email,
            oa.province,
            oa.city,
            oa.barangay,
            oa.street,
            oa.postal
        FROM orders o
        LEFT JOIN payments p ON p.order_id = o.id
        LEFT JOIN order_addresses oa ON oa.order_id = o.id
        WHERE o.id = :order_id AND o.customer_id = :customer_id
    ");
    $orderStmt->execute([
        ':order_id' => $orderId,
        ':customer_id' => $customerId
    ]);
    
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        return;
    }
    
    // Get order items
    $itemsStmt = $conn->prepare("
        SELECT 
            name,
            qty,
            unit_price,
            line_total
        FROM order_items
        WHERE order_id = :order_id
    ");
    $itemsStmt->execute([':order_id' => $orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get installments if applicable
    $installments = [];
    if ($order['is_installment'] == 1) {
        $installmentStmt = $conn->prepare("
            SELECT 
                id,
                installment_number,
                amount_due,
                amount_paid,
                status,
                due_date,
                payment_method,
                reference_number,
                verified_at,
                notes
            FROM payment_installments
            WHERE order_id = :order_id
            ORDER BY installment_number ASC
        ");
        $installmentStmt->execute([':order_id' => $orderId]);
        $installments = $installmentStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'order' => $order,
            'items' => $items,
            'installments' => $installments
        ]
    ]);
}
?>