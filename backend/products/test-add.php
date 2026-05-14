<?php
/**
 * Debug endpoint for testing product addition
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_flush();
    exit();
}

try {
    // Test 1: Database connection
    require_once '../config/database.php';
    $pdo = getDBConnection();
    
    $testResults = [
        'database_connection' => '✅ SUCCESS',
        'tests' => []
    ];
    
    // Test 2: Check products table
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($tableCheck->rowCount() > 0) {
        $testResults['tests'][] = ['name' => 'Products table exists', 'status' => 'OK'];
    } else {
        $testResults['tests'][] = ['name' => 'Products table exists', 'status' => 'MISSING'];
    }
    
    // Test 3: Try to load EmailNotifier
    require_once '../config/EmailNotifier.php';
    if (class_exists('EmailNotifier')) {
        $testResults['tests'][] = ['name' => 'EmailNotifier class loads', 'status' => 'OK'];
    } else {
        $testResults['tests'][] = ['name' => 'EmailNotifier class loads', 'status' => 'FAILED'];
    }
    
    // Test 4: Try to instantiate EmailNotifier
    $notifier = new EmailNotifier();
    $testResults['tests'][] = ['name' => 'EmailNotifier instantiates', 'status' => 'OK'];
    
    // Test 5: Try to add a simple product (without email notifications)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input && isset($input['name']) && isset($input['category']) && isset($input['price'])) {
            $name = trim($input['name']);
            $category = trim($input['category']);
            $price = floatval($input['price']);
            
            if (!empty($name) && !empty($category) && $price > 0) {
                // Try to insert without email
                $stmt = $pdo->prepare("INSERT INTO products (name, category, price, rating, is_available) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$name, $category, $price, 0]);
                
                $productId = $pdo->lastInsertId();
                $testResults['product_added'] = [
                    'status' => 'OK',
                    'product_id' => $productId
                ];
            }
        }
    }
    
    ob_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'All systems operational',
        'results' => $testResults
    ]);
    ob_end_flush();
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    ob_end_flush();
}
?>
