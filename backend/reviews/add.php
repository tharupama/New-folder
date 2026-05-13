<?php
// Start output buffering FIRST, before anything else
ob_start();

// Set error handling to NOT display errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile:$errline");
    return true;
});

set_exception_handler(function($exception) {
    error_log("Exception: " . $exception->getMessage());
    error_log("Stack trace: " . $exception->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
    exit;
});

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit;
}

// Clear any accidental output
ob_clean();

try {
    // Load database
    require_once '../config/database.php';
    
    // Get connection using the function
    $db = getDBConnection();
    error_log("Database connection established");
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    // Get input
    $input = file_get_contents('php://input');
    error_log("Raw input: " . $input);
    
    $data = json_decode($input, true);
    error_log("Decoded data: " . print_r($data, true));
    
    if ($data === null) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $product_id = intval($data['product_id'] ?? 0);
    $rating = intval($data['rating'] ?? 0);
    $comment = trim($data['comment'] ?? '');
    $user_name = trim($data['user_name'] ?? '');
    $user_email = trim($data['user_email'] ?? '');
    $user_id_raw = $data['user_id'] ?? null;
    $user_id = is_numeric($user_id_raw) ? intval($user_id_raw) : null;
    
    error_log("Validated input - PID: $product_id, Rating: $rating, Comment length: " . strlen($comment) . ", Name: $user_name");
    
    // Validate
    if ($product_id <= 0) {
        throw new Exception('Invalid product ID');
    }
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Invalid rating');
    }
    if (strlen($comment) < 10) {
        throw new Exception('Comment must be at least 10 characters');
    }
    if (empty($user_name)) {
        throw new Exception('Name is required');
    }

    if ($user_email === '' && $user_id !== null) {
        $emailLookup = $db->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
        $emailLookup->execute([$user_id]);
        $matchedEmail = trim((string)$emailLookup->fetchColumn());
        if ($matchedEmail !== '') {
            $user_email = $matchedEmail;
        }
    }

    if ($user_id === null && $user_email !== '') {
        $userLookup = $db->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $userLookup->execute([$user_email]);
        $matchedUserId = $userLookup->fetchColumn();
        if ($matchedUserId) {
            $user_id = intval($matchedUserId);
        }
    }
    
    // Check product exists
    $check = $db->prepare("SELECT id FROM products WHERE id = ?");
    $check->execute([$product_id]);
    if ($check->rowCount() === 0) {
        throw new Exception('Product not found');
    }
    error_log("Product $product_id found");
    
    // Insert review
    $insert = $db->prepare(
           "INSERT INTO product_reviews (product_id, user_name, user_email, user_id, rating, comment) 
            VALUES (?, ?, ?, ?, ?, ?)"
    );
        $result = $insert->execute([$product_id, $user_name, $user_email !== '' ? $user_email : null, $user_id ?: null, $rating, $comment]);
    error_log("Insert result: " . ($result ? 'success' : 'failed'));
    
    if (!$result) {
        throw new Exception('Failed to insert review');
    }
    
    $review_id = $db->lastInsertId();
    error_log("Review created with ID: $review_id");
    
    // Update product average rating
    $update = $db->prepare(
        "UPDATE products 
         SET rating = (SELECT AVG(rating) FROM product_reviews WHERE product_id = ?)
         WHERE id = ?"
    );
    $update->execute([$product_id, $product_id]);
    error_log("Product rating updated");
    
    // Return success
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully',
        'review_id' => $review_id
    ]);
    
} catch (Exception $e) {
    error_log("Review submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>
