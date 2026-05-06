<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function ensureAdvertisementsTable($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS advertisements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        button_text VARCHAR(80) DEFAULT 'Learn More',
        button_link VARCHAR(500) DEFAULT '',
        footer_text VARCHAR(150) DEFAULT '',
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $countStmt = $pdo->query("SELECT COUNT(*) AS total FROM advertisements");
    $count = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    if ($count === 0) {
        $seedStmt = $pdo->prepare("INSERT INTO advertisements (title, message, button_text, button_link, footer_text, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $seedStmt->execute(['Weekend Family Combo', 'Order 2 large pizzas and get a free drink combo.', 'Order Now', 'shop.html', 'Limited-time offer', 1, 1]);
        $seedStmt->execute(['Bakery Fresh Hour', 'Fresh buns and pastries every evening from 5 PM to 7 PM.', 'Browse Bakery', 'shop.html', 'Hot and fresh from the oven', 2, 1]);
        $seedStmt->execute(['Late Night Kottu Deal', 'Enjoy special prices on kottu after 9 PM.', 'View Deal', 'shop.html', 'Available tonight only', 3, 1]);
    }
}

function requireAdminToken($input = null) {
    if ($input !== null) {
        return trim($input['adminToken'] ?? '');
    }

    return trim($_GET['adminToken'] ?? '');
}

function loadAdvertisementPayload($input) {
    return [
        'id' => intval($input['id'] ?? 0),
        'title' => trim($input['title'] ?? ''),
        'message' => trim($input['message'] ?? ''),
        'button_text' => trim($input['button_text'] ?? 'Learn More'),
        'button_link' => trim($input['button_link'] ?? ''),
        'footer_text' => trim($input['footer_text'] ?? ''),
        'sort_order' => intval($input['sort_order'] ?? 0),
        'is_active' => isset($input['is_active']) ? (intval($input['is_active']) ? 1 : 0) : 1,
    ];
}

function createAdvertisementRecord($pdo, $input) {
    $payload = loadAdvertisementPayload($input);

    if (empty($payload['title']) || empty($payload['message'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title and message are required']);
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO advertisements (title, message, button_text, button_link, footer_text, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $payload['title'],
        $payload['message'],
        $payload['button_text'],
        $payload['button_link'],
        $payload['footer_text'],
        $payload['sort_order'],
        $payload['is_active']
    ]);

    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Advertisement created successfully']);
}

function updateAdvertisementRecord($pdo, $input) {
    $payload = loadAdvertisementPayload($input);

    if (!$payload['id'] || empty($payload['title']) || empty($payload['message'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID, title and message are required']);
        return;
    }

    $stmt = $pdo->prepare("UPDATE advertisements SET title = ?, message = ?, button_text = ?, button_link = ?, footer_text = ?, sort_order = ?, is_active = ? WHERE id = ?");
    $stmt->execute([
        $payload['title'],
        $payload['message'],
        $payload['button_text'],
        $payload['button_link'],
        $payload['footer_text'],
        $payload['sort_order'],
        $payload['is_active'],
        $payload['id']
    ]);

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Advertisement updated successfully']);
}

function deleteAdvertisementRecord($pdo, $input) {
    $payload = loadAdvertisementPayload($input);

    if (!$payload['id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Advertisement ID is required']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM advertisements WHERE id = ?");
    $stmt->execute([$payload['id']]);

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Advertisement deleted successfully']);
}

try {
    $pdo = getDBConnection();
    ensureAdvertisementsTable($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $all = isset($_GET['all']) && $_GET['all'] === '1';

        if ($all) {
            $adminToken = requireAdminToken();
            if (empty($adminToken)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT id, title, message, button_text, button_link, footer_text, sort_order, is_active, created_at, updated_at FROM advertisements ORDER BY sort_order ASC, id ASC");
            $stmt->execute();
            $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $ads]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, title, message, button_text, button_link, footer_text, sort_order FROM advertisements WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $stmt->execute();
        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $ads]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $adminToken = requireAdminToken($input);

        if (empty($adminToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }

        $action = trim(strtolower($input['action'] ?? 'create'));

        if ($action === 'create') {
            createAdvertisementRecord($pdo, $input);
            exit;
        }

        if ($action === 'update') {
            updateAdvertisementRecord($pdo, $input);
            exit;
        }

        if ($action === 'delete') {
            deleteAdvertisementRecord($pdo, $input);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid advertisement action']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $adminToken = requireAdminToken($input);

        if (empty($adminToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }

        updateAdvertisementRecord($pdo, $input);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $adminToken = requireAdminToken($input);

        if (empty($adminToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }

        deleteAdvertisementRecord($pdo, $input);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
