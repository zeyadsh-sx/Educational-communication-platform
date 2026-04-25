<?php
include "../../config/database.php";

$database = new Database();
$conn = $database->connect();

$course_id = $_GET['course_id'];

$stmt = $conn->prepare("SELECT * FROM materials WHERE course_id=?");
$stmt->execute([$course_id]);

echo json_encode($stmt->fetchAll());
?>
