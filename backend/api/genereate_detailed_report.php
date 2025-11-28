<?php
// backend/api/generate_detailed_report.php
declare(strict_types=1);
session_start();

require_once dirname(__DIR__, 2) . '/includes/guard.php';
require_once dirname(__DIR__) . '/config/database.php';

// [FIX] Manual Library Loading
$manual_tcpdf = dirname(__DIR__) . '/lib/tcpdf/tcpdf.php';
if (file_exists($manual_tcpdf)) {
    require_once $manual_tcpdf;
} else {
    $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
    if (file_exists($autoload)) require_once $autoload;
}

guard_require_staff();

if (!class_exists('TCPDF')) {
    die('Error: TCPDF library not found.');
}

$db = new Database();
$conn = $db->getConnection();

$type = $_GET['type'] ?? 'sales';
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');

// Queries based on type
$queries = [
    'sales' => ["SELECT order_code, full_name, order_date, status, total_amount FROM orders o JOIN customers c ON o.customer_id = c.id WHERE order_date BETWEEN ? AND ? AND status != 'Cancelled'", ['Code', 'Customer', 'Date', 'Status', 'Amount']],
    'orders' => ["SELECT order_code, full_name, order_date, status, total_amount FROM orders o JOIN customers c ON o.customer_id = c.id WHERE order_date BETWEEN ? AND ?", ['Code', 'Customer', 'Date', 'Status', 'Amount']],
    'cancelled' => ["SELECT order_code, full_name, order_date, status, total_amount FROM orders o JOIN customers c ON o.customer_id = c.id WHERE order_date BETWEEN ? AND ? AND status = 'Cancelled'", ['Code', 'Customer', 'Date', 'Status', 'Amount']],
    'new_customers' => ["SELECT full_name, email, phone, created_at, 'N/A' FROM customers WHERE created_at BETWEEN ? AND ?", ['Name', 'Email', 'Phone', 'Joined', '']],
    'feedbacks' => ["SELECT full_name, rating, comment, created_at, 'N/A' FROM feedback f JOIN customers c ON f.customer_id = c.id WHERE created_at BETWEEN ? AND ?", ['Customer', 'Rating', 'Comment', 'Date', '']]
];

$config = $queries[$type] ?? $queries['orders'];
$stmt = $conn->prepare($config[0]);
$stmt->execute([$from, $to]);
$rows = $stmt->fetchAll(PDO::FETCH_NUM);
$headers = $config[1];

// Generate PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('RADS TOOLING');
$pdf->SetTitle('Detailed Report');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Detailed Report: ' . ucfirst(str_replace('_', ' ', $type)), 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, "$from to $to", 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(200);
$w = [35, 50, 30, 30, 35]; // Widths

foreach ($headers as $i => $h) {
    if ($h) $pdf->Cell($w[$i], 7, $h, 1, 0, 'C', 1);
}
$pdf->Ln();

$pdf->SetFont('helvetica', '', 9);
foreach ($rows as $row) {
    foreach ($row as $i => $col) {
        if ($headers[$i]) $pdf->Cell($w[$i], 7, substr((string)$col, 0, 25), 1, 0, 'L');
    }
    $pdf->Ln();
}

if (ob_get_length()) ob_end_clean();
$pdf->Output('Detailed_Report.pdf', 'D');
