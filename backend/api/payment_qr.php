<?php
require_once __DIR__ . '/_bootstrap.php';
require_customer_id();

$method = strtolower($_GET['method'] ?? '');
if (!in_array($method, ['gcash','bpi'], true)) fail('Invalid method.');

$pdo = db();
$stmt = $pdo->prepare("SELECT image_path FROM payment_qr WHERE method=:m AND is_active=1 LIMIT 1");
$stmt->execute([':m'=>$method]);
$row = $stmt->fetch();

$qr = null;
if ($row && !empty($row['image_path'])) {
  $qr = '/RADS-TOOLING/' . ltrim($row['image_path'],'/');
}
ok(['qr_url'=>$qr]);
