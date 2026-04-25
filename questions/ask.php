<?php
include "../config/database.php";
include "../includes/auth.php";

$database = new Database();
$conn = $database->connect();

session_start();

$course_id = $_POST['course_id'];
$question = $_POST['question'];

$conn->prepare("INSERT INTO questions(course_id,user_id,question)
VALUES (?,?,?)")
->execute([$course_id,$_SESSION['user']['id'],$question]);

echo "Question Sent";
?>