<?php
include "../config/database.php";
include "../includes/auth.php";

$database = new Database();
$conn = $database->connect();

session_start();

$id = $_POST['question_id'];
$answer = $_POST['answer'];

$conn->prepare("UPDATE questions SET answer=? WHERE id=?")
->execute([$answer,$id]);

echo "Answered";
?>
