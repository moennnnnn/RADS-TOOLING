<?php
// backend/api/admin_orders.php - Order management API
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../includes/guard.php';

class AdminOrdersAPI {
    private PDO $conn;
    private array $currentUser;
    private string $currentRole;

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Throwable $e) {
            $this->send(false, 'Database connection failed', null, 500);
        }

        // Check if user is authenticated staff
        if (empty($_SESSION['staff'])) {
            $this->send(false, 'Unauthorized access', null, 401);
        }

        $this->currentUser = $_SESSION['staff'];
        $this->currentRole = $this->currentUser['role'];
    }

    public function handleRequest(): void {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'list':
                $this->listOrders();
                break;
            case 'view':
                $this->viewOrder();
                break;
            case 'details':
                $this->getOrderDetails();
                break;
            case 'update_status':
                $this->updateOrderStatus();
                break;
            case 'update_payment':
                $this->updatePaymentStatus();
                break;
            default:
                $this->send(false, 'Invalid action');
        }
    }

    private function listOrders(): void {
        try {
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $paymentStatus = $_GET['payment_status'] ?? '';
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);

            // Build WHERE clause
            $whereClauses = [];
            $params = [];

            if (!empty($search)) {
                $whereClauses[] = '(o.order_code LIKE ? OR c.full_name LIKE ?)';
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($status)) {
                $whereClauses[] = 'o.status = ?';
                $params[] = $status;
            }

            if (!empty($paymentStatus)) {
                if ($paymentStatus === 'Fully Paid') {
                    $whereClauses[] = "o.payment_status = 'Fully Paid'";
                } elseif ($paymentStatus === 'With Balance') {
                    $whereClauses[] = "o.payment_status IN ('Pending', 'Partially Paid')";
                }
            }

            $whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            $sql = "SELECT 
                        o.id,
                        o.order_code,
                        o.total_amount,
                        o.payment_status,
                        o.status,
                        o.order_date,
                        o.mode,
                        c.full_name as customer_name,
                        c.email as customer_email,
                        GROUP_CONCAT(DISTINCT oi.name SEPARATOR ', ') as product_name
                    FROM orders o
                    LEFT JOIN customers c ON o.customer_id = c.id
                    LEFT JOIN order_items oi ON oi.order_id = o.id
                    $whereClause
                    GROUP BY o.id
                    ORDER BY o.order_date DESC
                    LIMIT ? OFFSET ?";

            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination
            $countSql = "SELECT COUNT(DISTINCT o.id) as total 
                        FROM orders o
                        LEFT JOIN customers c ON o.customer_id = c.id
                        LEFT JOIN order_items oi ON oi.order_id = o.id
                        $whereClause";
            
            $countParams = array_slice($params, 0, -2); // Remove limit and offset
            $countStmt = $this->conn->prepare($countSql);
            $countStmt->execute($countParams);
            $total = $countStmt->fetchColumn();

            $this->send(true, 'Orders retrieved successfully', [
                'orders' => $orders,
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset,
                'current_user_role' => $this->currentRole
            ]);

        } catch (Throwable $e) {
            error_log("List orders error: " . $e->getMessage());
            $this->send(false, 'Failed to retrieve orders');
        }
    }

    private function viewOrder(): void {
        $orderId = $_GET['id'] ?? '';
        
        if (empty($orderId)) {
            $this->send(false, 'Order ID is required');
            return;
        }

        try {
            $sql = "SELECT 
                        o.*,
                        c.full_name as customer_name,
                        c.email as customer_email,
                        c.phone as customer_phone,
                        c.address as customer_address
                    FROM orders o
                    LEFT JOIN customers c ON o.customer_id = c.id
                    WHERE o.id = ?
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->send(false, 'Order not found');
                return;
            }

            // Get order items
            $itemsSql = "SELECT * FROM order_items WHERE order_id = ?";
            $itemsStmt = $this->conn->prepare($itemsSql);
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            $order['items'] = $items;

            $this->send(true, 'Order details retrieved successfully', $order);

        } catch (Throwable $e) {
            error_log("View order error: " . $e->getMessage());
            $this->send(false, 'Failed to retrieve order details');
        }
    }

    private function getOrderDetails(): void {
        $orderId = $_GET['id'] ?? '';
        
        if (empty($orderId)) {
            $this->send(false, 'Order ID is required');
            return;
        }

        try {
            // Get order details with payment info
            $sql = "SELECT 
                        o.*,
                        c.full_name as customer_name,
                        c.email as customer_email,
                        c.phone as customer_phone,
                        p.amount_paid,
                        p.payment_method,
                        p.deposit_rate,
                        p.amount_due,
                        p.status as payment_status_detail,
                        pv.status as payment_verification_status
                    FROM orders o
                    JOIN customers c ON o.customer_id = c.id
                    LEFT JOIN payments p ON p.order_id = o.id
                    LEFT JOIN payment_verifications pv ON pv.order_id = o.id
                    WHERE o.id = ?
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->send(false, 'Order not found');
                return;
            }

            // Get order items
            $itemsSql = "SELECT * FROM order_items WHERE order_id = ?";
            $itemsStmt = $this->conn->prepare($itemsSql);
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            $this->send(true, 'Order details retrieved successfully', [
                'order' => $order,
                'items' => $items
            ]);

        } catch (Throwable $e) {
            error_log("Get order details error: " . $e->getMessage());
            $this->send(false, 'Failed to retrieve order details');
        }
    }

    private function updateOrderStatus(): void {
        // Only Owner and Admin can update order status
        if (!in_array($this->currentRole, ['Owner', 'Admin'])) {
            $this->send(false, 'You do not have permission to update order status', null, 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed', null, 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['order_id']) || empty($input['status'])) {
            $this->send(false, 'Order ID and status are required', null, 400);
            return;
        }

        $orderId = (int)$input['order_id'];
        $newStatus = trim((string)$input['status']);

        // Validate status
        $validStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->send(false, 'Invalid status value', null, 400);
            return;
        }

        try {
            // Check if order exists
            $stmt = $this->conn->prepare('SELECT id, status FROM orders WHERE id = ? LIMIT 1');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->send(false, 'Order not found', null, 404);
                return;
            }

            // Update order status
            $stmt = $this->conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $orderId]);

            $this->send(true, 'Order status updated successfully', [
                'order_id' => $orderId,
                'old_status' => $order['status'],
                'new_status' => $newStatus
            ]);

        } catch (Throwable $e) {
            error_log("Update order status error: " . $e->getMessage());
            $this->send(false, 'Failed to update order status', null, 500);
        }
    }

    private function updatePaymentStatus(): void {
        // Only Owner and Admin can update payment status
        if (!in_array($this->currentRole, ['Owner', 'Admin'])) {
            $this->send(false, 'You do not have permission to update payment status', null, 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed', null, 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id']) || empty($input['payment_status'])) {
            $this->send(false, 'Order ID and payment status are required', null, 400);
            return;
        }

        $orderId = (int)$input['id'];
        $newPaymentStatus = trim((string)$input['payment_status']);

        // Validate payment status
        $validPaymentStatuses = ['Fully Paid', 'Partially Paid', 'Pending'];
        if (!in_array($newPaymentStatus, $validPaymentStatuses)) {
            $this->send(false, 'Invalid payment status value', null, 400);
            return;
        }

        try {
            // Check if order exists
            $stmt = $this->conn->prepare('SELECT id, payment_status FROM orders WHERE id = ? LIMIT 1');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->send(false, 'Order not found', null, 404);
                return;
            }

            // Update payment status
            $stmt = $this->conn->prepare('UPDATE orders SET payment_status = ? WHERE id = ?');
            $stmt->execute([$newPaymentStatus, $orderId]);

            $this->send(true, 'Payment status updated successfully', [
                'order_id' => $orderId,
                'old_payment_status' => $order['payment_status'],
                'new_payment_status' => $newPaymentStatus
            ]);

        } catch (Throwable $e) {
            error_log("Update payment status error: " . $e->getMessage());
            $this->send(false, 'Failed to update payment status', null, 500);
        }
    }

    private function send(bool $success, string $message, array|object|null $data = null, ?int $code = null): void {
        if ($code !== null) {
            http_response_code($code);
        }

        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$api = new AdminOrdersAPI();
$api->handleRequest();