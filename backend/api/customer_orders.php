<?php
// backend/api/customer_orders.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

require_once dirname(__DIR__, 2) . '/includes/guard.php';
require_once dirname(__DIR__) . '/config/database.php';

// Check customer authentication
if (empty($_SESSION['user']) || (($_SESSION['user']['aud'] ?? '') !== 'customer')) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Login required',
        'redirect' => '/RADS-TOOLING/customer/login.php'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$customer_id = (int) $_SESSION['user']['id'];

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        listOrders($conn, $customer_id);
        break;
    case 'details':
        getOrderDetails($conn, $customer_id);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function listOrders($conn, $customer_id) {
    $status_filter = $_GET['status'] ?? 'all';
    
    try {
        $sql = "
            SELECT 
                o.id,
                o.order_code,
                o.total_amount,
                o.payment_status,
                o.status,
                o.order_date,
                o.mode,
                p.amount_paid,
                p.deposit_rate,
                p.amount_due,
                p.status as payment_verification_status,
                pv.status as payment_proof_status,
                GROUP_CONCAT(DISTINCT oi.name SEPARATOR ', ') as items,
                GROUP_CONCAT(DISTINCT oi.qty SEPARATOR ', ') as quantities
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            LEFT JOIN payment_verifications pv ON pv.order_id = o.id
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE o.customer_id = ?
        ";

        if ($status_filter !== 'all') {
            switch ($status_filter) {
                case 'pending':
                    $sql .= " AND (o.status = 'Pending' OR pv.status = 'PENDING')";
                    break;
                case 'processing':
                    $sql .= " AND o.status = 'Processing'";
                    break;
                case 'completed':
                    $sql .= " AND o.status = 'Completed'";
                    break;
                case 'cancelled':
                    $sql .= " AND o.status = 'Cancelled'";
                    break;
            }
        }

        $sql .= " GROUP BY o.id ORDER BY o.order_date DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$customer_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $orders
        ]);
    } catch (PDOException $e) {
        error_log("List customer orders error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to load orders']);
    }
}

function getOrderDetails($conn, $customer_id) {
    $order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$order_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        return;
    }

    try {
        $stmt = $conn->prepare("
            SELECT 
                o.*,
                p.amount_paid,
                p.payment_method,
                p.deposit_rate,
                p.amount_due,
                p.status as payment_verification_status,
                pv.status as payment_proof_status,
                pv.account_name,
                pv.reference_number,
                pv.created_at as payment_submitted_at,
                oa.first_name,
                oa.last_name,
                oa.phone,
                oa.province,
                oa.city,
                oa.barangay,
                oa.street,
                oa.postal
            FROM orders o
            LEFT JOIN payments p ON p.order_id = o.id
            LEFT JOIN payment_verifications pv ON pv.order_id = o.id
            LEFT JOIN order_addresses oa ON oa.order_id = o.id
            WHERE o.id = ? AND o.customer_id = ?
        ");
        $stmt->execute([$order_id, $customer_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            return;
        }

        // Get order items
        $stmt = $conn->prepare("
            SELECT name, qty, unit_price, line_total
            FROM order_items
            WHERE order_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'order' => $order,
                'items' => $items
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Get order details error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to load order details']);
    }
}