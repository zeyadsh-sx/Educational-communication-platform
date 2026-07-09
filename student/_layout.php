<?php
/**
 * قالب مشترك لصفحات لوحة الطالب — nagah-theme
 *
 * الاستخدام:
 *   require_once __DIR__ . '/_layout.php';
 *   studentPageStart('عنوان الصفحة', 'sidebar-key');
 *   // ... محتوى الصفحة ...
 *   studentPageEnd();
 */

function studentPageStart(string $title, string $sidebarKey = ''): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';
    require_once __DIR__ . '/../includes/subscription_functions.php';
    require_once __DIR__ . '/../includes/nagah_theme.php';

    requireRole('student');

    $GLOBALS['_student_layout_userId'] = getCurrentUserId();
    $base = nagahBaseUrl();

    // Level badge
    $pdo = getDB();
    $pts = (int)$pdo->prepare("SELECT points FROM users WHERE id=?")->execute([$GLOBALS['_student_layout_userId']])
        ? (int)$pdo->query("SELECT points FROM users WHERE id={$GLOBALS['_student_layout_userId']}")->fetchColumn()
        : 0;
    $level = $pts >= 1000 ? ['👑','الأسطورة','#d97706'] :
            ($pts >= 500  ? ['🏆','بطل','#7c3aed'] :
            ($pts >= 200  ? ['🔥','نشط','#dc2626'] :
            ($pts >= 50   ? ['🚀','ناشئ','#2563EB'] :
                            ['⭐','مبتدئ','#64748b'])));

    $studentPoints = $pts;
    $studentRank   = function_exists('getStudentRank') ? getStudentRank($GLOBALS['_student_layout_userId']) : '—';
    $userId        = $GLOBALS['_student_layout_userId'];
    $_activeSidebar = $sidebarKey;

    $pageTitle = $title . ' | أكاديمية ماستر';
    require __DIR__ . '/../includes/nagah/head.php';
    require __DIR__ . '/../includes/nagah/nav.php';
    echo '<div class="flex min-h-[calc(100vh-64px)]">';
    require __DIR__ . '/../includes/sidebars/sidebar_student.php';
    echo '<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto"><div class="max-w-5xl">';
}

function studentPageEnd(): void
{
    echo '</div></main></div>';
    require __DIR__ . '/../includes/nagah/footer.php';
}
