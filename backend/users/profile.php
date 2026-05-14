<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function respond($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function columnExists($pdo, $tableName, $columnName) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = ? 
        AND COLUMN_NAME = ?
    ");
    $stmt->execute([$tableName, $columnName]);
    return (int)$stmt->fetchColumn() > 0;
}

function ensureUsersSchema($pdo) {
    if (!columnExists($pdo, 'users', 'phone')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(40) DEFAULT NULL");
    }

    if (!columnExists($pdo, 'users', 'address')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN address VARCHAR(200) DEFAULT NULL");
    }
}

function fetchUser($pdo, $email) {
    $stmt = $pdo->prepare("
        SELECT id, username, email, phone, address, created_at, updated_at 
        FROM users 
        WHERE email = ? 
        LIMIT 1
    ");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

try {
    $pdo = getDBConnection();
    ensureUsersSchema($pdo);

    // ================= GET PROFILE =================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $email = trim($_GET['email'] ?? '');

        if ($email === '') {
            respond(400, [
                'success' => false,
                'message' => 'Email is required'
            ]);
        }

        $user = fetchUser($pdo, $email);

        if (!$user) {
            respond(404, [
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        respond(200, [
            'success' => true,
            'data' => $user
        ]);
    }

    // ================= UPDATE PROFILE =================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $input = json_decode(file_get_contents('php://input'), true);

        $email = trim($input['email'] ?? '');
        $username = trim($input['username'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $address = trim($input['address'] ?? '');

        if ($email === '') {
            respond(400, [
                'success' => false,
                'message' => 'Email is required'
            ]);
        }

        // Auto fallback username
        if ($username === '') {
            $username = explode('@', $email)[0];
        }

        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = ?, phone = ?, address = ? 
            WHERE email = ?
        ");

        $stmt->execute([
            $username,
            $phone ?: null,
            $address ?: null,
            $email
        ]);

        $updatedUser = fetchUser($pdo, $email);

        respond(200, [
            'success' => true,
            'updated' => true,
            'message' => 'Profile updated successfully',
            'data' => $updatedUser
        ]);
    }

    // ================= INVALID METHOD =================
    respond(405, [
        'success' => false,
        'message' => 'Method not allowed'
    ]);

} catch (Exception $e) {
    respond(500, [
        'success' => false,
        'message' => 'Server error'
    ]);
}
?>