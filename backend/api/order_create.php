<?php
declare(strict_types=1);
require __DIR__ . '/helpers.php';

$type  = $_POST['type'] ?? '';
$pid   = (int)($_POST['pid'] ?? 0);
$name  = $_POST['name'] ?? 'Item';
$price = (float)($_POST['price'] ?? 0);
$qty   = (int)($_POST['qty'] ?? 1);
$vat   = (float)($_POST['vat'] ?? 0);

$pdo = db();
$pdo->beginTransaction();
try{
  $totals = compute_totals($price,$qty);
  $stmt = $pdo->prepare("INSERT INTO orders (order_no, type, product_id, product_name, price, qty, subtotal, vat, total, status, created_at) VALUES (UUID(), :type, :pid, :name, :price, :qty, :sub, :vat, :total, 'pending_payment', NOW())");
  $stmt->execute([
    ':type'=>$type, ':pid'=>$pid, ':name'=>$name, ':price'=>$price, ':qty'=>$qty,
    ':sub'=>$totals['sub'], ':vat'=>$totals['vat'], ':total'=>$totals['total']
  ]);
  $orderId = $pdo->lastInsertId();

  if($type==='delivery'){
    $stmt = $pdo->prepare("INSERT INTO order_delivery (order_id, first_name,last_name,email,phone,province,city,street,postal) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
      $orderId,
      $_POST['first_name']??'', $_POST['last_name']??'', $_POST['email']??'',
      $_POST['phone']??'', $_POST['province']??'', $_POST['city']??'',
      $_POST['street']??'', $_POST['postal']??''
    ]);
  }
  $pdo->commit();
  json_out(true, ['order_id'=>$orderId]);
}catch(Throwable $e){
  $pdo->rollBack();
  json_out(false, ['message'=>$e->getMessage()]);
}
