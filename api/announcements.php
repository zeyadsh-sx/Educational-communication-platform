<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$database = new Database();
$conn = $database->connect();

session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {

  $title = $_POST['title'];
  $content = $_POST['content'];
  $course_id = $_POST['course_id'];

  $conn->prepare("INSERT INTO announcements(course_id,title,content,created_by)
VALUES (?,?,?,?)")
    ->execute([$course_id, $title, $content, $_SESSION['user']['id']]);

  echo json_encode(["status" => "created"]);
} else {

  $course_id = $_GET['course_id'];

  $stmt = $conn->prepare("SELECT * FROM announcements WHERE course_id=?");
  $stmt->execute([$course_id]);

  echo json_encode($stmt->fetchAll());
}
