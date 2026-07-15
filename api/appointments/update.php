<?php
/**
 * API — تحديث حالة موعد
 * POST /api/appointments/update.php
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

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['appointment_id'], $data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$appointment_id = (int)$data['appointment_id'];
$status         = $data['status'];

// Validate status value
$allowedStatuses = ['confirmed', 'cancelled', 'completed', 'pending'];
if (!in_array($status, $allowedStatuses, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

$pdo = getDB();

// Verify the user owns this appointment
if ($user_type === 'professor') {
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND professor_id = ?");
} elseif ($user_type === 'admin') {
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch();
    if (!$appointment) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        exit;
    }
    // Admin path: update directly
    $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?")->execute([$status, $appointment_id]);
    echo json_encode(['success' => true]);
    exit;
} else {
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND student_id = ?");
}

$stmt->execute([$appointment_id, $user_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$updateStmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
$result     = $updateStmt->execute([$status, $appointment_id]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
