<?php
include "../config/database.php";
include "../includes/auth.php";

$database = new Database();
$conn = $database->connect();

session_start();

$title = $_POST['title'];
$content = $_POST['content'];
$course_id = $_POST['course_id'];

$stmt = $conn->prepare("INSERT INTO announcements(course_id,title,content,created_by)
VALUES (?,?,?,?)");

$stmt->execute([$course_id,$title,$content,$_SESSION['user']['id']]);

echo "Announcement Created";
?>