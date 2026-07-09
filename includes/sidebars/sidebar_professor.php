<?php
/**
 * Sidebar — معلم (professor)
 * المتغيرات المطلوبة: $base, $userId, $_activeSidebar
 */
$_activeSidebar = $_activeSidebar ?? '';
$_pendingQ = 0;
try {
    $pdo = getDB();
    $q = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE professor_id=? AND status='pending'");
    $q->execute([$userId]);
    $_pendingQ = (int)$q->fetchColumn();
} catch (Exception $e) {}
?>
<aside class="nagah-sidebar hidden lg:flex flex-col w-60 shrink-0 border-l border-slate-100 bg-white/80 backdrop-blur" id="sidebar">
    <!-- Profile -->
    <div class="p-5 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-white font-bold text-sm shrink-0"
                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <?php echo mb_substr($_SESSION['full_name'] ?? 'P', 0, 2); ?>
            </span>
            <div class="min-w-0">
                <p class="font-bold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></p>
                <p class="text-xs text-blue-600 font-medium flex items-center gap-1">
                    <i data-lucide="user-cog" style="width:11px;height:11px;"></i> معلم
                </p>
            </div>
        </div>
    </div>

    <!-- Nav -->
    <nav class="p-3 space-y-0.5 flex-1 overflow-y-auto">
        <?php
        $__links = [
            ['layout-dashboard','لوحة التحكم',         $base.'/professor/dashboard.php',       'dashboard'],
            ['book',            'كورساتي',              $base.'/courses/list.php',              'courses'],
            ['plus-circle',     'كورس جديد',            $base.'/courses/create.php',            'create-course'],
            ['upload-cloud',    'رفع مادة',             $base.'/materials/upload.php',          'upload'],
            ['users',           'إدارة الطلاب',         $base.'/professor/students.php',        'students'],
            ['user-check',      'تسجيل الحضور',        $base.'/professor/attendance.php',      'attendance'],
            ['bar-chart-2',     'الدرجات',              $base.'/professor/grades.php',          'grades'],
            ['megaphone',       'الإعلانات',            $base.'/announcements/create.php',      'announcements'],
            ['message-circle',  'الأسئلة',              $base.'/professor/questions.php',       'questions'],
            ['user',            'الملف الشخصي',         $base.'/auth/profile.php',              'profile'],
            ['log-out',         'تسجيل الخروج',         $base.'/auth/logout.php',               'logout'],
        ];
        foreach ($__links as [$icon, $label, $url, $key]):
            $isActive = $_activeSidebar === $key;
        ?>
        <a href="<?php echo $url; ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
           <?php echo $isActive ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'; ?>">
            <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
            <?php echo $label; ?>
            <?php if ($key === 'questions' && $_pendingQ > 0): ?>
            <span class="mr-auto w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center"><?php echo $_pendingQ; ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
</aside>
