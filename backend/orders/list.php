<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function readJsonBody() {
    $input = json_decode(file_get_contents('php://input'), true);
    return is_array($input) ? $input : [];
}

function respond($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function columnExists($pdo, $tableName, $columnName) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$tableName, $columnName]);
    return (int)$stmt->fetchColumn() > 0;
}

function isColumnNullable($pdo, $tableName, $columnName) {
    $stmt = $pdo->prepare('SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1');
    $stmt->execute([$tableName, $columnName]);
    $value = $stmt->fetchColumn();
    return strtoupper((string)$value) === 'YES';
}

function ensureOrdersSchema($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        supabase_user_id VARCHAR(64) DEFAULT NULL,
        user_email VARCHAR(100) DEFAULT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    if (!columnExists($pdo, 'orders', 'supabase_user_id')) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN supabase_user_id VARCHAR(64) DEFAULT NULL AFTER user_id");
    }

    if (columnExists($pdo, 'orders', 'user_id') && !isColumnNullable($pdo, 'orders', 'user_id')) {
        $pdo->exec("ALTER TABLE orders MODIFY COLUMN user_id INT NULL");
    }

    if (!columnExists($pdo, 'orders', 'user_email')) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN user_email VARCHAR(100) DEFAULT NULL AFTER supabase_user_id");
    }

    if (!columnExists($pdo, 'orders', 'updated_at')) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
}

try {
    $pdo = getDBConnection();
    ensureOrdersSchema($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $all = isset($_GET['all']) && $_GET['all'] === '1';

        if ($all) {
            $adminToken = trim($_GET['adminToken'] ?? '');
            if ($adminToken === '') {
                respond(403, ['success' => false, 'message' => 'Unauthorized access']);
            }

            $stmt = $pdo->prepare("SELECT o.id, o.user_id, o.supabase_user_id, o.user_email, o.total_amount, o.status, o.created_at, o.updated_at, COALESCE(u.username, o.user_email, 'Customer') AS customer_name
                                   FROM orders o
                                   LEFT JOIN users u ON o.user_id = u.id
                                   ORDER BY o.created_at DESC");
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            respond(200, ['success' => true, 'data' => $orders]);
        }

        $userId = intval($_GET['user_id'] ?? 0);
        $supabaseUserId = trim($_GET['supabase_user_id'] ?? '');
        $userEmail = trim($_GET['user_email'] ?? '');

        if ($userId <= 0 && $supabaseUserId === '' && $userEmail === '') {
            respond(400, ['success' => false, 'message' => 'user_id, supabase_user_id or user_email is required']);
        }

        $whereParts = [];
        $params = [];

        if ($userId > 0) {
            $whereParts[] = 'o.user_id = ?';
            $params[] = $userId;
        }

        if ($supabaseUserId !== '') {
            $whereParts[] = 'o.supabase_user_id = ?';
            $params[] = $supabaseUserId;
        }

        if ($userEmail !== '') {
            $whereParts[] = 'o.user_email = ?';
            $params[] = $userEmail;
        }

        $query = "SELECT o.id, o.user_id, o.supabase_user_id, o.user_email, o.total_amount, o.status, o.created_at, o.updated_at
                  FROM orders o
                  WHERE " . implode(' OR ', $whereParts) . "
                  ORDER BY o.created_at DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        respond(200, ['success' => true, 'data' => $orders]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = readJsonBody();

        $userId = intval($input['user_id'] ?? 0);
        $supabaseUserId = trim($input['supabase_user_id'] ?? '');
        $userEmail = trim($input['user_email'] ?? '');
        $totalAmount = floatval($input['total_amount'] ?? 0);
        $status = trim($input['status'] ?? 'pending');
        $items = $input['items'] ?? [];

        if ($userId <= 0 && $supabaseUserId === '' && $userEmail === '') {
            respond(400, ['success' => false, 'message' => 'Order must include a customer identifier']);
        }

        if ($totalAmount <= 0) {
            respond(400, ['success' => false, 'message' => 'total_amount must be greater than 0']);
        }

        $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'pending';
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO orders (user_id, supabase_user_id, user_email, total_amount, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId > 0 ? $userId : null,
            $supabaseUserId !== '' ? $supabaseUserId : null,
            $userEmail !== '' ? $userEmail : null,
            $totalAmount,
            $status
        ]);

        $orderId = intval($pdo->lastInsertId());

        if (is_array($items) && !empty($items)) {
            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            foreach ($items as $item) {
                $productId = intval($item['product_id'] ?? 0);
                $quantity = intval($item['quantity'] ?? 0);
                $price = floatval($item['price'] ?? 0);

                if ($productId <= 0 || $quantity <= 0 || $price < 0) {
                    continue;
                }

                $itemStmt->execute([$orderId, $productId, $quantity, $price]);
            }
        }

        $pdo->commit();

        respond(201, [
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $orderId
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = readJsonBody();

        $adminToken = trim($input['adminToken'] ?? '');
        if ($adminToken === '') {
            respond(403, ['success' => false, 'message' => 'Unauthorized access']);
        }

        $orderId = intval($input['id'] ?? 0);
        $status = strtolower(trim($input['status'] ?? ''));

        if ($orderId <= 0 || $status === '') {
            respond(400, ['success' => false, 'message' => 'Order ID and status are required']);
        }

        $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            respond(400, ['success' => false, 'message' => 'Invalid status']);
        }

        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);

        if ($stmt->rowCount() === 0) {
            respond(404, ['success' => false, 'message' => 'Order not found or status unchanged']);
        }

        respond(200, ['success' => true, 'message' => 'Order status updated']);
    }

    respond(405, ['success' => false, 'message' => 'Method not allowed']);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>