<?php

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$lang = $_SESSION['lang'] ?? 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

$error = '';
$success = '';

$database = new Database();
$pdo = $database->connect();

if (isset($_SESSION['user_id'])) {
    redirect('/index.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = 'Invalid security token. Please refresh the page and try again.';
        } else {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $user_type = $_POST['user_type'] ?? 'student';
            
            if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                $error = 'Please fill in all required fields.';
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                    $stmt->execute([$email, $username]);
                    
                    if ($stmt->fetch()) {
                        $error = 'Email or username is already registered.';
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $email, $hashed_password, $full_name, $user_type]);
                        
                        $success = 'Registration successful! Please login.';
                    }
                } catch (PDOException $e) {
                    $error = 'An error occurred while connecting to the database.';
                }
            }
        }
    }

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('register_title'); ?> - <?php echo __('hero_title'); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-15px);
            }
            60% {
                transform: translateY(-7px);
            }
        }

        @keyframes pop {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(0.95);
            }
            100% {
                transform: scale(1);
            }
        }

        .register-container {
            background: white;
            padding: 50px;
            border-radius: 25px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 480px;
            animation: fadeIn 0.8s ease-out;
        }
        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }
        .register-header h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 800;
        }
        .register-header i {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
            display: block;
        }
        .form-group {
            margin-bottom: 25px;
            animation: fadeIn 0.6s ease-out;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s;
        }
        .form-group label:hover {
            color: #667eea;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        .btn-register {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            animation: fadeIn 0.8s ease-out;
        }
        .btn-register:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        .btn-register:active {
            animation: pop 0.3s ease;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }
        .alert-error {
            background: linear-gradient(135deg, #fee 0%, #fcc 100%);
            color: #c00;
            border: 1px solid #fcc;
        }
        .alert-success {
            background: linear-gradient(135deg, #efe 0%, #cfc 100%);
            color: #060;
            border: 1px solid #cfc;
        }
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            animation: fadeIn 1s ease-out;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .login-link a:hover {
            color: #764ba2;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h1><?php echo __('hero_title'); ?></h1>
            <p><?php echo __('register_title'); ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="full_name"><?php echo __('full_name_label'); ?></label>
                <input type="text" id="full_name" name="full_name" required 
                       placeholder="<?php echo __('full_name_placeholder'); ?>">
            </div>
            
            <div class="form-group">
                <label for="username"><?php echo __('username_label'); ?></label>
                <input type="text" id="username" name="username" required 
                       placeholder="<?php echo __('username_placeholder'); ?>">
            </div>
            
            <div class="form-group">
                <label for="email"><?php echo __('email_label'); ?></label>
                <input type="email" id="email" name="email" required 
                       placeholder="<?php echo __('email_placeholder'); ?>">
            </div>
            
            <div class="form-group">
                <label for="user_type"><?php echo __('account_type_label'); ?></label>
                <select id="user_type" name="user_type" required>
                    <option value="student"><?php echo __('student'); ?></option>
                    <option value="professor"><?php echo __('professor'); ?></option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password"><?php echo __('password_label'); ?></label>
                <input type="password" id="password" name="password" required 
                       placeholder="<?php echo __('password_placeholder'); ?>">
            </div>
            
            <div class="form-group">
                <label for="confirm_password"><?php echo __('password_confirm_label'); ?></label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="<?php echo __('password_confirm_placeholder'); ?>">
            </div>
            
            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> <?php echo __('register'); ?>
            </button>
        </form>
        
        <div class="login-link">
            <p><?php echo __('have_account'); ?> <a href="login.php"><?php echo __('login'); ?></a></p>
        </div>
    </div>
</body>
</html>
