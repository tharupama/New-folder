<?php
// DeepSeek chat endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$history = $input['history'] ?? [];

if ($message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message is required']);
    exit;
}

$configPath = __DIR__ . '/../config/deepseek.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DeepSeek config missing']);
    exit;
}

$config = require $configPath;
$apiKey = $config['api_key'] ?? '';
$model = $config['model'] ?? 'deepseek-chat';

if ($apiKey === '' || $apiKey === 'DEEPSEEK_API_KEY_HERE') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DeepSeek API key not configured']);
    exit;
}

$messages = [
    [
        'role' => 'system',
        'content' => 'You are BUY LK Assistant. Help with menu items, orders, delivery, and store info. Keep answers short and friendly.'
    ]
];

if (is_array($history)) {
    foreach ($history as $item) {
        if (!isset($item['role'], $item['content'])) {
            continue;
        }
        $role = $item['role'] === 'user' ? 'user' : 'assistant';
        $messages[] = [
            'role' => $role,
            'content' => (string) $item['content']
        ];
    }
}

$messages[] = [
    'role' => 'user',
    'content' => $message
];

$payload = [
    'model' => $model,
    'messages' => $messages,
    'temperature' => 0.4,
    'max_tokens' => 300
];

$ch = curl_init('https://api.deepseek.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Chat request failed']);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => 'DeepSeek API error', 'details' => $response]);
    exit;
}

$data = json_decode($response, true);
$reply = $data['choices'][0]['message']['content'] ?? '';

if ($reply === '') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Empty response from AI']);
    exit;
}

echo json_encode(['success' => true, 'reply' => $reply]);
