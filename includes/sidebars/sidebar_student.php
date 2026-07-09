<?php
/**
 * Sidebar — طالب
 * المتغيرات المطلوبة: $base, $level, $studentPoints, $studentRank
 */
$_activeSidebar = $_activeSidebar ?? '';
$_unreadNotifs  = function_exists('getUnreadNotificationsCount') && isset($userId) ? getUnreadNotificationsCount($userId) : 0;

// Pending subscriptions
$_pendingSubs = 0;
try {
    $pdo = getDB();
    $s = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE student_id=? AND status='pending'");
    $s->execute([$userId]);
    $_pendingSubs = (int)$s->fetchColumn();
} catch (Exception $e) {}
?>
<aside class="nagah-sidebar hidden lg:flex flex-col w-60 shrink-0 border-l border-slate-100 bg-white/80 backdrop-blur" id="sidebar">
    <!-- Profile -->
    <div class="p-5 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-white font-bold text-sm shrink-0"
                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <?php echo mb_substr($_SESSION['full_name'] ?? 'S', 0, 2); ?>
            </span>
            <div class="min-w-0">
                <p class="font-bold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></p>
                <span class="text-xs font-bold" style="color:<?php echo $level[2] ?? '#2563EB'; ?>">
                    <?php echo ($level[0] ?? '⭐') . ' ' . ($level[1] ?? 'طالب'); ?>
                </span>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
            <span><?php echo number_format($studentPoints ?? 0); ?> نقطة</span>
            <span class="font-bold" style="color:#2563EB">#<?php echo $studentRank ?? '—'; ?></span>
        </div>
        <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5">
            <?php $__pct = isset($studentPoints) ? min(100, ($studentPoints >= 1000 ? 100 : ($studentPoints % 200) / 2)) : 0; ?>
            <div class="h-1.5 rounded-full transition-all" style="width:<?php echo $__pct; ?>%;background:linear-gradient(90deg,#2563EB,#60A5FA)"></div>
        </div>
    </div>

    <!-- Nav -->
    <nav class="p-3 space-y-0.5 flex-1 overflow-y-auto">
        <?php
        $__links = [
            ['layout-dashboard', 'لوحة التحكم',       $base.'/student/dashboard.php',        'dashboard'],
            ['book-open',        'كورساتي',             $base.'/subscriptions/my.php',         'my-courses'],
            ['credit-card',      'اشتراك في مادة',     $base.'/subscriptions/subscribe.php',  'subscribe'],
            ['upload',           'رفع إيصال دفع',      $base.'/payments/upload.php',          'payment'],
            ['user-check',       'الحضور',              $base.'/student/attendance.php',       'attendance'],
            ['bar-chart-2',      'درجاتي',              $base.'/student/grades.php',           'grades'],
            ['megaphone',        'الإعلانات',           $base.'/announcements/view.php',       'announcements'],
            ['user',             'الملف الشخصي',        $base.'/auth/profile.php',             'profile'],
            ['log-out',          'تسجيل الخروج',        $base.'/auth/logout.php',              'logout'],
        ];
        foreach ($__links as [$icon, $label, $url, $key]):
            $isActive = $_activeSidebar === $key;
        ?>
        <a href="<?php echo $url; ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
           <?php echo $isActive ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'; ?>">
            <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
            <?php echo $label; ?>
            <?php if ($key === 'payment' && $_pendingSubs > 0): ?>
            <span class="mr-auto w-5 h-5 rounded-full bg-amber-500 text-white text-[10px] font-bold flex items-center justify-center"><?php echo $_pendingSubs; ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
</aside>

<!-- Mobile toggle button -->
<button id="sidebar-toggle"
        class="lg:hidden fixed bottom-5 left-5 z-50 w-12 h-12 rounded-full btn-primary-nagah shadow-xl flex items-center justify-center"
        onclick="document.getElementById('sidebar').classList.toggle('!flex')">
    <i data-lucide="sidebar" style="width:20px;height:20px;"></i>
</button>
