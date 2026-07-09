<?php
/**
 * شريط جانبي لوحة تحكم الطالب
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$sidebarItems = [
    ['file' => 'dashboard.php', 'icon' => 'fa-tachometer-alt', 'label' => __('dashboard')],
    ['file' => 'dashboard.php', 'icon' => 'fa-book', 'label' => __('sidebar_my_courses'), 'anchor' => '#courses'],
    ['file' => 'videos.php', 'icon' => 'fa-play-circle', 'label' => __('sidebar_videos')],
    ['file' => 'live.php', 'icon' => 'fa-broadcast-tower', 'label' => __('sidebar_live')],
    ['file' => 'homework.php', 'icon' => 'fa-tasks', 'label' => __('sidebar_homework')],
    ['file' => 'exams.php', 'icon' => 'fa-file-alt', 'label' => __('sidebar_exams')],
    ['file' => 'grades.php', 'icon' => 'fa-chart-bar', 'label' => __('sidebar_grades')],
    ['file' => 'attendance.php', 'icon' => 'fa-calendar-check', 'label' => __('sidebar_attendance')],
    ['file' => 'certificates.php', 'icon' => 'fa-certificate', 'label' => __('sidebar_certificates')],
    ['file' => '../auth/profile.php', 'icon' => 'fa-user', 'label' => __('profile')],
    ['file' => 'settings.php', 'icon' => 'fa-cog', 'label' => __('settings')],
];
?>
<aside class="dashboard-sidebar glass">
    <div class="sidebar-header">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="user-avatar-sm" style="width: 45px; height: 45px; font-size: 1.1rem;">
                <?php echo mb_substr($_SESSION['full_name'] ?? 'ط', 0, 1); ?>
            </div>
            <div>
                <div style="font-weight: 700; font-size: 0.95rem;"><?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'] ?? 'طالب')[0]); ?></div>
                <div style="font-size: 0.8rem; color: var(--text-secondary);">طالب</div>
            </div>
        </div>
    </div>
    <ul class="sidebar-nav">
        <?php foreach ($sidebarItems as $item):
            $isActive = ($currentPage === $item['file'] || ($currentPage === 'dashboard.php' && $item['file'] === 'dashboard.php' && !isset($item['anchor']) && $currentPage === basename($_SERVER['PHP_SELF'])));
            if (isset($item['anchor'])) {
                $isActive = false;
            }
            $href = getBaseUrl() . '/student/' . $item['file'];
            if (strpos($item['file'], '../') === 0) {
                $href = getBaseUrl() . '/auth/profile.php';
            }
            if (isset($item['anchor'])) {
                $href = getBaseUrl() . '/student/dashboard.php' . $item['anchor'];
            }
        ?>
        <li>
            <a href="<?php echo $href; ?>" class="<?php echo ($currentPage === $item['file'] && !isset($item['anchor'])) ? 'active' : ''; ?>">
                <i class="fas <?php echo $item['icon']; ?>"></i>
                <?php echo $item['label']; ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</aside>
