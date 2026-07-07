<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

$lang = $_SESSION['lang'] ?? 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
$error = '';
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isProfessor()) {
        redirect('/admin/dashboard.php');
    } else {
        redirect('/student/dashboard.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $error = 'توكن الأمان غير صحيح. الرجاء تحديث الصفحة والمحاولة مجدداً';
    } else {
        $email = getSafePost('email', '', 'email');
        $password = getSafePost('password', '', 'string');
        
        if (empty($email) || empty($password)) {
            $error = 'الرجاء إدخال البريد الإلكتروني وكلمة المرور';
        } else {
            try {
                $pdo = getDB();
                $stmt = $pdo->prepare("
                    SELECT id, username, full_name, user_type, password 
                    FROM users 
                    WHERE email = ?
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && verifyPassword($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    // Log the login attempt
                    logError('Login successful', [
                        'user_id' => $user['id'],
                        'user_type' => $user['user_type'],
                        'email' => $email
                    ]);
                    
                    if ($user['user_type'] === 'professor') {
                        redirect('/admin/dashboard.php');
                    } else {
                        redirect('/student/dashboard.php');
                    }
                    exit;
                } else {
                    // Log failed login attempt
                    logError('Failed login attempt', ['email' => $email]);
                    $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
                }
            } catch (PDOException $e) {
                logError('Login database error', ['error' => $e->getMessage()]);
                $error = 'حدث خطأ عند الاتصال بقاعدة البيانات';
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
    <title><?php echo __('login_title'); ?> - EduFlow</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        [data-theme="dark"] {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
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
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

        .login-container {
            background: var(--card-bg);
            padding: 50px;
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.8s ease-out;
            border: 1px solid var(--border);
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header h1 {
            color: var(--text-primary);
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .login-header i {
            font-size: 60px;
            color: var(--primary);
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
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-group label:hover {
            color: var(--primary);
        }

        .form-group input {
            width: 100%;
            padding: 16px;
            border: 2px solid var(--border);
            background: var(--bg-secondary);
            color: var(--text-primary);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
            animation: fadeIn 0.8s ease-out;
        }

        .btn-login:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.6);
        }

        .btn-login:active {
            animation: pop 0.3s ease;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: center;
        }

        .alert-error {
            background: var(--danger-light);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .alert i {
            font-size: 1.1rem;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            color: var(--text-secondary);
            animation: fadeIn 1s ease-out;
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .register-link a:hover {
            color: var(--primary-dark);
        }

        @media (max-width: 640px) {
            .login-container {
                padding: 30px;
                margin: 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .login-header i {
                font-size: 48px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-graduation-cap"></i>
            <h1>EduFlow</h1>
            <p><?php echo __('login_title'); ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="email">📧 <?php echo __('email_label'); ?></label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       autocomplete="email"
                       placeholder="<?php echo __('email_placeholder'); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">🔒 <?php echo __('password_label'); ?></label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                       placeholder="<?php echo __('password_placeholder'); ?>">
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> <?php echo __('login'); ?>
            </button>
        </form>
        
        <div class="register-link">
            <p><?php echo __('no_account'); ?> <a href="register.php"><?php echo __('register_now'); ?></a></p>
        </div>
    </div>
</body>
</html>
