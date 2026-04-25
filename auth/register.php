<?php
header("Content-Type: application/json");

include "../config/database.php";
include "../includes/auth.php";

$database = new Database();
$conn = $database->connect();

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$type = $data['type'] ?? 'student';

if (!$name || !$email || !$password) {
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, email, password, type) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashed, $type);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "User already exists"]);
}
?>