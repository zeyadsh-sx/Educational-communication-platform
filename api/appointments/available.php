<?php
/**
 * API — عرض المواعيد / الأوقات المتاحة
 * GET /api/appointments/available.php?professor_id=X&date=Y
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

// Use flat session keys — consistent with the rest of the app
$userId   = (int)($_SESSION['user_id']   ?? 0);
$userType = $_SESSION['user_type'] ?? '';

$professorId = isset($_GET['professor_id']) ? (int)$_GET['professor_id'] : null;
$date        = $_GET['date'] ?? null;

$pdo        = getDB();
$conditions = [];
$params     = [];

// Build query conditions based on user role
if ($userType === 'student') {
    $conditions[] = 'a.student_id = ?';
    $params[]     = $userId;
} elseif ($userType === 'professor') {
    $conditions[] = 'a.professor_id = ?';
    $params[]     = $userId;
}
// admin sees everything — no extra condition

if ($professorId) {
    $conditions[] = 'a.professor_id = ?';
    $params[]     = $professorId;
}

if ($date) {
    $conditions[] = 'DATE(a.date_time) = ?';
    $params[]     = $date;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$stmt = $pdo->prepare("
    SELECT a.*,
           us.full_name  AS student_name,
           up.full_name  AS professor_name,
           c.course_name
    FROM appointments a
    LEFT JOIN users us ON a.student_id  = us.id
    LEFT JOIN users up ON a.professor_id = up.id
    LEFT JOIN courses c ON a.course_id  = c.id
    $where
    ORDER BY a.date_time DESC
");
$stmt->execute($params);

echo json_encode([
    'success'      => true,
    'appointments' => $stmt->fetchAll(PDO::FETCH_ASSOC),
]);
