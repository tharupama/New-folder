<?php
require_once __DIR__ . '/../config/database.php';

function siteReviewsRespond($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function siteReviewsColumnExists($db, $tableName, $columnName) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$tableName, $columnName]);
    return (int)$stmt->fetchColumn() > 0;
}

function ensureSiteReviewsSchema($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS site_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_name VARCHAR(100) NOT NULL,
        user_email VARCHAR(100) DEFAULT NULL,
        user_id INT DEFAULT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    if (!siteReviewsColumnExists($db, 'site_reviews', 'user_email')) {
        $db->exec("ALTER TABLE site_reviews ADD COLUMN user_email VARCHAR(100) DEFAULT NULL AFTER user_name");
    }

    if (!siteReviewsColumnExists($db, 'site_reviews', 'user_id')) {
        $db->exec("ALTER TABLE site_reviews ADD COLUMN user_id INT DEFAULT NULL AFTER user_email");
    }
}
