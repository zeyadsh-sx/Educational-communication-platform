<?php
/**
 * قالب مشترك لصفحات لوحة الطالب
 */
function renderStudentPage(string $title, string $contentCallback): void
{
    session_start();
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/functions.php';
    require_once __DIR__ . '/../includes/header.php';

    if (!isLoggedIn() || !isStudent()) {
        redirect('/auth/login.php');
        exit;
    }

    $pageTitle = $title . ' | أكاديمية ماستر';
    ?>
    <div class="container dashboard-layout animate-fade">
        <?php require_once __DIR__ . '/../includes/dashboard_sidebar.php'; ?>
        <div class="dashboard-main">
            <?php $contentCallback(); ?>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
}
