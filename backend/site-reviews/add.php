<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/common.php';

try {
    $db = getDBConnection();
    ensureSiteReviewsSchema($db);

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        siteReviewsRespond(400, ['success' => false, 'message' => 'Invalid JSON input']);
    }

    $userName = trim($input['user_name'] ?? '');
    $userEmail = trim($input['user_email'] ?? '');
    $userIdRaw = $input['user_id'] ?? null;
    $userId = is_numeric($userIdRaw) ? intval($userIdRaw) : null;
    $rating = intval($input['rating'] ?? 0);
    $comment = trim($input['comment'] ?? '');

    if ($userName === '') {
        siteReviewsRespond(400, ['success' => false, 'message' => 'Name is required']);
    }

    if ($rating < 1 || $rating > 5) {
        siteReviewsRespond(400, ['success' => false, 'message' => 'Rating must be between 1 and 5']);
    }

    if (strlen($comment) < 10) {
        siteReviewsRespond(400, ['success' => false, 'message' => 'Comment must be at least 10 characters']);
    }

    if ($userEmail === '' && $userId !== null) {
        $emailLookup = $db->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
        $emailLookup->execute([$userId]);
        $matchedEmail = trim((string)$emailLookup->fetchColumn());
        if ($matchedEmail !== '') {
            $userEmail = $matchedEmail;
        }
    }

    if ($userId === null && $userEmail !== '') {
        $idLookup = $db->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $idLookup->execute([$userEmail]);
        $matchedUserId = $idLookup->fetchColumn();
        if ($matchedUserId) {
            $userId = intval($matchedUserId);
        }
    }

    $insert = $db->prepare('INSERT INTO site_reviews (user_name, user_email, user_id, rating, comment) VALUES (?, ?, ?, ?, ?)');
    $insert->execute([
        $userName,
        $userEmail !== '' ? $userEmail : null,
        $userId !== null ? $userId : null,
        $rating,
        $comment
    ]);

    siteReviewsRespond(200, [
        'success' => true,
        'message' => 'Site review submitted successfully',
        'review_id' => $db->lastInsertId()
    ]);
} catch (PDOException $e) {
    siteReviewsRespond(500, ['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    siteReviewsRespond(500, ['success' => false, 'message' => 'Error occurred']);
}
?>
