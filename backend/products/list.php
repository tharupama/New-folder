<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
        $stmt = $pdo->prepare("SELECT id, name, category, price, rating, tag, image, description, stock FROM products ORDER BY id ASC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode($products);
        exit;
    }
    
    // POST new product (Admin only)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = trim($input['name'] ?? '');
        $category = trim($input['category'] ?? '');
        $price = floatval($input['price'] ?? 0);
        $rating = floatval($input['rating'] ?? 4.5);
        $tag = trim($input['tag'] ?? '');
        $image = trim($input['image'] ?? '');
        $description = trim($input['description'] ?? '');
        $stock = intval($input['stock'] ?? 50);
        $adminKey = trim($input['adminKey'] ?? '');
        
        // Basic admin authentication (change this key!)
        if ($adminKey !== 'admin123') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        // Validation
        if (empty($name) || empty($category) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name, category, and valid price are required']);
            exit;
        }
        
        // Insert product
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, rating, tag, image, description, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price, $rating, $tag, $image, $description, $stock]);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Product added successfully',
            'productId' => $pdo->lastInsertId()
        ]);
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
