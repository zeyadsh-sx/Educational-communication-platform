<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireLogin();

$database = new Database();
$conn = $database->connect();

$professor_id = $_GET['professor_id'] ?? null;
$date = $_GET['date'] ?? null;

$user_id = $_SESSION['user']['id'];
$user_type = $_SESSION['user']['user_type'];

$query = "
    SELECT a.*, 
    
           u_student.full_name as student_name,
           u_professor.full_name as professor_name,
           c.course_name
    FROM appointments a
    LEFT JOIN users u_student ON a.student_id = u_student.id
    LEFT JOIN users u_professor ON a.professor_id = u_professor.id
    LEFT JOIN courses c ON a.course_id = c.id
";

$conditions = [];
$params = [];

if ($professor_id) {
  $conditions[] = "a.professor_id = ?";
  $params[] = $professor_id;
}

if ($date) {
  $conditions[] = "DATE(a.date_time) = ?";
  $params[] = $date;
}

// If student, only show their appointments
if ($user_type === 'student') {
  $conditions[] = "a.student_id = ?";
  $params[] = $user_id;
}
// If professor, only show their appointments
elseif ($user_type === 'professor') {
  $conditions[] = "a.professor_id = ?";
  $params[] = $user_id;
}

if (!empty($conditions)) {
  $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY a.date_time DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);

$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  "success" => true,
  "appointments" => $appointments
]);
