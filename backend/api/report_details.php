<?php
// backend/api/report_details.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . '/includes/guard.php';
require_once dirname(__DIR__) . '/config/database.php';

guard_require_staff();

$db = new Database();
$conn = $db->getConnection();

$type = $_GET['type'] ?? '';
$from_date = $_GET['from'] ?? date('Y-m-01');
$to_date = $_GET['to'] ?? date('Y-m-d');

try {
    $data = [];
    $columns = []; // Headers for the frontend table

    switch ($type) {
        case 'sales':
        case 'orders':
        case 'avg_order': // Same list as sales/orders usually
            $query = "
                SELECT order_code, full_name, order_date, total_amount, status 
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                WHERE order_date BETWEEN ? AND ?
            ";
            // Filter specific to Sales (exclude cancelled usually)
            if ($type === 'sales') {
                $query .= " AND status != 'Cancelled'";
            }
            $query .= " ORDER BY order_date DESC";

            $stmt = $conn->prepare($query);
            $stmt->execute([$from_date, $to_date]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = ['Order Code', 'Customer', 'Date', 'Amount', 'Status'];
            foreach ($rows as $row) {
                $data[] = [
                    $row['order_code'],
                    $row['full_name'],
                    date('M d, Y', strtotime($row['order_date'])),
                    '₱' . number_format((float)$row['total_amount'], 2),
                    $row['status']
                ];
            }
            break;

        case 'fully_paid':
            $stmt = $conn->prepare("
                SELECT order_code, full_name, order_date, total_amount 
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                WHERE order_date BETWEEN ? AND ? AND payment_status = 'Fully Paid'
                ORDER BY order_date DESC
            ");
            $stmt->execute([$from_date, $to_date]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = ['Order Code', 'Customer', 'Date', 'Amount'];
            foreach ($rows as $row) {
                $data[] = [
                    $row['order_code'],
                    $row['full_name'],
                    date('M d, Y', strtotime($row['order_date'])),
                    '₱' . number_format((float)$row['total_amount'], 2)
                ];
            }
            break;

        case 'cancelled':
        case 'pending':
            $status = ($type === 'cancelled') ? 'Cancelled' : 'Pending';
            $stmt = $conn->prepare("
                SELECT order_code, full_name, order_date, total_amount 
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                WHERE order_date BETWEEN ? AND ? AND status = ?
                ORDER BY order_date DESC
            ");
            $stmt->execute([$from_date, $to_date, $status]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = ['Order Code', 'Customer', 'Date', 'Amount'];
            foreach ($rows as $row) {
                $data[] = [
                    $row['order_code'],
                    $row['full_name'],
                    date('M d, Y', strtotime($row['order_date'])),
                    '₱' . number_format((float)$row['total_amount'], 2)
                ];
            }
            break;

        case 'new_customers':
            $stmt = $conn->prepare("
                SELECT full_name, email, phone, created_at 
                FROM customers 
                WHERE created_at BETWEEN ? AND ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$from_date, $to_date]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = ['Name', 'Email', 'Phone', 'Date Joined'];
            foreach ($rows as $row) {
                $data[] = [
                    $row['full_name'],
                    $row['email'],
                    $row['phone'],
                    date('M d, Y', strtotime($row['created_at']))
                ];
            }
            break;

        case 'feedbacks':
            $stmt = $conn->prepare("
                SELECT c.full_name, f.rating, f.comment, f.created_at 
                FROM feedback f
                JOIN customers c ON f.customer_id = c.id
                WHERE f.created_at BETWEEN ? AND ?
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([$from_date, $to_date]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = ['Customer', 'Rating', 'Comment', 'Date'];
            foreach ($rows as $row) {
                $data[] = [
                    $row['full_name'],
                    $row['rating'] . '/5',
                    $row['comment'],
                    date('M d, Y', strtotime($row['created_at']))
                ];
            }
            break;

        default:
            throw new Exception("Invalid report type");
    }

    echo json_encode([
        'success' => true,
        'columns' => $columns,
        'data' => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
