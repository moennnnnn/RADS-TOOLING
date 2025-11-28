<?php
// backend/api/generate_report.php
declare(strict_types=1);
session_start();

require_once dirname(__DIR__, 2) . '/includes/guard.php';
require_once dirname(__DIR__) . '/config/database.php';

// [FIX] Manual Library Loading (Bypass Composer)
$manual_tcpdf = dirname(__DIR__) . '/lib/tcpdf/tcpdf.php';
if (file_exists($manual_tcpdf)) {
    require_once $manual_tcpdf;
} else {
    // Fallback sa composer kung sakali
    $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
    if (file_exists($autoload)) require_once $autoload;
}

guard_require_staff();

if (!class_exists('TCPDF')) {
    die('Error: TCPDF library not found. Please follow Step 1 (Manual Install).');
}

$db = new Database();
$conn = $db->getConnection();

$from_date = $_GET['from'] ?? date('Y-m-01');
$to_date = $_GET['to'] ?? date('Y-m-d');
$from = new DateTime($from_date);
$to = new DateTime($to_date);

try {
    // 1. Fetch Data
    $queries = [
        'sales' => "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_date BETWEEN ? AND ? AND status != 'Cancelled'",
        'orders' => "SELECT COUNT(*) FROM orders WHERE order_date BETWEEN ? AND ?",
        'paid' => "SELECT COUNT(*) FROM orders WHERE order_date BETWEEN ? AND ? AND payment_status = 'Fully Paid'",
        'cancelled' => "SELECT COUNT(*) FROM orders WHERE order_date BETWEEN ? AND ? AND status = 'Cancelled'",
        'pending' => "SELECT COUNT(*) FROM orders WHERE order_date BETWEEN ? AND ? AND status = 'Pending'",
        'customers' => "SELECT COUNT(*) FROM customers WHERE created_at BETWEEN ? AND ?",
        'feedbacks' => "SELECT COUNT(*) FROM feedback WHERE created_at BETWEEN ? AND ?"
    ];

    $stats = [];
    foreach ($queries as $key => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$from_date, $to_date]);
        $stats[$key] = $stmt->fetchColumn();
    }

    // Recent Orders
    $stmt = $conn->prepare("SELECT order_code, full_name, order_date, total_amount, status FROM orders o JOIN customers c ON o.customer_id = c.id WHERE order_date BETWEEN ? AND ? ORDER BY order_date DESC LIMIT 20");
    $stmt->execute([$from_date, $to_date]);
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database Error: ' . $e->getMessage());
}

// 2. Create PDF
// [FIX] Use TCPDF class directly
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Document Settings
$pdf->SetCreator('RADS TOOLING');
$pdf->SetTitle('Sales Report');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// Header
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(47, 91, 136);
$pdf->Cell(0, 10, 'RADS TOOLING INC.', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(100);
$pdf->Cell(0, 5, 'Sales & Order Report', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0);
$pdf->Cell(0, 10, 'Period: ' . $from->format('M d, Y') . ' - ' . $to->format('M d, Y'), 0, 1, 'C');
$pdf->Ln(5);

// Statistics Grid
$pdf->SetFont('helvetica', '', 10);
$w = 90;
$data = [
    ['Total Sales', 'P ' . number_format((float)$stats['sales'], 2)],
    ['Total Orders', $stats['orders']],
    ['Fully Paid', $stats['paid']],
    ['Cancelled', $stats['cancelled']],
    ['New Customers', $stats['customers']],
    ['Feedbacks', $stats['feedbacks']]
];

foreach ($data as $row) {
    $pdf->Cell($w, 8, $row[0] . ': ' . $row[1], 1, 0, 'L');
    if ($pdf->GetX() > 150) $pdf->Ln();
}
$pdf->Ln(15);

// Recent Orders Table
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(47, 91, 136);
$pdf->Cell(0, 10, 'Recent Orders', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(255);
$pdf->SetFillColor(47, 91, 136);
$pdf->Cell(35, 7, 'Code', 1, 0, 'C', 1);
$pdf->Cell(50, 7, 'Customer', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'Date', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'Amount', 1, 0, 'C', 1);
$pdf->Cell(35, 7, 'Status', 1, 1, 'C', 1);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0);
foreach ($recent as $r) {
    $pdf->Cell(35, 7, $r['order_code'], 1, 0, 'C');
    $pdf->Cell(50, 7, substr($r['full_name'], 0, 25), 1, 0, 'L');
    $pdf->Cell(30, 7, date('M d', strtotime($r['order_date'])), 1, 0, 'C');
    $pdf->Cell(30, 7, number_format((float)$r['total_amount'], 2), 1, 0, 'R');
    $pdf->Cell(35, 7, $r['status'], 1, 1, 'C');
}

// [FIX] Output Cleaning
if (ob_get_length()) ob_end_clean();

$pdf->Output('Report.pdf', 'D');
