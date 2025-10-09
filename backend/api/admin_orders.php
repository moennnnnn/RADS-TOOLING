<?php
// backend/api/admin_orders.php - Order management API
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
            $this->send(false, 'Database connection failed');
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
                $whereClauses[] = '(o.order_code LIKE ? OR c.full_name LIKE ? OR p.name LIKE ?)';
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($status)) {
                $whereClauses[] = 'o.status = ?';
                $params[] = $status;
            }

            if (!empty($paymentStatus)) {
                $whereClauses[] = 'o.payment_status = ?';
                $params[] = $paymentStatus;
            }

            $whereClause = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            $sql = "SELECT 
                        o.id,
                        o.order_code,
                        o.total_amount,
                        o.payment_status,
                        o.status,
                        o.order_date,
                        c.full_name as customer_name,
                        c.email as customer_email,
                        p.name as product_name
                    FROM orders o
                    LEFT JOIN customers c ON o.customer_id = c.id
                    LEFT JOIN products p ON o.product_id = p.id
                    $whereClause
                    ORDER BY o.order_date DESC
                    LIMIT ? OFFSET ?";

            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination
            $countSql = "SELECT COUNT(*) as total 
                        FROM orders o
                        LEFT JOIN customers c ON o.customer_id = c.id
                        LEFT JOIN products p ON o.product_id = p.id
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
                        c.address as customer_address,
                        p.name as product_name,
                        p.description as product_description,
                        p.price as product_price
                    FROM orders o
                    LEFT JOIN customers c ON o.customer_id = c.id
                    LEFT JOIN products p ON o.product_id = p.id
                    WHERE o.id = ?
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->send(false, 'Order not found');
                return;
            }

            $this->send(true, 'Order details retrieved successfully', $order);

        } catch (Throwable $e) {
            error_log("View order error: " . $e->getMessage());
            $this->send(false, 'Failed to retrieve order details');
        }
    }

    private function updateOrderStatus(): void {
        // Only Owner and Admin can update order status
        if (!in_array($this->currentRole, ['Owner', 'Admin'])) {
            $this->send(false, 'You do not have permission to update order status');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id']) || empty($input['status'])) {
            $this->send(false, 'Order ID and status are required');
            return;
        }

        $orderId = (int)$input['id'];
        $newStatus = trim((string)$input['status']);

        // Validate status
        $validStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->send(false, 'Invalid status value');
            return;
        }

        try {
            // Check if order exists
            $stmt = $this->conn->prepare('SELECT id, status FROM orders WHERE id = ? LIMIT 1');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->send(false, 'Order not found');
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
            $this->send(false, 'Failed to update order status');
        }
    }

    private function updatePaymentStatus(): void {
        // Only Owner and Admin can update payment status
        if (!in_array($this->currentRole, ['Owner', 'Admin'])) {
            $this->send(false, 'You do not have permission to update payment status');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id']) || empty($input['payment_status'])) {
            $this->send(false, 'Order ID and payment status are required');
            return;
        }

        $orderId = (int)$input['id'];
        $newPaymentStatus = trim((string)$input['payment_status']);

        // Validate payment status
        $validPaymentStatuses = ['Fully Paid', 'With Balance'];
        if (!in_array($newPaymentStatus, $validPaymentStatuses)) {
            $this->send(false, 'Invalid payment status value');
            return;
        }

        try {
            // Check if order exists
            $stmt = $this->conn->prepare('SELECT id, payment_status FROM orders WHERE id = ? LIMIT 1');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->send(false, 'Order not found');
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
            $this->send(false, 'Failed to update payment status');
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