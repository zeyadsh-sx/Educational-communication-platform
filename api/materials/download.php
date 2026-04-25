<?php
include "../../config/database.php";

$database = new Database();
$conn = $database->connect();

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM materials WHERE id=?");
$stmt->execute([$id]);

$file = $stmt->fetch();

if ($file) {
    header("Content-Disposition: attachment; filename=".$file['file_name']);
    readfile($file['file_path']);
} else {
    http_response_code(404);
    echo json_encode(["error" => "File not found"]);
}
?>
