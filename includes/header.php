<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/emoji.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/migrations.php';
$basePath = getBaseUrl();
$lang = $_SESSION['lang'] ?? 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
$theme = $_COOKIE['theme'] ?? 'light';
?>


<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>" data-theme="<?php echo $theme; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? SITE_NAME ?? 'أكاديمية ماستر'; ?></title>

    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Twemoji - Realistic Emoji Rendering -->
    <script src="https://cdn.jsdelivr.net/npm/@twemoji/api@14.1.0/dist/twemoji.min.js" crossorigin="anonymous"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/style.css">
    
    <!-- Emoji Styling -->
    <style>
        img.emoji {
            height: 1em;
            width: 1em;
            margin: 0 0.05em 0 0.1em;
            vertical-align: -0.1em;
        }
        
        .emoji {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            font-size: inherit;
        }
        
        .emoji-text {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .achievement-badge {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border-radius: var(--radius-md);
            background: rgba(99, 102, 241, 0.05);
            border: 1px solid var(--glass-border);
            text-align: center;
            cursor: help;
            transition: all 0.3s ease;
        }
        
        .achievement-badge:hover {
            background: rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }
        
        .achievement-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
        }
        
        .rating-display {
            display: inline-flex;
            gap: 0.25rem;
        }
    </style>

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

        /* Notification Dropdown */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 350px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            margin-top: 0.5rem;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .dropdown-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text);
        }

        .view-all-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .view-all-link:hover {
            text-decoration: underline;
        }

        .dropdown-content {
            max-height: 300px;
            overflow-y: auto;
        }

        .dropdown-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
            transition: background-color 0.2s ease;
        }

        .dropdown-item:hover {
            background: var(--hover-bg);
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item p {
            margin: 0 0 0.5rem 0;
            font-size: 0.9rem;
            color: var(--text);
            line-height: 1.4;
        }

        .dropdown-item small {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .no-notifications {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--text-muted);
        }

        .no-notifications i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        @media (max-width: 992px) {
            .nav-links {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="nav-wrapper" id="navWrapper">
        <nav class="navbar glass">
            <a href="<?php echo $basePath; ?>/index.php" class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>أكاديمية ماستر</span>
            </a>

            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="القائمة">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-links">
                <li><a href="<?php echo $basePath; ?>/index.php" class="nav-link"><?php echo __('home'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>/courses/list.php" class="nav-link"><?php echo __('courses'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>/pages/teachers.php" class="nav-link"><?php echo __('teachers'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>/pages/schedule.php" class="nav-link"><?php echo __('schedule'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>/pages/about.php" class="nav-link"><?php echo __('about'); ?></a></li>
                <li><a href="<?php echo $basePath; ?>/pages/contact.php" class="nav-link"><?php echo __('contact'); ?></a></li>
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
                            $notifCount = getUnreadNotificationsCount(getCurrentUserId());
                            if ($notifCount > 0): ?>
                                <span class="notification-badge"><?php echo $notifCount; ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu" id="notifDropdown">
                            <div class="dropdown-header">
                                <h4>الإشعارات</h4>
                                <a href="<?php echo $basePath; ?>/notifications/view.php" class="view-all-link">عرض الكل</a>
                            </div>
                            <div class="dropdown-content" id="notifContent">
                                <?php
                                $pdo = getDB();
                                $notifStmt = $pdo->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                                $notifStmt->execute([getCurrentUserId()]);
                                $recentNotifs = $notifStmt->fetchAll();

                                if (empty($recentNotifs)): ?>
                                    <div class="no-notifications">
                                        <i class="fas fa-bell-slash"></i>
                                        <p>لا توجد إشعارات جديدة</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recentNotifs as $notif): ?>
                                        <div class="dropdown-item">
                                            <p><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <small><?php echo date('d/m H:i', strtotime($notif['created_at'])); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
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

    <!-- Mobile Navigation -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
    <nav class="mobile-nav" id="mobileNav">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <strong style="color:var(--primary); font-size:1.1rem;">أكاديمية ماستر</strong>
            <button class="mobile-menu-btn" id="mobileNavClose"><i class="fas fa-times"></i></button>
        </div>
        <a href="<?php echo $basePath; ?>/index.php" class="nav-link"><?php echo __('home'); ?></a>
        <a href="<?php echo $basePath; ?>/courses/list.php" class="nav-link"><?php echo __('courses'); ?></a>
        <a href="<?php echo $basePath; ?>/pages/teachers.php" class="nav-link"><?php echo __('teachers'); ?></a>
        <a href="<?php echo $basePath; ?>/pages/schedule.php" class="nav-link"><?php echo __('schedule'); ?></a>
        <a href="<?php echo $basePath; ?>/pages/about.php" class="nav-link"><?php echo __('about'); ?></a>
        <a href="<?php echo $basePath; ?>/pages/contact.php" class="nav-link"><?php echo __('contact'); ?></a>
        <?php if (isLoggedIn()): ?>
            <a href="<?php echo isProfessor() ? $basePath.'/admin/dashboard.php' : $basePath.'/student/dashboard.php'; ?>" class="nav-link"><?php echo __('dashboard'); ?></a>
            <a href="<?php echo $basePath; ?>/auth/logout.php" class="nav-link" style="color:var(--danger);"><?php echo __('logout'); ?></a>
        <?php else: ?>
            <a href="<?php echo $basePath; ?>/auth/login.php" class="nav-link"><?php echo __('login'); ?></a>
            <a href="<?php echo $basePath; ?>/auth/register.php" class="nav-link" style="color:var(--primary);"><?php echo __('register'); ?></a>
        <?php endif; ?>
    </nav>

    <script>
        // Initialize Twemoji Parser
        document.addEventListener('DOMContentLoaded', () => {
            twemoji.parse(document.body);
        });
        
        // Re-parse when content is dynamically added
        const observer = new MutationObserver((mutations) => {
            let shouldParse = false;
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length > 0) {
                    shouldParse = true;
                }
            });
            if (shouldParse) {
                twemoji.parse(document.body);
            }
        });
        
        observer.observe(document.body, { childList: true, subtree: true });

        // Mobile menu
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileNavClose = document.getElementById('mobileNavClose');
        const mobileNav = document.getElementById('mobileNav');
        const mobileNavOverlay = document.getElementById('mobileNavOverlay');

        function openMobileNav() {
            mobileNav?.classList.add('open');
            mobileNavOverlay?.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeMobileNav() {
            mobileNav?.classList.remove('open');
            mobileNavOverlay?.classList.remove('open');
            document.body.style.overflow = '';
        }
        mobileMenuBtn?.addEventListener('click', openMobileNav);
        mobileNavClose?.addEventListener('click', closeMobileNav);
        mobileNavOverlay?.addEventListener('click', closeMobileNav);

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

        // Notification Dropdown Logic
        const notifTrigger = document.getElementById('notifTrigger');
        const notifDropdown = document.getElementById('notifDropdown');

        if (notifTrigger && notifDropdown) {
            notifTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                notifDropdown.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!notifTrigger.contains(e.target) && !notifDropdown.contains(e.target)) {
                    notifDropdown.classList.remove('show');
                }
            });
        }
    </script>

    <main class="animate-fade">