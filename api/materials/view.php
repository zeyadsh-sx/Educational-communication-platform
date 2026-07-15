<?php
/**
 * API — عرض مواد كورس معين
 * GET /api/materials/view.php?course_id=X
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$course_id = (int)($_GET['course_id'] ?? 0);
if (!$course_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

$userId    = (int)($_SESSION['user_id']   ?? 0);
$userType  = $_SESSION['user_type'] ?? '';

$pdo = getDB();

// Check access
if ($userType === 'student') {
    $accessStmt = $pdo->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND student_id = ? AND status = 'active'");
} elseif ($userType === 'professor') {
    $accessStmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND professor_id = ?");
} else {
    // admin has full access
    $accessStmt = $pdo->prepare("SELECT id FROM courses WHERE id = ?");
    $accessStmt->execute([$course_id]);
    $access = $accessStmt->fetch();
    if (!$access) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        exit;
    }
    goto fetch_materials;
}

$accessStmt->execute([$course_id, $userId]);
if (!$accessStmt->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

fetch_materials:
$stmt = $pdo->prepare("
    SELECT m.id, m.title, m.description, m.file_name, m.file_type,
           m.upload_date, u.full_name AS uploaded_by_name, c.course_name
    FROM materials m
    LEFT JOIN users u ON m.uploaded_by = u.id
    JOIN courses c ON m.course_id = c.id
    WHERE m.course_id = ?
    ORDER BY m.upload_date DESC
");
$stmt->execute([$course_id]);
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'materials' => $materials]);
