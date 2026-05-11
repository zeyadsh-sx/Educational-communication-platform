<?php

require_once __DIR__ . '/../config/database.php';

function createCourse($courseName, $courseCode, $professorId, $description = '')
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        INSERT INTO courses (course_name, course_code, professor_id, description, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");

    try {
        $stmt->execute([$courseName, $courseCode, $professorId, $description]);
        return [
            'success' => true,
            'course_id' => $pdo->lastInsertId(),
            'message' => 'Course created successfully'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error creating course: ' . $e->getMessage()
        ];
    }
}

function getCourses($professorId = null, $search = '', $limit = 20, $offset = 0)
{
    $pdo = getDB();

    $sql = "SELECT c.*, u.full_name as professor_name 
            FROM courses c 
            LEFT JOIN users u ON c.professor_id = u.id 
            WHERE 1=1";
    $params = [];

    if ($professorId) {
        $sql .= " AND c.professor_id = ?";
        $params[] = $professorId;
    }

    if ($search) {
        $sql .= " AND (c.course_name LIKE ? OR c.course_code LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function getCourseById($courseId)
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name as professor_name 
        FROM courses c 
        LEFT JOIN users u ON c.professor_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$courseId]);

    return $stmt->fetch();
}

function updateCourse($courseId, $courseName, $description)
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        UPDATE courses 
        SET course_name = ?, description = ? 
        WHERE id = ?
    ");

    try {
        $stmt->execute([$courseName, $description, $courseId]);
        return ['success' => true, 'message' => 'Course updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error updating course'];
    }
}

function deleteCourse($courseId)
{
    $pdo = getDB();

    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");

    try {
        $stmt->execute([$courseId]);
        return ['success' => true, 'message' => 'Course deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error deleting course'];
    }
}

function enrollStudent($courseId, $studentId)
{
    $pdo = getDB();

    $course = getCourseById($courseId);
    if (!$course) {
        return ['success' => false, 'message' => 'Course not available'];
    }

    $stmt = $pdo->prepare("SELECT user_type FROM users WHERE id = ?");
    $stmt->execute([$studentId]);
    $user = $stmt->fetch();

    if (!$user || $user['user_type'] !== 'student') {
        return ['success' => false, 'message' => 'You must be a student to enroll in a course'];
    }

    $stmt = $pdo->prepare("
        SELECT id FROM course_enrollments 
        WHERE course_id = ? AND student_id = ?
    ");
    $stmt->execute([$courseId, $studentId]);

    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'You are already enrolled in this course'];
    }

    $stmt = $pdo->prepare("
        INSERT INTO course_enrollments (course_id, student_id, enrolled_at, status)
        VALUES (?, ?, NOW(), 'active')
    ");

    try {
        $stmt->execute([$courseId, $studentId]);

        // Send notification to professor
        $studentStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
        $studentStmt->execute([$studentId]);
        $studentName = $studentStmt->fetchColumn();

        include_once 'notification_functions.php';
        sendNotification($course['professor_id'], "طالب جديد ({$studentName}) انضم لكورس '{$course['course_name']}'");

        return ['success' => true, 'message' => 'Student enrolled in course successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error enrolling student in course'];
    }
}

function unenrollStudent($courseId, $studentId)
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        DELETE FROM course_enrollments 
        WHERE course_id = ? AND student_id = ?
    ");

    try {
        $stmt->execute([$courseId, $studentId]);
        return ['success' => true, 'message' => 'Student removed from course successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error removing student from course'];
    }
}

function getCourseStudents($courseId, $status = 'active')
{
    $pdo = getDB();

    $sql = "SELECT u.id, u.username, u.email, u.full_name, u.user_type, 
                   ce.enrolled_at, ce.status
            FROM course_enrollments ce
            JOIN users u ON ce.student_id = u.id
            WHERE ce.course_id = ?";

    if ($status) {
        $sql .= " AND ce.status = ?";
    }

    $sql .= " ORDER BY ce.enrolled_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($status ? [$courseId, $status] : [$courseId]);

    return $stmt->fetchAll();
}

function getStudentCourses($studentId)
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name as professor_name, ce.enrolled_at, ce.status
        FROM course_enrollments ce
        JOIN courses c ON ce.course_id = c.id
        LEFT JOIN users u ON c.professor_id = u.id
        WHERE ce.student_id = ? AND ce.status = 'active'
        ORDER BY ce.enrolled_at DESC
    ");
    $stmt->execute([$studentId]);

    return $stmt->fetchAll();
}

function isStudentEnrolled($courseId, $studentId)
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT id FROM course_enrollments 
        WHERE course_id = ? AND student_id = ? AND status = 'active'
    ");
    $stmt->execute([$courseId, $studentId]);

    return (bool) $stmt->fetch();
}

function updateStudentStatus($courseId, $studentId, $status)
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        UPDATE course_enrollments 
        SET status = ? 
        WHERE course_id = ? AND student_id = ?
    ");

    try {
        $stmt->execute([$status, $courseId, $studentId]);
        return ['success' => true, 'message' => 'Student status updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error updating student status'];
    }
}

function getCourseStudentCount($courseId)
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM course_enrollments 
        WHERE course_id = ? AND status = 'active'
    ");
    $stmt->execute([$courseId]);

    $result = $stmt->fetch();
    return $result['count'];
}

function searchCourses($query, $limit = 20)
{
    return getCourses(null, $query, $limit);
}

function courseCodeExists($courseCode, $excludeId = null)
{
    $pdo = getDB();

    $sql = "SELECT id FROM courses WHERE course_code = ?";
    $params = [$courseCode];

    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

function getProfessorCourses($professorId)
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT c.*,
               (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id AND ce.status = 'active') as student_count,
               (SELECT COUNT(*) FROM materials m WHERE m.course_id = c.id) as material_count,
               (SELECT COUNT(*) FROM questions q WHERE q.course_id = c.id) as question_count
        FROM courses c
        WHERE c.professor_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$professorId]);

    return $stmt->fetchAll();
}
