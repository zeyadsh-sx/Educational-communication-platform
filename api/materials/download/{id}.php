<?php
include "../../../config/database.php";

$database = new Database();
$conn = $database->connect();

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM materials WHERE id=?");
$stmt->execute([$id]);

$file = $stmt->fetch();

header("Content-Disposition: attachment; filename=".$file['file_name']);
readfile($file['file_path']);
?>
