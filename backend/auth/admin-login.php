<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    
    // Hardcoded admin credentials
    $adminUsername = 'admin';
    $adminPassword = 'admin123';
    
    // Validate credentials
    if ($username === $adminUsername && $password === $adminPassword) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Admin login successful',
            'adminToken' => bin2hex(random_bytes(32)),
            'adminName' => 'admin'
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid admin credentials'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
