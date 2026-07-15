<?php
/**
 * API — إدارة المستخدمين (GET/POST/PUT)
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }
        echo json_encode(['user' => [
            'id'        => $_SESSION['user_id'],
            'username'  => $_SESSION['username']  ?? null,
            'full_name' => $_SESSION['full_name']  ?? null,
            'email'     => $_SESSION['email']      ?? null,
            'user_type' => $_SESSION['user_type']  ?? null,
        ]]);
        break;

    case 'POST':
        $action = $_GET['action'] ?? '';

        if ($action === 'register') {
            $data      = json_decode(file_get_contents('php://input'), true) ?? [];
            $username  = trim($data['username']  ?? '');
            $email     = trim($data['email']     ?? '');
            $password  = $data['password']        ?? '';
            $fullName  = trim($data['full_name']  ?? '');
            $userType  = in_array($data['user_type'] ?? '', ['student','professor']) ? $data['user_type'] : 'student';

            if (!$username || !$email || !$password || !$fullName) {
                http_response_code(400); echo json_encode(['error'=>'Missing required fields']); exit;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400); echo json_encode(['error'=>'Invalid email']); exit;
            }

            $pdo  = getDB();
            $chk  = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                http_response_code(409); echo json_encode(['error'=>'Email already registered']); exit;
            }

            $pdo->prepare("INSERT INTO users (username,email,password,full_name,user_type) VALUES (?,?,?,?,?)")
                ->execute([$username, $email, hashPassword($password), $fullName, $userType]);

            echo json_encode(['success'=>true, 'message'=>'User registered successfully']);

        } elseif ($action === 'login') {
            $data     = json_decode(file_get_contents('php://input'), true) ?? [];
            $email    = trim($data['email']    ?? '');
            $password = $data['password']       ?? '';

            $pdo  = getDB();
            $stmt = $pdo->prepare("SELECT id, username, full_name, email, user_type, password FROM users WHERE email=?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && verifyPassword($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                echo json_encode(['success'=>true, 'user'=>[
                    'id'        => $user['id'],
                    'full_name' => $user['full_name'],
                    'user_type' => $user['user_type'],
                ]]);
            } else {
                http_response_code(401); echo json_encode(['error'=>'Invalid credentials']);
            }

        } elseif ($action === 'logout') {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
            session_destroy();
            echo json_encode(['success'=>true]);
        }
        break;

    case 'PUT':
        if (!isLoggedIn()) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }
        $data     = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId   = (int)($_SESSION['user_id'] ?? 0);
        $fullName = trim($data['full_name'] ?? '');
        $username = trim($data['username']  ?? '');

        $sets   = ['full_name = ?'];
        $params = [$fullName];
        if ($username) { $sets[] = 'username = ?'; $params[] = $username; }
        $params[] = $userId;

        $pdo = getDB();
        $pdo->prepare("UPDATE users SET " . implode(', ', $sets) . " WHERE id=?")->execute($params);
        if ($fullName) $_SESSION['full_name'] = $fullName;
        echo json_encode(['success'=>true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error'=>'Method not allowed']);
}
