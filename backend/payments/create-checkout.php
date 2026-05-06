<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/stripe.php';

const APP_PUBLIC_BASE_PATH = '/New%20folder%20(2)/New-folder';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

function getAppBaseUrl() {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return rtrim($scheme . '://' . $host . APP_PUBLIC_BASE_PATH, '/');
}

function sendError($statusCode, $message) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit();
}

function findCaBundlePath() {
    $configuredPath = ini_get('curl.cainfo') ?: ini_get('openssl.cafile');
    if ($configuredPath && is_file($configuredPath)) {
        return $configuredPath;
    }

    $candidates = [
        'C:\\wamp64\\bin\\php\\cacert.pem',
        'C:\\wamp64\\bin\\apache\\apache2.4.54\\bin\\cacert.pem',
        'C:\\wamp64\\bin\\apache\\apache2.4.58\\bin\\cacert.pem',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    return null;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    sendError(400, 'Invalid JSON payload');
}

$cartItems = $input['cartItems'] ?? [];
$shippingAmount = (float)($input['shipping'] ?? 0);
$billingDetails = is_array($input['billingDetails'] ?? null) ? $input['billingDetails'] : [];

if (!is_array($cartItems) || count($cartItems) === 0) {
    sendError(400, 'Cart is empty');
}

$lineItems = [];

foreach ($cartItems as $item) {
    $name = trim((string)($item['name'] ?? 'Product'));
    $quantity = max(1, (int)($item['quantity'] ?? 1));
    $price = (float)($item['price'] ?? 0);
    $unitAmount = (int) round($price * 100);

    if ($name === '' || $unitAmount <= 0) {
        continue;
    }

    $lineItems[] = [
        'price_data' => [
            'currency' => STRIPE_CHECKOUT_CURRENCY,
            'product_data' => [
                'name' => substr($name, 0, 120),
            ],
            'unit_amount' => $unitAmount,
        ],
        'quantity' => $quantity,
    ];
}

if (count($lineItems) === 0) {
    sendError(400, 'No valid checkout items were found');
}

if ($shippingAmount > 0) {
    $lineItems[] = [
        'price_data' => [
            'currency' => STRIPE_CHECKOUT_CURRENCY,
            'product_data' => [
                'name' => 'Shipping',
            ],
            'unit_amount' => (int) round($shippingAmount * 100),
        ],
        'quantity' => 1,
    ];
}

$baseUrl = getAppBaseUrl();
$successUrl = $baseUrl . '/front/payment-success.html?session_id={CHECKOUT_SESSION_ID}';
$cancelUrl = $baseUrl . '/front/payment-cancel.html';

$formFields = [
    'mode' => 'payment',
    'success_url' => $successUrl,
    'cancel_url' => $cancelUrl,
    'line_items' => $lineItems,
    'payment_method_types' => ['card'],
];

if (!empty($input['email']) && filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $formFields['customer_email'] = trim($input['email']);
}

$payload = http_build_query($formFields);

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . base64_encode(STRIPE_SECRET_KEY . ':'),
    ],
]);

$caBundlePath = findCaBundlePath();
if ($caBundlePath) {
    curl_setopt($ch, CURLOPT_CAINFO, $caBundlePath);
} elseif (in_array(strtolower($_SERVER['HTTP_HOST'] ?? ''), ['localhost', '127.0.0.1'], true)) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
}

$responseBody = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($responseBody === false) {
    sendError(500, $curlError ?: 'Unable to contact Stripe');
}

$stripeResponse = json_decode($responseBody, true);

if ($statusCode < 200 || $statusCode >= 300 || !is_array($stripeResponse)) {
    $message = $stripeResponse['error']['message'] ?? 'Stripe checkout session could not be created';
    sendError($statusCode >= 400 ? $statusCode : 500, $message);
}

echo json_encode([
    'success' => true,
    'sessionId' => $stripeResponse['id'] ?? null,
    'sessionUrl' => $stripeResponse['url'] ?? null,
]);
