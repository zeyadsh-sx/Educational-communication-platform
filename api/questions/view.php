<?php
/**
 * API — عرض أسئلة كورس
 * GET /api/questions/view.php?course_id=X
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

$userId   = (int)($_SESSION['user_id']   ?? 0);
$userType = $_SESSION['user_type'] ?? '';
$courseId = (int)($_GET['course_id'] ?? 0);

if (!$courseId) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Course ID is required']); exit; }

$pdo = getDB();

// Check access
if ($userType === 'student') {
    $acc = $pdo->prepare("SELECT id FROM course_enrollments WHERE course_id=? AND student_id=? AND status='active'");
} elseif ($userType === 'professor') {
    $acc = $pdo->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
} else {
    // admin — full access
    $acc = null;
}

if ($acc) {
    $acc->execute([$courseId, $userId]);
    if (!$acc->fetch()) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Access denied']); exit; }
}

$stmt = $pdo->prepare("
    SELECT q.id, q.question_text, q.answer_text, q.status, q.created_at, q.answered_at,
           us.full_name AS student_name,
           up.full_name AS professor_name,
           c.course_name
    FROM questions q
    LEFT JOIN users us ON q.student_id  = us.id
    LEFT JOIN users up ON q.professor_id = up.id
    JOIN courses c ON q.course_id = c.id
    WHERE q.course_id = ?
    ORDER BY q.created_at DESC
");
$stmt->execute([$courseId]);
echo json_encode(['success'=>true, 'questions'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
