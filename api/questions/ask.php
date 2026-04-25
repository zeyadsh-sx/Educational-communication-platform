<?php
include "../../config/database.php";
include "../../includes/auth.php";

$database = new Database();
$conn = $database->connect();

session_start();

$conn->prepare("INSERT INTO questions(course_id,user_id,question)
VALUES (?,?,?)")
->execute([
$_POST['course_id'],
$_SESSION['user']['id'],
$_POST['question']
]);

echo json_encode(["status"=>"sent"]);
?>
