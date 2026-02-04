<?php
// Must set headers and error handling FIRST before any output
ob_start(); // Buffer output to prevent errors from appearing

// Enable error reporting but DON'T display errors (they break JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/../../logs/php_error.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Clear any buffered output
ob_clean();

try {
    require_once '../config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load database configuration: ' . $e->getMessage()
    ]);
    exit;
}

try {
    require_once '../config/database.php';
    
    // Get connection using the function
    $db = getDBConnection();
    error_log("Database connection established");
    
    if (!$db) {
        throw new Exception('Database connection is null');
    }
    
    // Get product ID from query parameter
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    
    error_log("Getting reviews for product ID: " . $product_id);
    
    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid product ID'
        ]);
        exit;
    }
    
    // Fetch reviews for the product
    $query = "SELECT 
                id,
                product_id,
                user_name,
                rating,
                comment,
                created_at
              FROM product_reviews 
              WHERE product_id = :product_id 
              ORDER BY created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate average rating
    $avgQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                 FROM product_reviews 
                 WHERE product_id = :product_id";
    $avgStmt = $db->prepare($avgQuery);
    $avgStmt->bindParam(':product_id', $product_id);
    $avgStmt->execute();
    $avgData = $avgStmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($reviews) . " reviews for product " . $product_id);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'reviews' => $reviews,
            'average_rating' => round($avgData['avg_rating'], 1),
            'total_reviews' => intval($avgData['total_reviews'])
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
} catch (Exception $e) {
    error_log("General error in get.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error occurred'
    ]);
}
?>
