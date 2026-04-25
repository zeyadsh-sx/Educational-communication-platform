<?php
header("Content-Type: application/json");

include "../config/database.php";
include "../includes/auth.php";

$database = new Database();
$conn = $database->connect();

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {

    loginUser([
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "type" => $user['type']
    ]);

    echo json_encode([
        "success" => true,
        "user" => $_SESSION['user']
    ]);

} else {
    echo json_encode(["error" => "Invalid credentials"]);
}
?>