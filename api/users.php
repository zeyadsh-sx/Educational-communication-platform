<?php
header("Content-Type: application/json");

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$database = new Database();
$conn = $database->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case "GET":
        requireLogin();
        echo json_encode(["user" => currentUser()]);
        break;

    case "POST":
        $action = $_GET['action'] ?? '';

        if ($action === 'register') {
            $data = json_decode(file_get_contents("php://input"), true);

            $username = $data['username'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $full_name = $data['full_name'] ?? '';
            $user_type = $data['user_type'] ?? 'student';

            if (!$username || !$email || !$password || !$full_name) {
                echo json_encode(["error" => "Missing required fields"]);
                exit;
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed, $full_name, $user_type]);

            if ($stmt) {
                echo json_encode(["success" => true, "message" => "User registered successfully"]);
            } else {
                echo json_encode(["error" => "Registration failed"]);
            }
        } elseif ($action === 'login') {
            $data = json_decode(file_get_contents("php://input"), true);

            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';

            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                loginUser([
                    "id" => $user['id'],
                    "username" => $user['username'],
                    "full_name" => $user['full_name'],
                    "email" => $user['email'],
                    "user_type" => $user['user_type']
                ]);

                echo json_encode([
                    "success" => true,
                    "user" => currentUser()
                ]);
            } else {
                echo json_encode(["error" => "Invalid credentials"]);
            }
        } elseif ($action === 'logout') {
            logoutUser();
            echo json_encode(["success" => true]);
        }
        break;

    case "PUT":
        requireLogin();

        $data = json_decode(file_get_contents("php://input"), true);
        $id = currentUser()['id'];
        $full_name = $data['full_name'] ?? '';
        $username = $data['username'] ?? '';

        $sql = "UPDATE users SET full_name = ?";
        $params = [$full_name];

        if ($username) {
            $sql .= ", username = ?";
            $params[] = $username;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $conn->prepare($sql);
        echo json_encode(["success" => $stmt->execute($params)]);
        break;
}
