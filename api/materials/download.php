<?php
/**
 * API — تحميل مادة دراسية (JSON metadata — التحميل الفعلي في materials/download.php)
 * GET /api/materials/download.php?id=X
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId   = (int)($_SESSION['user_id']   ?? 0);
$userType = $_SESSION['user_type'] ?? '';
$matId    = (int)($_GET['id'] ?? 0);

if (!$matId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Material ID is required']);
    exit;
}

$pdo  = getDB();
$stmt = $pdo->prepare("SELECT m.*, c.professor_id FROM materials m JOIN courses c ON m.course_id=c.id WHERE m.id=?");
$stmt->execute([$matId]);
$material = $stmt->fetch();

if (!$material) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Material not found']);
    exit;
}

// Check access
$hasAccess = false;
if ($userType === 'professor') {
    $hasAccess = ((int)$material['professor_id'] === $userId);
} elseif ($userType === 'admin') {
    $hasAccess = true;
} else {
    $enr = $pdo->prepare("SELECT id FROM course_enrollments WHERE course_id=? AND student_id=? AND status='active'");
    $enr->execute([$material['course_id'], $userId]);
    $hasAccess = (bool)$enr->fetch();
}

if (!$hasAccess) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Return download URL pointing to the real download endpoint
$base = getBaseUrl();
echo json_encode([
    'success'      => true,
    'download_url' => $base . '/materials/download.php?id=' . $matId,
    'file_name'    => $material['file_name'],
    'file_type'    => $material['file_type'] ?? null,
]);
