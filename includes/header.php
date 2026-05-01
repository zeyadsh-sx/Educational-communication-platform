<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

$lang = $_SESSION['lang'] ?? 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
$theme = $_COOKIE['theme'] ?? 'light';

// Calculate base path for assets
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$appRoot = str_replace('\\', '/', dirname(__DIR__));
$basePath = str_replace($docRoot, '', $appRoot);
if (empty($basePath) || $basePath == '/') {
    $basePath = '';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Educational Platform'; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/style.css">
    
    <style>
        .nav-wrapper {
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 1rem 0;
            transition: var(--transition);
        }
        
        .nav-wrapper.scrolled {
            padding: 0.5rem 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 2rem;
            border-radius: var(--radius-lg);
            margin: 0 auto;
            max-width: 1400px;
            width: calc(100% - 2rem);
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: 800;
            text-decoration: none;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
        }

        .nav-link:hover {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .action-btn {
            background: transparent;
            border: none;
            color: var(--text-main);
            font-size: 1.25rem;
            cursor: pointer;
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
            position: relative;
        }

        .action-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .user-dropdown {
            position: relative;
        }

        .user-trigger {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-full);
            cursor: pointer;
            background: rgba(99, 102, 241, 0.05);
            transition: var(--transition);
        }

        .user-trigger:hover {
            background: rgba(99, 102, 241, 0.1);
        }

        .user-avatar-sm {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        @media (max-width: 992px) {
            .nav-links { display: none; }
        }
    </style>
</head>
<body>
    <div class="nav-wrapper" id="navWrapper">
        <nav class="navbar glass">
            <a href="<?php echo $basePath; ?>/index.php" class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>EduFlow</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="<?php echo $basePath; ?>/index.php" class="nav-link"><?php echo __('home'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>/courses/list.php" class="nav-link"><?php echo __('courses'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>/pages/about.php" class="nav-link"><?php echo __('about'); ?></a></li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isProfessor()): ?>
                        <li><a href="<?php echo $basePath; ?>/admin/dashboard.php" class="nav-link"><?php echo __('dashboard'); ?></a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $basePath; ?>/student/dashboard.php" class="nav-link"><?php echo __('dashboard'); ?></a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <div class="nav-search" style="position: relative; margin: 0 1rem;">
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="globalSearch" placeholder="<?php echo __('search'); ?>..." class="form-control" style="padding-right: 2.5rem; width: 250px; border-radius: var(--radius-full);">
                </div>
                <div id="searchResults" class="glass" style="display: none; position: absolute; top: 100%; right: 0; width: 300px; margin-top: 0.5rem; border-radius: var(--radius-md); box-shadow: var(--card-shadow); max-height: 400px; overflow-y: auto; z-index: 1000;">
                </div>
            </div>
            
            <div class="nav-actions">
                <!-- Theme Toggle -->
                <button class="action-btn" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                
                <!-- Language Switch -->
                <a href="?lang=<?php echo $lang === 'ar' ? 'en' : 'ar'; ?>" class="action-btn" title="Switch Language">
                    <i class="fas fa-globe"></i>
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <!-- Notifications -->
                    <div class="user-dropdown">
                        <button class="action-btn" id="notifTrigger">
                            <i class="fas fa-bell"></i>
                            <?php 
                            $notifCount = getPendingQuestionsCount(getCurrentUserId(), getCurrentUserType());
                            if ($notifCount > 0): ?>
                                <span class="notification-badge"><?php echo $notifCount; ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="user-dropdown">
                        <div class="user-trigger" onclick="location.href='<?php echo $basePath; ?>/auth/profile.php'">
                            <div class="user-avatar-sm">
                                <?php echo mb_substr($_SESSION['full_name'] ?? 'U', 0, 1); ?>
                            </div>
                            <span class="user-name-text" style="font-weight: 600; font-size: 0.9rem; margin-right: 5px;">
                                <?php echo explode(' ', $_SESSION['full_name'] ?? 'User')[0]; ?>
                            </span>
                        </div>
                    </div>
                    
                    <a href="<?php echo $basePath; ?>/auth/logout.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $basePath; ?>/auth/login.php" class="btn btn-outline btn-sm"><?php echo __('login'); ?></a>
                    <a href="<?php echo $basePath; ?>/auth/register.php" class="btn btn-primary btn-sm"><?php echo __('register'); ?></a>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <script>
        // Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            document.cookie = `theme=${newTheme};path=/;max-age=31536000`;
            
            themeToggle.querySelector('i').className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        });

        // Initialize icon
        if (html.getAttribute('data-theme') === 'dark') {
            themeToggle.querySelector('i').className = 'fas fa-sun';
        }

        // Scroll Logic
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navWrapper');
            if (window.scrollY > 20) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // Global Search Logic
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    fetch(`<?php echo $basePath; ?>/api/search.php?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success' && data.results.length > 0) {
                                searchResults.innerHTML = data.results.map(item => `
                                    <a href="${item.url}" style="display: block; padding: 10px 15px; border-bottom: 1px solid var(--glass-border); text-decoration: none; color: var(--text-main); transition: var(--transition);">
                                        <div style="font-weight: 600;">
                                            <i class="${item.type === 'course' ? 'fas fa-book' : 'fas fa-file'} " style="color: var(--primary); margin-left: 8px;"></i>
                                            ${item.title}
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-right: 24px;">${item.subtitle}</div>
                                    </a>
                                `).join('');
                                searchResults.style.display = 'block';
                            } else {
                                searchResults.innerHTML = '<div style="padding: 15px; text-align: center; color: var(--text-muted);">لا توجد نتائج</div>';
                                searchResults.style.display = 'block';
                            }
                        });
                }, 300);
            });
            
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        }
    </script>
    
    <main class="animate-fade">
