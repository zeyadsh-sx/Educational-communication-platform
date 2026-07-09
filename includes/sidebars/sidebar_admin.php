<?php
/**
 * Sidebar — مدير النظام (admin)
 * المتغيرات المطلوبة: $base, $_activeSidebar
 */
$_activeSidebar = $_activeSidebar ?? '';
$_pendingSubs = 0;
try {
    $pdo = getDB();
    $_pendingSubs = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='pending'")->fetchColumn();
} catch (Exception $e) {}
?>
<aside class="nagah-sidebar hidden lg:flex flex-col w-60 shrink-0 border-l border-slate-100 bg-white/80 backdrop-blur" id="sidebar">
    <!-- Profile -->
    <div class="p-5 border-b border-slate-100" style="background:rgba(124,58,237,.04)">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-white font-bold text-sm shrink-0"
                  style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                <?php echo mb_substr($_SESSION['full_name'] ?? 'A', 0, 2); ?>
            </span>
            <div class="min-w-0">
                <p class="font-bold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></p>
                <p class="text-xs font-bold flex items-center gap-1" style="color:#7c3aed">
                    <i data-lucide="shield-check" style="width:11px;height:11px;"></i> مدير النظام
                </p>
            </div>
        </div>
    </div>

    <!-- Nav -->
    <nav class="p-3 space-y-0.5 flex-1 overflow-y-auto">
        <?php
        $__links = [
            // الرئيسية
            ['layout-dashboard','الرئيسية',            $base.'/admin_panel/dashboard.php',     'dashboard'],
            // المستخدمون
            ['users',           'إدارة المستخدمين',    $base.'/admin_panel/users.php',         'users'],
            ['graduation-cap',  'الطلاب',               $base.'/admin_panel/students.php',      'students'],
            ['user-cog',        'المعلمون',             $base.'/admin_panel/professors.php',    'professors'],
            // المحتوى
            ['book-open',       'الكورسات',             $base.'/admin_panel/courses.php',       'courses'],
            ['megaphone',       'الإعلانات',            $base.'/admin_panel/announcements.php', 'announcements'],
            // المالية
            ['credit-card',     'الاشتراكات',           $base.'/admin_panel/subscriptions.php', 'subscriptions'],
            ['receipt',         'إيصالات الدفع',        $base.'/admin_panel/payments.php',      'payments'],
            // الإحصائيات
            ['bar-chart-2',     'الإحصائيات',           $base.'/admin_panel/analytics.php',    'analytics'],
            // الإعدادات
            ['settings',        'إعدادات الموقع',       $base.'/admin_panel/settings.php',     'settings'],
            ['log-out',         'تسجيل الخروج',         $base.'/auth/logout.php',               'logout'],
        ];
        foreach ($__links as [$icon, $label, $url, $key]):
            $isActive = $_activeSidebar === $key;
        ?>
        <a href="<?php echo $url; ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
           <?php echo $isActive ? 'font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'; ?>"
           <?php if ($isActive) echo 'style="background:rgba(124,58,237,.08);color:#7c3aed"'; ?>>
            <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
            <?php echo $label; ?>
            <?php if ($key === 'subscriptions' && $_pendingSubs > 0): ?>
            <span class="mr-auto inline-flex w-5 h-5 items-center justify-center rounded-full bg-amber-500 text-white text-[10px] font-bold"><?php echo $_pendingSubs; ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
</aside>
