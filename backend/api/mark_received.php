<?php
// backend/api/mark_received.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/app.php';

try {
    // Require POST + JSON
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    $orderId = isset($data['order_id']) ? (int)$data['order_id'] : 0;

    if ($orderId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid order_id']);
        exit;
    }

    // ---- Identify logged-in customer (use whichever your app sets) ----
    // Try common session shapes:
    $customerId = 0;
    if (isset($_SESSION['customer']['id'])) {
        $customerId = (int) $_SESSION['customer']['id'];
    } elseif (isset($_SESSION['customer_id'])) {
        $customerId = (int) $_SESSION['customer_id'];
    } elseif (isset($_SESSION['user']['id'])) { // fallback
        $customerId = (int) $_SESSION['user']['id'];
    }

    if ($customerId <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    // ---- Load order (must belong to this customer) ----
    $stmt = $pdo->prepare("
        SELECT id, customer_id, status, payment_status,
               COALESCE(received_by_customer, 0) AS received_by_customer,
               customer_received_at, COALESCE(is_received, 0) AS is_received
        FROM orders
        WHERE id = :oid AND customer_id = :cid
        LIMIT 1
    ");
    $stmt->execute([':oid' => $orderId, ':cid' => $customerId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // ---- Business rules (match the front-end canMarkAsReceived) ----
    $status = strtolower((string)$order['status']);
    $pay    = strtolower((string)$order['payment_status']);
    $allowedStatuses = ['delivered','ready for pickup','ready_for_pickup','for pickup','for_pickup','completed'];

    if (!in_array($status, $allowedStatuses, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order is not in a receivable status']);
        exit;
    }
    if (!in_array($pay, ['fully paid','fully_paid'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order must be fully paid']);
        exit;
    }
    if ((int)$order['received_by_customer'] === 1 || (int)$order['is_received'] === 1 || !empty($order['customer_received_at'])) {
        echo json_encode(['success' => true, 'message' => 'Order already marked as received']);
        exit;
    }

    // ---- Update flags (columns are optional; update only those that exist) ----
    // We'll try to update common columns; ignore missing columns gracefully.
    // Build dynamic SQL depending on existing columns
    $cols = [];
    $params = [':oid' => $orderId, ':cid' => $customerId];

    // Check columns quickly
    $columns = [];
    $q = $pdo->query("SHOW COLUMNS FROM orders");
    foreach ($q as $row) $columns[strtolower($row['Field'])] = true;

    if (isset($columns['received_by_customer'])) $cols[] = "received_by_customer = 1";
    if (isset($columns['is_received']))          $cols[] = "is_received = 1";
    if (isset($columns['customer_received_at'])) $cols[] = "customer_received_at = NOW()";

    if (empty($cols)) {
        // Nothing to update — but we consider it success to avoid blocking UX
        echo json_encode(['success' => true, 'message' => 'No receivable columns to update (skipped)']);
        exit;
    }

    $sql = "UPDATE orders SET " . implode(', ', $cols) . " WHERE id = :oid AND customer_id = :cid";
    $upd = $pdo->prepare($sql);
    $upd->execute($params);

    echo json_encode(['success' => true, 'message' => 'Order marked as received']);

} catch (Throwable $e) {
    error_log('mark_received error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
