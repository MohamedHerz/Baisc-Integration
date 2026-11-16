<?php
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    $body = $_POST;
}

$data = is_array($body['data'] ?? null) ? $body['data'] : $body;
$payment = $data['payment'] ?? $body['payment'] ?? [];
$transaction = $data['transaction'] ?? $payment['transaction'] ?? $body['transaction'] ?? [];
if (!is_array($payment) || !is_array($transaction)) {
    http_response_code(400);
    exit('Invalid callback data.');
}

$paymentId = $payment['id'] ?? $data['payment_id'] ?? $body['payment_id'] ?? null;
$referenceCode = $payment['code']
    ?? $payment['reference_code']
    ?? $data['reference_code']
    ?? $body['reference_code']
    ?? null;
$status = $payment['status'] ?? $transaction['status'] ?? null;
if ($status === null && is_string($data['status'] ?? null)) {
    $status = $data['status'];
}
if (is_array($status)) {
    $status = $status['value'] ?? $status['code'] ?? $status['name'] ?? null;
}
if ((!$paymentId && !$referenceCode) || !is_scalar($status)) {
    http_response_code(400);
    exit('Payment ID or status is missing.');
}

if ($paymentId) {
    $findPayment = $pdo->prepare('SELECT id FROM payments WHERE gateway_payment_id = ? LIMIT 1');
    $findPayment->execute([$paymentId]);
} else {
    $findPayment = $pdo->prepare('SELECT id FROM payments WHERE reference_code = ? LIMIT 1');
    $findPayment->execute([$referenceCode]);
}
$savedPayment = $findPayment->fetch(PDO::FETCH_ASSOC);

if (!$savedPayment && $referenceCode) {
    $findPayment = $pdo->prepare('SELECT id FROM payments WHERE reference_code = ? LIMIT 1');
    $findPayment->execute([$referenceCode]);
    $savedPayment = $findPayment->fetch(PDO::FETCH_ASSOC);
}
if (!$savedPayment) {
    http_response_code(404);
    exit('Payment was not found.');
}

$transactionId = $transaction['id'] ?? $transaction['transaction_id'] ?? null;
$transactionStatus = $transaction['status'] ?? $status;

$pdo->beginTransaction();

$updatePayment = $pdo->prepare('UPDATE payments SET status = ? WHERE id = ?');
$updatePayment->execute([$status, $savedPayment['id']]);

if ($transactionId) {
    $saveTransaction = $pdo->prepare(
        'INSERT INTO transactions (payment_id, gateway_transaction_id, status)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE status = ?'
    );
    $saveTransaction->execute([
        $savedPayment['id'],
        $transactionId,
        $transactionStatus,
        $transactionStatus,
    ]);
}

$pdo->commit();
echo json_encode(['success' => true, 'message' => 'Payment callback processed.']);
