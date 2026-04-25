<?php
include "../config/database.php";
include "../includes/auth.php";

$database = new Database();
$conn = $database->connect();

session_start();

$course_id = $_POST['course_id'];
$file = $_FILES['file'];

$name = time() . "_" . $file['name'];
$path = "../uploads/" . $name;

move_uploaded_file($file['tmp_name'], $path);

$stmt = $conn->prepare("INSERT INTO materials(course_id,file_name,file_path,uploaded_by)
VALUES (?,?,?,?)");

$stmt->execute([$course_id, $file['name'], $path, $_SESSION['user']['id']]);

echo "Uploaded";
?>