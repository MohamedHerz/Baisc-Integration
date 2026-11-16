<?php
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$mobile = $_POST['mobile'] ?? '';
$referenceCode = 'ORDER_' . bin2hex(random_bytes(16));
$amount = 50;
$currency = 'BHD';
$publicBaseUrl = rtrim($settings['PUBLIC_BASE_URL'], '/');

$customer = $pdo->prepare('INSERT INTO customers (name, email, mobile) VALUES (?, ?, ?)');
$customer->execute([$name, $email, $mobile]);
$customerId = $pdo->lastInsertId();

$requestBody = json_encode([
    'api' => [
        'version' => '01',
        'user' => $settings['GATE_E_API_USER'],
        'key' => $settings['GATE_E_API_KEY'],
        'resource' => 'payment',
        'action' => 'create',
        'response' => ['type' => 'json'],
    ],
    'payment' => [
        'type' => 'pay',
        'code' => $referenceCode,
        'amount' => $amount,
        'currency' => $currency,
        'client' => [
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
        ],
        'settings' => [
            'callback_url' => $publicBaseUrl . '/callback.php',
            'redirect_url' => $publicBaseUrl . '/payment-return.php?reference_code=' . rawurlencode($referenceCode),
        ],
    ],
]);

$apiUrl = $settings['GATE_E_API_LINK'];

$curl = curl_init($apiUrl);
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $requestBody,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

$gatewayResponse = json_decode($response ?: '', true);
if ($response === false || $status < 200 || $status >= 300 ||
    !is_array($gatewayResponse) ||
    (int) ($gatewayResponse['status'] ?? 0) !== 1) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => is_array($gatewayResponse)
            ? ($gatewayResponse['message'] ?? 'Could not create payment.')
            : 'Could not create payment.',
    ]);
    exit;
}

$gatewayPayment = $gatewayResponse['data']['payment'] ?? [];
$gatewayPaymentId = $gatewayPayment['id'] ?? '';
$paymentUrl = $gatewayPayment['link'] ?? '';

if ($gatewayPaymentId === '' || $paymentUrl === '') {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Gate-E returned incomplete payment details.']);
    exit;
}

$payment = $pdo->prepare(
    "INSERT INTO payments (
        customer_id, reference_code, gateway_payment_id, amount, currency, status
     ) VALUES (?, ?, ?, ?, ?, 'initiated')"
);
$payment->execute([
    $customerId,
    $referenceCode,
    $gatewayPaymentId,
    $amount,
    $currency,
]);

echo json_encode([
    'success' => true,
    'message' => 'Payment request created.',
    'payment_url' => $paymentUrl,
]);
