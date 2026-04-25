<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
require_once __DIR__ . '/functions.php';
$lang = $_SESSION['lang'] ?? 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Educational communication platform'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            background-size: cover;
            color: #2c3e50;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.95) 0%, rgba(26, 37, 47, 0.95) 100%);
            color: white;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 1000;
            animation: slideDown 0.6s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .navbar-brand {
            font-size: 22px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
            text-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        }

        .navbar-brand i {
            animation: bounce 2s infinite;
        }
        
        .navbar-menu {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .navbar-menu a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .navbar-menu a::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.4s, height 0.4s;
        }

        .navbar-menu a:hover::before {
            width: 200px;
            height: 200px;
        }
        
        .navbar-menu a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .navbar-menu a:active {
            animation: pop 0.3s ease;
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 15px;
            border-radius: 25px;
            background: rgba(255,255,255,0.1);
            transition: all 0.3s;
        }

        .user-info:hover {
            background: rgba(255,255,255,0.15);
            transform: scale(1.05);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s;
        }

        .user-avatar:hover {
            transform: rotate(360deg);
        }
        
        .user-name {
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-type {
            font-size: 11px;
            opacity: 0.8;
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            padding: 10px 20px;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-logout:hover {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.5);
        }

        .btn-logout:active {
            animation: pop 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
                padding: 15px 20px;
            }
            
            .navbar-menu {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-5px);
            }
            60% {
                transform: translateY(-3px);
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
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="/index.php" class="navbar-brand">
            <i class="fas fa-graduation-cap"></i> <?php echo __('hero_title'); ?>
        </a>
        
        <div class="navbar-menu">
            <a href="/courses/list.php">
                <i class="fas fa-book"></i> <?php echo __('courses'); ?>
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_type'] === 'professor'): ?>
                    <a href="/admin/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> <?php echo __('dashboard'); ?>
                    </a>
                <?php else: ?>
                    <a href="/student/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> <?php echo __('dashboard'); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="navbar-user">
            <?php
            // Logic for switching language
            $switch_lang = $lang === 'ar' ? 'en' : 'ar';
            ?>
            <a href="?lang=<?php echo $switch_lang; ?>" style="color: white; margin-right: 15px; text-decoration: none;">
                <i class="fas fa-globe"></i> <?php echo __('switch_lang'); ?>
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo mb_substr($_SESSION['full_name'] ?? 'M', 0, 1); ?>
                    </div>
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></div>
                        <div class="user-type">
                            <?php echo $_SESSION['user_type'] === 'professor' ? 'Professor' : 'Student'; ?>
                        </div>
                    </div>
                </div>
                <a href="/auth/logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> <?php echo __('logout'); ?>
                </a>
            <?php else: ?>
                <a href="/auth/login.php" style="color: white;">
                    <i class="fas fa-sign-in-alt"></i> <?php echo __('login'); ?>
                </a>
            <?php endif; ?>
        </div>
    </nav>
    
    <main>
