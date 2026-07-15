<?php
$base    = nagahBaseUrl();
$currUrl = $_SERVER['REQUEST_URI'] ?? '';

function navActive(string $path, string $curr): string {
    return str_contains($curr, $path) ? 'text-blue-600 font-bold' : 'text-slate-700 hover:text-blue-600';
}
?>
<header class="sticky top-0 z-50 w-full" id="main-nav">
    <nav class="glass-nav w-full">
        <div class="max-w-7xl mx-auto px-5 py-3 flex items-center justify-between gap-4">

            <!-- Logo -->
            <a href="<?php echo $base; ?>/index.php" class="flex items-center gap-2.5 shrink-0">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-lg"
                      style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <i data-lucide="graduation-cap" style="width:20px;height:20px;"></i>
                </span>
                <div class="leading-tight">
                    <span class="display font-semibold text-base text-slate-900 block">أكاديمية ماستر</span>
                    <span class="text-[10px] text-slate-400 font-medium block -mt-0.5">ثانوية عامة · بكالوريا</span>
                </div>
            </a>

            <!-- Desktop links -->
            <div class="hidden md:flex items-center gap-1">
                <?php $links = [
                    ['/index.php',          'المواد',    'book-open'],
                    ['/pages/teachers.php', 'المعلمون', 'users'],
                    ['/pages/schedule.php', 'الجدول',   'calendar-days'],
                    ['/pages/about.php',    'من نحن',   'info'],
                    ['/pages/contact.php',  'تواصل',    'phone'],
                ];
                foreach ($links as [$href, $label, $icon]): ?>
                <a href="<?php echo $base . $href; ?>"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium transition-all <?php echo navActive($href, $currUrl); ?>">
                    <i data-lucide="<?php echo $icon; ?>" style="width:14px;height:14px;"></i>
                    <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Auth actions -->
            <div class="flex items-center gap-2 shrink-0">
                <?php if (isLoggedIn()):
                    $dashUrl    = isProfessor() ? $base.'/professor/dashboard.php' : $base.'/student/dashboard.php';
                    $notifCount = function_exists('getUnreadNotificationsCount') ? getUnreadNotificationsCount(getCurrentUserId()) : 0;
                    $isDashPage = str_contains($currUrl, 'dashboard.php');
                ?>
                    <!-- Notifications bell -->
                    <a href="<?php echo $base; ?>/notifications/view.php"
                       class="relative hidden sm:flex p-2 rounded-xl hover:bg-slate-100 transition text-slate-600">
                        <i data-lucide="bell" style="width:20px;height:20px;"></i>
                        <?php if ($notifCount > 0): ?>
                        <span class="absolute -top-0.5 -left-0.5 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                            <?php echo $notifCount > 9 ? '9+' : $notifCount; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <!-- زر لوحة التحكم — يظهر في كل صفحة -->
                    <?php if (!$isDashPage): ?>
                    <a href="<?php echo $dashUrl; ?>"
                       class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 btn-primary-nagah">
                        <i data-lucide="layout-dashboard" style="width:15px;height:15px;"></i>
                        لوحة التحكم
                    </a>
                    <?php else: ?>
                    <span class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-blue-50 text-blue-700">
                        <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold">
                            <?php echo mb_substr($_SESSION['full_name'] ?? 'U', 0, 1); ?>
                        </span>
                        <?php echo htmlspecialchars(mb_substr($_SESSION['full_name'] ?? '', 0, 20)); ?>
                    </span>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo $base; ?>/auth/login.php"
                       class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-full transition">
                        <i data-lucide="log-in" style="width:15px;height:15px;"></i>
                        دخول
                    </a>
                    <a href="<?php echo $base; ?>/auth/register.php"
                       class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 btn-primary-nagah">
                        <i data-lucide="user-plus" style="width:15px;height:15px;"></i>
                        سجّل الآن
                    </a>
                <?php endif; ?>

                <!-- Mobile hamburger -->
                <button type="button" id="nagahMobileBtn"
                        class="md:hidden p-2 rounded-xl hover:bg-slate-100 transition text-slate-600"
                        aria-label="القائمة" aria-expanded="false">
                    <i data-lucide="menu" id="nav-menu-icon" style="width:22px;height:22px;"></i>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="nagahMobileMenu"
             class="hidden md:hidden border-t border-slate-100 bg-white/98 backdrop-blur px-5 py-4">
            <div class="flex flex-col gap-1 mb-4">
                <?php foreach ($links as [$href, $label, $icon]): ?>
                <a href="<?php echo $base . $href; ?>"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo navActive($href, $currUrl); ?>">
                    <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;"></i>
                    <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php if (isLoggedIn()): ?>
            <div class="border-t border-slate-100 pt-3 flex flex-col gap-2">
                <?php if (!$isDashPage): ?>
                <a href="<?php echo $dashUrl ?? $base.'/student/dashboard.php'; ?>"
                   class="flex items-center justify-center gap-2 py-2.5 rounded-full btn-primary-nagah font-bold text-sm">
                    <i data-lucide="layout-dashboard" style="width:15px;height:15px;"></i>
                    لوحة التحكم
                </a>
                <?php endif; ?>
                <a href="<?php echo $base; ?>/auth/logout.php"
                   class="text-center text-sm text-red-500 font-medium py-2">تسجيل الخروج</a>
            </div>
            <?php else: ?>
            <div class="border-t border-slate-100 pt-3 flex gap-2">
                <a href="<?php echo $base; ?>/auth/login.php"
                   class="flex-1 text-center py-2.5 rounded-full border-2 border-blue-600 text-blue-600 font-bold text-sm">دخول</a>
                <a href="<?php echo $base; ?>/auth/register.php"
                   class="flex-1 text-center py-2.5 rounded-full btn-primary-nagah font-bold text-sm">سجّل الآن</a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<script>
(function () {
    const btn  = document.getElementById('nagahMobileBtn');
    const menu = document.getElementById('nagahMobileMenu');
    const icon = document.getElementById('nav-menu-icon');
    if (!btn || !menu) return;
    btn.addEventListener('click', () => {
        const open = !menu.classList.contains('hidden');
        menu.classList.toggle('hidden', open);
        btn.setAttribute('aria-expanded', String(!open));
        if (icon) icon.setAttribute('data-lucide', open ? 'menu' : 'x');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    /* Shrink nav on scroll */
    const nav = document.getElementById('main-nav');
    window.addEventListener('scroll', () => {
        nav?.classList.toggle('nav-scrolled', window.scrollY > 20);
    }, { passive: true });
})();
</script>
