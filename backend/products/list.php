<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // GET all products
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->prepare("SELECT id, name, category, price, rating, tag, image, description, stock, is_available FROM products ORDER BY id ASC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode($products);
        exit;
    }
    
    // POST new product (Admin only)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $adminToken = trim($input['adminToken'] ?? '');
        
        // For now, just check if token is provided (in production, validate token)
        // Token validation can be enhanced later with a sessions table
        if (empty($adminToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        // Handle availability update
        if (isset($input['productId']) && isset($input['is_available'])) {
            $productId = intval($input['productId']);
            $isAvailable = $input['is_available'] ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE products SET is_available = ? WHERE id = ?");
            $stmt->execute([$isAvailable, $productId]);
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Product availability updated']);
            exit;
        }
        
        // Handle new product creation
        $name = trim($input['name'] ?? '');
        $category = trim($input['category'] ?? '');
        $price = floatval($input['price'] ?? 0);
        $rating = floatval($input['rating'] ?? 4.5);
        $tag = trim($input['tag'] ?? '');
        $image = trim($input['image'] ?? '');
        $description = trim($input['description'] ?? '');
        $stock = intval($input['stock'] ?? 50);
        
        // Validation
        if (empty($name) || empty($category) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name, category, and valid price are required']);
            exit;
        }
        
        // Insert product
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, rating, tag, image, description, stock, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $category, $price, $rating, $tag, $image, $description, $stock]);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Product added successfully',
            'productId' => $pdo->lastInsertId()
        ]);
        exit;
    }
    
    // PUT update product (Admin only)
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $adminToken = trim($input['adminToken'] ?? '');
        
        if (empty($adminToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $productId = intval($input['id'] ?? 0);
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }
        
        $name = trim($input['name'] ?? '');
        $category = trim($input['category'] ?? '');
        $price = floatval($input['price'] ?? 0);
        $rating = floatval($input['rating'] ?? 4.5);
        $tag = trim($input['tag'] ?? '');
        $image = trim($input['image'] ?? '');
        $description = trim($input['description'] ?? '');
        $stock = intval($input['stock'] ?? 50);
        
        // Validation
        if (empty($name) || empty($category) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name, category, and valid price are required']);
            exit;
        }
        
        // Update product
        $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, rating = ?, tag = ?, image = ?, description = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $category, $price, $rating, $tag, $image, $description, $stock, $productId]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
        exit;
    }
    
    // DELETE product (Admin only)
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $adminToken = trim($input['adminToken'] ?? '');
        
        if (empty($adminToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $productId = intval($input['id'] ?? 0);
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }
        
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
        exit;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
