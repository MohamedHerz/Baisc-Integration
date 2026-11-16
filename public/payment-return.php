<?php
require_once dirname(__DIR__) . '/config/database.php';

$referenceCode = $_GET['reference_code'] ?? '';
if (!is_string($referenceCode) || $referenceCode === '') {
    http_response_code(400);
    exit('Payment reference is missing.');
}

$paymentQuery = $pdo->prepare(
    'SELECT status FROM payments WHERE reference_code = ? LIMIT 1'
);
$paymentQuery->execute([$referenceCode]);
$payment = $paymentQuery->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    http_response_code(404);
    exit('Payment was not found.');
}

$status = htmlspecialchars($payment['status'], ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Payment result</title>
</head>
<body>
  <h1>Payment result</h1>
  <p>Payment status: <?= $status ?></p>
  <?php if ($payment['status'] === 'initiated'): ?>
    <p>Waiting for the payment confirmation.</p>
  <?php endif; ?>
  <p><a href="index.php" target="_top">Back to payments</a></p>
</body>
</html>
