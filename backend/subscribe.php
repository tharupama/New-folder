<?php
/**
 * Subscription API - Alternative location (simpler path)
 * Place this in a more accessible location if the main one doesn't work
 */

// Prevent any output before JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_flush();
    exit();
}

try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    
    // Verify table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'email_subscriptions'");
    if ($tableCheck->rowCount() === 0) {
        throw new Exception("Table 'email_subscriptions' does not exist. Run setup-database.php");
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            ob_end_flush();
            exit;
        }
        
        $email = trim($input['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Valid email required']);
            ob_end_flush();
            exit;
        }
        
        // Check if already subscribed
        $stmt = $pdo->prepare("SELECT id FROM email_subscriptions WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $updateStmt = $pdo->prepare("UPDATE email_subscriptions SET is_subscribed = 1, updated_at = NOW() WHERE email = ?");
            $updateStmt->execute([$email]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'You are already subscribed!'
            ]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO email_subscriptions (email, is_subscribed) VALUES (?, 1)");
            $insertStmt->execute([$email]);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Successfully subscribed!'
            ]);
        }
        ob_end_flush();
        exit;
    }
    
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    ob_end_flush();
    
} catch (Exception $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    ob_end_flush();
}
?>
