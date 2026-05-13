<?php
// Prevent any output before JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Error logging
function logError($message) {
    $logFile = __DIR__ . '/../logs/subscription-errors.log';
    @mkdir(dirname($logFile), 0755, true);
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $message\n", FILE_APPEND);
}

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError("PHP Error [$errno]: $errstr in $errfile:$errline");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHP Error',
        'debug' => $errstr
    ]);
    ob_end_flush();
    exit;
});

require_once '../config/database.php';

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Verify table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'email_subscriptions'");
    if ($tableCheck->rowCount() === 0) {
        throw new Exception("Table 'email_subscriptions' does not exist. Run database.sql first.");
    }
    
    // POST - Subscribe user to email notifications
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        
        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            exit;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }
        
        // Check if already subscribed
        $stmt = $pdo->prepare("SELECT id FROM email_subscriptions WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing subscription to active
            $updateStmt = $pdo->prepare("UPDATE email_subscriptions SET is_subscribed = 1, updated_at = NOW() WHERE email = ?");
            $updateStmt->execute([$email]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'You are already subscribed to our notifications!'
            ]);
        } else {
            // Create new subscription
            $insertStmt = $pdo->prepare("INSERT INTO email_subscriptions (email, is_subscribed) VALUES (?, 1)");
            $insertStmt->execute([$email]);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Successfully subscribed! You will receive email notifications about new items.'
            ]);
        }
        exit;
    }
    
    // GET - Check subscription status
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $email = $_GET['email'] ?? '';
        
        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email parameter required']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT id, email, is_subscribed, created_at FROM email_subscriptions WHERE email = ?");
        $stmt->execute([$email]);
        $subscription = $stmt->fetch();
        
        if ($subscription) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $subscription
            ]);
        } else {
            http_response_code(200);
            echo json_encode([
                'success' => false,
                'data' => null,
                'message' => 'Not subscribed'
            ]);
        }
        exit;
    }
    
    // PUT - Unsubscribe user
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Valid email required']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE email_subscriptions SET is_subscribed = 0 WHERE email = ?");
        $stmt->execute([$email]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'You have been unsubscribed'
        ]);
        exit;
    }
    
    // DELETE - Remove subscription completely
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Valid email required']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM email_subscriptions WHERE email = ?");
        $stmt->execute([$email]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Subscription removed'
        ]);
        exit;
    }
    
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    
} catch (PDOException $e) {
    logError("Database error: " . $e->getMessage());
    http_response_code(500);
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Database error. Please run the setup script at: backend/setup-database.php',
        'debug' => $e->getMessage()
    ]);
    ob_end_flush();
} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
    http_response_code(500);
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    ob_end_flush();
}
?>
