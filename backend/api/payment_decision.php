<?php
declare(strict_types=1);
require __DIR__ . '/helpers.php';

// Example: /backend/api/payment_decision.php?order=123&action=approve
$order = (int)($_GET['order'] ?? 0);
$action= $_GET['action'] ?? '';
if(!$order || !in_array($action,['approve','reject'],true)) json_out(false,['message'=>'Bad request']);

$pdo = db();
$new = $action==='approve' ? 'approved' : 'rejected';
$pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$new,$order]);
json_out(true,['status'=>$new]);
