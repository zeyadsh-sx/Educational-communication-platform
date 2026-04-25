<?php
include "../../config/database.php";

$database = new Database();
$conn = $database->connect();

$conn->prepare("UPDATE questions SET answer=? WHERE id=?")
->execute([$_POST['answer'], $_POST['question_id']]);

echo json_encode(["status"=>"answered"]);
?>
