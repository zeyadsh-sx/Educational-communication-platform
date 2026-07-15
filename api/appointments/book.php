<?php
/**
 * API — حجز موعد
 * POST /api/appointments/book.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Must be logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Use the flat session keys set by login.php / functions.php
$user_id   = (int)($_SESSION['user_id']   ?? 0);
$user_type = $_SESSION['user_type'] ?? '';

if ($user_type !== 'student') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only students can book appointments']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['professor_id'], $data['date_time'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$professor_id = (int)$data['professor_id'];
$date_time    = $data['date_time'];
$notes        = trim($data['notes']     ?? '');
$course_id    = isset($data['course_id']) ? (int)$data['course_id'] : null;

$pdo = getDB();

// Verify professor exists
$profStmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'professor'");
$profStmt->execute([$professor_id]);
if (!$profStmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Professor not found']);
    exit;
}

// Check for scheduling conflict (±60 minutes window)
$conflictStmt = $pdo->prepare("
    SELECT id FROM appointments
    WHERE professor_id = ?
      AND DATE(date_time) = DATE(?)
      AND ABS(TIMESTAMPDIFF(MINUTE, date_time, ?)) < 60
      AND status IN ('pending', 'confirmed')
");
$conflictStmt->execute([$professor_id, $date_time, $date_time]);
if ($conflictStmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Time slot not available']);
    exit;
}

// Insert appointment
$insertStmt = $pdo->prepare("
    INSERT INTO appointments (student_id, professor_id, course_id, date_time, notes, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$result = $insertStmt->execute([$user_id, $professor_id, $course_id, $date_time, $notes]);

if ($result) {
    // Award gamification points if available
    if (file_exists(__DIR__ . '/../../includes/gamification.php')) {
        require_once __DIR__ . '/../../includes/gamification.php';
        if (function_exists('awardPoints')) {
            awardPoints($user_id, 'book_appointment');
        }
    }

    $newId = (int)$pdo->lastInsertId();
    echo json_encode(['success' => true, 'appointment_id' => $newId]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to book appointment']);
}
