<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Educational communication platform'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-size: 20px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        
        .navbar-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .navbar-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            transition: background 0.3s;
        }
        
        .navbar-menu a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .user-name {
            font-weight: 600;
        }
        
        .user-type {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .btn-logout {
            background: #e74c3c;
            padding: 8px 15px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: #c0392b;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .navbar-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="/index.php" class="navbar-brand">
            <i class="fas fa-graduation-cap"></i> Educational communication platform
        </a>
        
        <div class="navbar-menu">
            <a href="/courses/list.php">
                <i class="fas fa-book"></i> Courses
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_type'] === 'professor'): ?>
                    <a href="/admin/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="/student/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="navbar-user">
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
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="/auth/login.php" style="color: white;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </nav>
    
    <main>