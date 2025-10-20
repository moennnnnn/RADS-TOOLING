<?php
function recalc_order_payment(PDO $pdo, int $orderId): void
{
    // 1) kunin total ng order
    $st = $pdo->prepare("SELECT total_amount AS total FROM orders WHERE id=:id");
    $st->execute([':id' => $orderId]);
    $row = $st->fetch();
    if (!$row) return;

    $total = (float)$row['total'];

    // 2) sum ng lahat ng VERIFIED payments

    $st = $pdo->prepare("SELECT COALESCE(SUM(amount_paid),0) AS paid FROM payment_installments WHERE order_id=:id AND UPPER(status)='PAID'");
    $st->execute([':id' => $orderId]);
    $paid = (float)$st->fetch()['paid'];

    $remain = max(0.0, round($total - $paid, 2));

    // 3) decide label
    $label = 'Pending';
    if ($paid <= 0) {
        $label = 'Unpaid';
    } elseif ($remain > 0) {
        $label = 'Partially Paid';
    } else {
        $label = 'Paid';
    }

    // 4) update sa orders
    $u = $pdo->prepare("UPDATE orders SET payment_status=:ps WHERE id=:id");
    $u->execute([':ps' => $label, ':id' => $orderId]);
}
