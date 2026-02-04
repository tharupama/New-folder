<?php
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if product_reviews table exists
    $query = "SHOW TABLES LIKE 'product_reviews'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    // Get table structure if it exists
    $structure = null;
    if ($tableExists) {
        $query = "DESCRIBE product_reviews";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Count existing reviews
    $reviewCount = 0;
    if ($tableExists) {
        $query = "SELECT COUNT(*) as count FROM product_reviews";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $reviewCount = $result['count'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'database' => 'shop_db',
        'table_exists' => $tableExists,
        'review_count' => $reviewCount,
        'table_structure' => $structure
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
