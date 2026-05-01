<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$query = $_GET['q'] ?? '';
$type = $_GET['type'] ?? 'all'; // all, courses, materials

if (empty(trim($query))) {
    echo json_encode(['results' => []]);
    exit;
}

$pdo = getDB();
$userId = getCurrentUserId();
$userType = getCurrentUserType();
$results = [];

try {
    $searchPattern = '%' . $query . '%';
    
    // Search Courses
    if ($type === 'all' || $type === 'courses') {
        if ($userType === 'student') {
            // Students can search all available courses
            $stmt = $pdo->prepare("SELECT id, course_name as title, course_code as subtitle, 'course' as type 
                                   FROM courses 
                                   WHERE course_name LIKE ? OR course_code LIKE ? LIMIT 5");
        } else {
            // Professors search their own courses
            $stmt = $pdo->prepare("SELECT id, course_name as title, course_code as subtitle, 'course' as type 
                                   FROM courses 
                                   WHERE professor_id = ? AND (course_name LIKE ? OR course_code LIKE ?) LIMIT 5");
            $stmt->bindValue(1, $userId);
            $searchPatternIndex = 2;
        }
        
        if ($userType === 'student') {
            $stmt->execute([$searchPattern, $searchPattern]);
        } else {
            $stmt->execute([$userId, $searchPattern, $searchPattern]);
        }
        
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($courses as &$c) {
            $c['url'] = "/courses/view.php?id=" . $c['id'];
        }
        $results = array_merge($results, $courses);
    }
    
    // Search Materials
    if ($type === 'all' || $type === 'materials') {
        if ($userType === 'student') {
            // Students search materials in their enrolled courses
            $stmt = $pdo->prepare("SELECT m.id, m.title, c.course_name as subtitle, 'material' as type 
                                   FROM materials m
                                   JOIN course_enrollments ce ON m.course_id = ce.course_id
                                   JOIN courses c ON m.course_id = c.id
                                   WHERE ce.student_id = ? AND (m.title LIKE ? OR m.file_name LIKE ?) LIMIT 5");
            $stmt->execute([$userId, $searchPattern, $searchPattern]);
        } else {
            // Professors search their own materials
            $stmt = $pdo->prepare("SELECT m.id, m.title, c.course_name as subtitle, 'material' as type 
                                   FROM materials m
                                   JOIN courses c ON m.course_id = c.id
                                   WHERE m.professor_id = ? AND (m.title LIKE ? OR m.file_name LIKE ?) LIMIT 5");
            $stmt->execute([$userId, $searchPattern, $searchPattern]);
        }
        
        $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($materials as &$m) {
            $m['url'] = "/materials/download.php?id=" . $m['id'];
        }
        $results = array_merge($results, $materials);
    }
    
    echo json_encode(['status' => 'success', 'results' => $results]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
