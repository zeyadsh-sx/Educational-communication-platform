<?php
header("Content-Type: application/json");

include "../config/database.php";
include "../includes/auth.php";

$database = new Database();
$conn = $database->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case "GET":
        requireLogin();
        echo json_encode(["user" => currentUser()]);
        break;

    case "PUT":
        requireLogin();

        $data = json_decode(file_get_contents("php://input"), true);
        $id = currentUser()['id'];
        $name = $data['name'] ?? '';

        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);

        echo json_encode(["success" => $stmt->execute()]);
        break;

    case "POST":
        logoutUser();
        echo json_encode(["success" => true]);
        break;
}
?>