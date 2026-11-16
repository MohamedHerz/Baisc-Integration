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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
</head>
<body>
  <main class="container py-5">
    <div class="card border-0 shadow-sm mx-auto" style="max-width: 32rem;">
      <div class="card-body p-4">
        <h1 class="h3 mb-3">Payment result</h1>
        <p class="fs-5">Payment status: <strong><?= $status ?></strong></p>
        <?php if ($payment['status'] === 'initiated'): ?>
          <p class="alert alert-info">Waiting for the payment confirmation.</p>
        <?php endif; ?>
        <a class="btn btn-primary" href="index.php" target="_top">Back to payments</a>
      </div>
    </div>
  </main>
</body>
</html>
