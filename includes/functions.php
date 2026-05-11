<?php
ob_start();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserType()
{
    return $_SESSION['user_type'] ?? null;
}

function isProfessor()
{
    return getCurrentUserType() === 'professor';
}

function isStudent()
{
    return getCurrentUserType() === 'student';
}

function getBaseUrl()
{
    static $basePath = null;
    if ($basePath === null) {
        $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        $appRoot = str_replace('\\', '/', dirname(__DIR__));
        $basePath = str_replace($docRoot, '', $appRoot);
        if (empty($basePath) || $basePath == '/') {
            $basePath = '';
        }
    }
    return $basePath;
}

function redirect($url)
{
    // Ensure no output has been sent before sending header
    if (ob_get_length()) {
        // Clean any existing output buffers
        ob_end_clean();
    }
    if (strpos($url, '/') === 0) {
        $url = getBaseUrl() . $url;
    }
    header("Location: $url");
    exit;
}

function showError($message)
{
    return '<div class="alert alert-error">' . htmlspecialchars($message) . '</div>';
}

function showSuccess($message)
{
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatDate($date)
{
    return date('Y-m-d H:i', strtotime($date));
}

function getPendingQuestionsCount($userId, $userType)
{
    $pdo = getDB();
    if ($userType === 'professor') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE professor_id = ? AND status = 'pending'");
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE student_id = ? AND status = 'pending'");
    }
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getUpcomingAppointmentsCount($userId, $userType)
{
    $pdo = getDB();
    if ($userType === 'professor') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE professor_id = ? AND (status = 'pending' OR date_time > NOW())");
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE student_id = ? AND (status = 'pending' OR date_time > NOW())");
    }
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getStudentMaterialsCount($userId)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM materials m 
        JOIN course_enrollments ce ON m.course_id = ce.course_id 
        WHERE ce.student_id = ? AND ce.status = 'active'
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getUnreadNotificationsCount($userId)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

// Language System
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ar'; // Default language
}

function __($key)
{
    global $lang_data;
    if (!isset($lang_data)) {
        $lang = $_SESSION['lang'] ?? 'ar';
        $lang_path = __DIR__ . "/../lang/$lang.php";
        if (file_exists($lang_path)) {
            $lang_data = require $lang_path;
        } else {
            $lang_data = [];
        }
    }
    return $lang_data[$key] ?? $key;
}

function getRecentQuestions($userId, $userType, $limit = 5)
{
    $pdo = getDB();
    if ($userType === 'professor') {
        $stmt = $pdo->prepare("SELECT q.*, c.course_name, u.full_name as student_name 
                               FROM questions q 
                               JOIN courses c ON q.course_id = c.id 
                               LEFT JOIN users u ON q.student_id = u.id 
                               WHERE q.professor_id = ? 
                               ORDER BY q.created_at DESC LIMIT ?");
    } else {
        $stmt = $pdo->prepare("SELECT q.*, c.course_name, u.full_name as professor_name 
                               FROM questions q 
                               JOIN courses c ON q.course_id = c.id 
                               LEFT JOIN users u ON q.professor_id = u.id 
                               WHERE q.student_id = ? 
                               ORDER BY q.created_at DESC LIMIT ?");
    }
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUpcomingAppointmentsList($userId, $userType, $limit = 5)
{
    $pdo = getDB();
    if ($userType === 'professor') {
        $stmt = $pdo->prepare("SELECT a.*, u.full_name as student_name 
                               FROM appointments a 
                               LEFT JOIN users u ON a.student_id = u.id 
                               WHERE a.professor_id = ? AND (a.date_time >= NOW() OR a.status = 'pending') 
                               ORDER BY a.date_time ASC LIMIT ?");
    } else {
        $stmt = $pdo->prepare("SELECT a.*, u.full_name as professor_name 
                               FROM appointments a 
                               LEFT JOIN users u ON a.professor_id = u.id 
                               WHERE a.student_id = ? AND (a.date_time >= NOW() OR a.status = 'pending') 
                               ORDER BY a.date_time ASC LIMIT ?");
    }
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getRecentNotifications($userId, $limit = 5)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Security Functions
function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token)
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Analytics Functions
function getProfessorAnalytics($userId)
{
    $pdo = getDB();
    $analytics = ['courses' => [], 'students_count' => [], 'questions' => ['pending' => 0, 'answered' => 0]];

    // Students per course
    $stmt1 = $pdo->prepare("SELECT c.course_name, COUNT(ce.student_id) as student_count 
                           FROM courses c 
                           LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.status = 'active'
                           WHERE c.professor_id = ? 
                           GROUP BY c.id");
    $stmt1->execute([$userId]);
    while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        $analytics['courses'][] = $row['course_name'];
        $analytics['students_count'][] = $row['student_count'];
    }

    // Questions status
    $stmt2 = $pdo->prepare("SELECT status, COUNT(*) as count FROM questions WHERE professor_id = ? GROUP BY status");
    $stmt2->execute([$userId]);
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        if ($row['status'] == 'pending') $analytics['questions']['pending'] = $row['count'];
        else $analytics['questions']['answered'] += $row['count']; // Combine answered statuses
    }

    return $analytics;
}

function getStudentAnalytics($userId)
{
    $pdo = getDB();
    $analytics = ['courses' => [], 'materials_count' => [], 'appointments' => ['pending' => 0, 'completed' => 0, 'cancelled' => 0]];

    // Materials per course
    $stmt1 = $pdo->prepare("SELECT c.course_name, COUNT(m.id) as material_count 
                           FROM course_enrollments ce 
                           JOIN courses c ON ce.course_id = c.id 
                           LEFT JOIN materials m ON c.id = m.course_id 
                           WHERE ce.student_id = ? AND ce.status = 'active'
                           GROUP BY c.id");
    $stmt1->execute([$userId]);
    while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        $analytics['courses'][] = $row['course_name'];
        $analytics['materials_count'][] = $row['material_count'];
    }

    // Appointments status
    $stmt2 = $pdo->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE student_id = ? GROUP BY status");
    $stmt2->execute([$userId]);
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        if ($row['status'] == 'pending') $analytics['appointments']['pending'] = $row['count'];
        else if ($row['status'] == 'completed') $analytics['appointments']['completed'] = $row['count'];
        else $analytics['appointments']['cancelled'] += $row['count'];
    }

    return $analytics;
}
