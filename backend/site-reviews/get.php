<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/common.php';

try {
    $db = getDBConnection();
    ensureSiteReviewsSchema($db);

    $stmt = $db->query("SELECT sr.id, sr.user_name, sr.user_email, sr.user_id, sr.rating, sr.comment, sr.created_at, COALESCE(u.username, sr.user_name) AS display_name
                        FROM site_reviews sr
                        LEFT JOIN users u ON sr.user_id = u.id
                        ORDER BY sr.created_at DESC
                        LIMIT 50");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $summaryStmt = $db->query("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM site_reviews");
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: ['avg_rating' => null, 'total_reviews' => 0];

    siteReviewsRespond(200, [
        'success' => true,
        'data' => [
            'reviews' => $reviews,
            'average_rating' => $summary['avg_rating'] !== null ? round((float)$summary['avg_rating'], 1) : 0,
            'total_reviews' => intval($summary['total_reviews'])
        ]
    ]);
} catch (PDOException $e) {
    siteReviewsRespond(500, ['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    siteReviewsRespond(500, ['success' => false, 'message' => 'Error occurred']);
}
?>
