<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

function respond($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

try {
    $db = getDBConnection();

    $userId = intval($_GET['user_id'] ?? 0);
    $userEmail = trim($_GET['email'] ?? $_GET['user_email'] ?? '');
    $userName = trim($_GET['user_name'] ?? '');

    if ($userId <= 0 && $userEmail === '' && $userName === '') {
        respond(400, ['success' => false, 'message' => 'user_id, email or user_name is required']);
    }

    $whereParts = [];
    $params = [];
    $needsUserJoin = $userEmail !== '';

    if ($userId > 0) {
        $whereParts[] = 'r.user_id = ?';
        $params[] = $userId;
    }

    if ($userEmail !== '') {
        $whereParts[] = 'LOWER(u.email) = LOWER(?)';
        $params[] = $userEmail;
    }

    if ($userName !== '') {
        $whereParts[] = 'LOWER(r.user_name) LIKE LOWER(?)';
        $params[] = '%' . $userName . '%';
    }

    $query = "SELECT r.id, r.product_id, r.user_name, r.user_id, u.email AS user_email, r.rating, r.comment, r.created_at, p.name AS product_name
              FROM product_reviews r
              LEFT JOIN products p ON r.product_id = p.id
              LEFT JOIN users u ON r.user_id = u.id
              WHERE " . implode(' OR ', $whereParts) . "
              ORDER BY r.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    respond(200, ['success' => true, 'data' => $reviews]);
} catch (PDOException $e) {
    respond(500, ['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    respond(500, ['success' => false, 'message' => 'Error occurred']);
}
?>
