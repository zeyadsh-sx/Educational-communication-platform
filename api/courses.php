<?php
/**
 * Courses API - واجهة برمجة تطبيقات الكورسات
 * 
 * Endpoints:
 * - GET    /api/courses           - قائمة الكورسات
 * - POST   /api/courses           - إنشاء كورس جديد
 * - GET    /api/courses/{id}      - تفاصيل الكورس
 * - PUT    /api/courses/{id}      - تحديث الكورس
 * - DELETE /api/courses/{id}      - حذف الكورس
 * - POST   /api/courses/{id}/join - انضمام للكورس
 * - DELETE /api/courses/{id}/join - إلغاء الانضمام
 * - GET    /api/courses/{id}/students - قائمة الطلبة
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/course_functions.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace('/api/courses', '', $path);
$path = trim($path, '/');

// Parse path to get course ID and sub-resources
$parts = $path ? explode('/', $path) : [];
$courseId = !empty($parts[0]) && is_numeric($parts[0]) ? (int)$parts[0] : null;
$subResource = !empty($parts[1]) ? $parts[1] : null;

// Simple auth check (should be improved in production)
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

function requireProfessor() {
    requireAuth();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'professor') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - Professor access required']);
        exit;
    }
}

function requireStudent() {
    requireAuth();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - Student access required']);
        exit;
    }
}

// Route handling
try {
    switch ($method) {
        case 'GET':
            if ($courseId && $subResource === 'students') {
                // GET /api/courses/{id}/students
                requireAuth();
                $students = getCourseStudents($courseId);
                echo json_encode(['success' => true, 'students' => $students]);
            } elseif ($courseId) {
                // GET /api/courses/{id}
                $course = getCourseById($courseId);
                if (!$course) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Course not found']);
                } else {
                    echo json_encode(['success' => true, 'course' => $course]);
                }
            } else {
                // GET /api/courses
                $search = $_GET['search'] ?? '';
                $professorId = $_GET['professor_id'] ?? null;
                
                // If student, only show enrolled courses
                if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student' && isset($_SESSION['user_id'])) {
                    $courses = getStudentCourses($_SESSION['user_id']);
                } else {
                    $courses = getCourses($professorId, $search);
                }
                
                echo json_encode(['success' => true, 'courses' => $courses, 'count' => count($courses)]);
            }
            break;
            
        case 'POST':
            if ($courseId && $subResource === 'join') {
                // POST /api/courses/{id}/join
                requireStudent();
                $result = enrollStudent($courseId, $_SESSION['user_id']);
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
            } else {
                // POST /api/courses - Create new course
                requireProfessor();
                
                $data = json_decode(file_get_contents('php://input'), true);
                $courseName = $data['course_name'] ?? '';
                $courseCode = $data['course_code'] ?? '';
                $description = $data['description'] ?? '';
                
                if (empty($courseName) || empty($courseCode)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Course name and code are required']);
                    exit;
                }
                
                if (courseCodeExists($courseCode)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Course code already exists']);
                    exit;
                }
                
                $result = createCourse($courseName, $courseCode, $_SESSION['user_id'], $description);
                http_response_code($result['success'] ? 201 : 400);
                echo json_encode($result);
            }
            break;
            
        case 'PUT':
            // PUT /api/courses/{id}
            requireProfessor();
            
            if (!$courseId) {
                http_response_code(400);
                echo json_encode(['error' => 'Course ID required']);
                exit;
            }
            
            $course = getCourseById($courseId);
            if (!$course || $course['professor_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['error' => 'Not authorized to update this course']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $courseName = $data['course_name'] ?? $course['course_name'];
            $description = $data['description'] ?? $course['description'];
            
            $result = updateCourse($courseId, $courseName, $description);
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            if ($courseId && $subResource === 'students' && !empty($parts[2])) {
                // DELETE /api/courses/{id}/students/{student_id}
                requireProfessor();
                
                $studentId = (int)$parts[2];
                $course = getCourseById($courseId);
                
                if (!$course || $course['professor_id'] != $_SESSION['user_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Not authorized to remove students from this course']);
                    exit;
                }
                
                $result = unenrollStudent($courseId, $studentId);
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
            } else {
                // DELETE /api/courses/{id}
                requireProfessor();
                
                if (!$courseId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Course ID required']);
                    exit;
                }
                
                $course = getCourseById($courseId);
                if (!$course || $course['professor_id'] != $_SESSION['user_id']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Not authorized to delete this course']);
                    exit;
                }
                
                $result = deleteCourse($courseId);
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
