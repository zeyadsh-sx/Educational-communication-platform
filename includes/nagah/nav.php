<?php $base = nagahBaseUrl(); ?>
<header class="sticky top-0 z-50 w-full">
    <nav class="glass-nav w-full">
        <div class="max-w-7xl mx-auto px-5 py-3 flex items-center justify-between">
            <a href="<?php echo $base; ?>/index.php#top" class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-lg" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <i data-lucide="graduation-cap" style="width:20px;height:20px;"></i>
                </span>
                <span class="display font-semibold text-lg text-slate-900">أكاديمية ماستر</span>
            </a>

            <div class="hidden md:flex items-center gap-8">
                <a href="<?php echo $base; ?>/index.php#subjects" class="text-sm font-medium text-slate-700 hover:text-blue-600 transition">المواد</a>
                <a href="<?php echo $base; ?>/index.php#teachers" class="text-sm font-medium text-slate-700 hover:text-blue-600 transition">المعلمون</a>
                <a href="<?php echo $base; ?>/index.php#features" class="text-sm font-medium text-slate-700 hover:text-blue-600 transition">المميزات</a>
                <a href="<?php echo $base; ?>/pages/schedule.php" class="text-sm font-medium text-slate-700 hover:text-blue-600 transition">الجدول</a>
                <a href="<?php echo $base; ?>/pages/contact.php" class="text-sm font-medium text-slate-700 hover:text-blue-600 transition">تواصل</a>
            </div>

            <div class="flex items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo isProfessor() ? $base.'/admin/dashboard.php' : $base.'/student/dashboard.php'; ?>"
                       class="hidden sm:inline-flex px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 btn-primary-nagah">
                        لوحة التحكم
                    </a>
                <?php else: ?>
                    <a href="<?php echo $base; ?>/auth/login.php" class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-blue-600 hover:text-blue-700">دخول</a>
                    <a href="<?php echo $base; ?>/auth/register.php"
                       class="px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 btn-primary-nagah">
                        سجّل الآن
                    </a>
                <?php endif; ?>
                <button type="button" id="nagahMobileBtn" class="md:hidden p-2 rounded-lg hover:bg-slate-100" aria-label="القائمة">
                    <i data-lucide="menu" style="width:22px;height:22px;"></i>
                </button>
            </div>
        </div>
        <div id="nagahMobileMenu" class="hidden md:hidden border-t border-slate-100 px-5 py-4 flex flex-col gap-3 bg-white/95 backdrop-blur">
            <a href="<?php echo $base; ?>/index.php#subjects" class="text-sm font-medium py-2">المواد</a>
            <a href="<?php echo $base; ?>/index.php#teachers" class="text-sm font-medium py-2">المعلمون</a>
            <a href="<?php echo $base; ?>/index.php#features" class="text-sm font-medium py-2">المميزات</a>
            <a href="<?php echo $base; ?>/pages/schedule.php" class="text-sm font-medium py-2">الجدول</a>
            <a href="<?php echo $base; ?>/pages/contact.php" class="text-sm font-medium py-2">تواصل</a>
            <?php if (!isLoggedIn()): ?>
                <a href="<?php echo $base; ?>/auth/login.php" class="text-sm font-medium py-2 text-blue-600">تسجيل الدخول</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
