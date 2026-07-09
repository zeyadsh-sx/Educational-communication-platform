<?php
/**
 * صفحة 403 — موحدة وجميلة
 * تُستدعى من accessDenied() في rbac.php
 */
function renderAccessDenied(string $userName, string $dashUrl, string $customMsg = ''): void
{
    $base = function_exists('getBaseUrl') ? getBaseUrl() : '';

    // هل الـ head مطلوب؟ (ربما تم تحميله مسبقاً)
    $needsHead = !defined('NAGAH_HEAD_LOADED');
    if ($needsHead && function_exists('nagahBaseUrl')) {
        $pageTitle = '403 — غير مصرح';
        if (file_exists(__DIR__ . '/nagah_theme.php')) require_once __DIR__ . '/nagah_theme.php';
        if (file_exists(__DIR__ . '/nagah/head.php'))   require     __DIR__ . '/nagah/head.php';
        if (file_exists(__DIR__ . '/nagah/nav.php'))    require     __DIR__ . '/nagah/nav.php';
    } elseif ($needsHead) {
        // fallback بسيط بدون nagah
        echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head>';
        echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>غير مصرح</title>';
        echo '<script src="https://cdn.tailwindcss.com/3.4.17"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/lucide@0.263.0/dist/umd/lucide.min.js"></script>';
        echo '</head><body class="bg-slate-50 min-h-screen">';
    }
    ?>
<div class="min-h-[80vh] flex items-center justify-center px-5 py-16">
    <div class="max-w-md w-full text-center">

        <!-- Icon -->
        <div class="relative mx-auto mb-8 w-32 h-32">
            <div class="w-32 h-32 rounded-full flex items-center justify-center"
                 style="background:linear-gradient(135deg,rgba(220,38,38,.12),rgba(239,68,68,.06))">
                <i data-lucide="shield-off" style="width:52px;height:52px;color:#dc2626"></i>
            </div>
            <!-- 403 badge -->
            <span class="absolute -top-1 -right-1 w-12 h-12 rounded-full flex items-center justify-center
                         font-black text-sm text-white shadow-lg"
                  style="background:linear-gradient(135deg,#dc2626,#f87171)">403</span>
        </div>

        <!-- Text -->
        <h1 class="text-2xl font-bold text-slate-900 mb-2">غير مصرح بالوصول</h1>
        <p class="text-slate-500 mb-2">
            مرحباً <strong><?php echo $userName; ?></strong>، ليس لديك صلاحية لعرض هذه الصفحة.
        </p>
        <?php if ($customMsg): ?>
        <p class="text-sm text-red-600 font-medium mb-4"><?php echo htmlspecialchars($customMsg); ?></p>
        <?php endif; ?>
        <p class="text-sm text-slate-400 mb-8">
            إذا كنت تعتقد أن هذا خطأ، تواصل مع مدير النظام.
        </p>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="<?php echo $dashUrl; ?>"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-bold text-sm text-white shadow-lg hover:-translate-y-0.5 transition-all"
               style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="layout-dashboard" style="width:15px;height:15px;"></i>
                لوحة التحكم
            </a>
            <a href="<?php echo $base; ?>/index.php"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-bold text-sm border-2 border-slate-300 text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="home" style="width:15px;height:15px;"></i>
                الرئيسية
            </a>
        </div>

        <!-- Breadcrumb hint -->
        <p class="mt-8 text-xs text-slate-400 flex items-center justify-center gap-1.5">
            <i data-lucide="info" style="width:12px;height:12px;"></i>
            الصفحة المطلوبة:
            <code class="bg-slate-100 px-2 py-0.5 rounded text-slate-500">
                <?php echo htmlspecialchars(substr($_SERVER['REQUEST_URI'] ?? '', 0, 60)); ?>
            </code>
        </p>

    </div>
</div>
    <?php
    if (file_exists(__DIR__ . '/nagah/footer.php')) {
        require __DIR__ . '/nagah/footer.php';
    } else {
        echo '<script>if(typeof lucide!=="undefined")lucide.createIcons();</script></body></html>';
    }
}
