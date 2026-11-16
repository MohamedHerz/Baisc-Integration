<?php
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$id = $_GET['id'] ?? '';
if (!is_string($id) || !ctype_digit($id) || (int) $id < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payment ID.']);
    exit;
}

$paymentQuery = $pdo->prepare(
    'SELECT p.amount, p.currency, p.status, p.created_at,
            c.name, c.email, c.mobile
     FROM payments p
     JOIN customers c ON c.id = p.customer_id
     WHERE p.id = ?
     LIMIT 1'
);
$paymentQuery->execute([(int) $id]);
$payment = $paymentQuery->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Payment was not found.']);
    exit;
}

$transactionQuery = $pdo->prepare(
    'SELECT gateway_transaction_id
     FROM transactions
     WHERE payment_id = ?
     ORDER BY id DESC'
);
$transactionQuery->execute([(int) $id]);

echo json_encode([
    'success' => true,
    'payment' => $payment,
    'transactions' => $transactionQuery->fetchAll(PDO::FETCH_ASSOC),
]);
