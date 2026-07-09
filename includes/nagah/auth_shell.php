<?php
/**
 * Nagah auth pages — head + mini nav
 */
function nagahAuthHead(string $title): void
{
    $lang = $_SESSION['lang'] ?? 'ar';
    $dir = $lang === 'ar' ? 'rtl' : 'ltr';
    $base = getBaseUrl();
    ?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>" class="nagah-theme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.0/dist/umd/lucide.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Cairo:wght@400;500;600;700&family=El+Messiri:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base; ?>/css/nagah-theme.css">
</head>
<body class="nagah-theme min-h-screen w-full text-slate-900 overflow-x-hidden bg-white">
<header class="sticky top-0 z-50 w-full glass-nav">
    <nav class="max-w-7xl mx-auto px-5 py-3 flex items-center justify-between">
        <a href="<?php echo $base; ?>/index.php" class="flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-lg" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="graduation-cap" style="width:20px;height:20px;"></i>
            </span>
            <span class="display font-semibold text-lg">أكاديمية ماستر</span>
        </a>
        <div class="hidden md:flex items-center gap-8">
            <a href="<?php echo $base; ?>/index.php#subjects" class="text-sm font-medium hover:text-blue-600 transition">المواد</a>
            <a href="<?php echo $base; ?>/index.php#teachers" class="text-sm font-medium hover:text-blue-600 transition">المعلمون</a>
            <a href="<?php echo $base; ?>/auth/register.php" class="text-sm font-medium hover:text-blue-600 transition">التسجيل</a>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo $base; ?>/auth/login.php" class="px-4 py-2.5 rounded-full text-sm font-semibold hover:bg-slate-100 transition">تسجيل الدخول</a>
            <a href="<?php echo $base; ?>/auth/register.php" class="btn-primary-nagah px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5">سجّل الآن</a>
        </div>
    </nav>
</header>
    <?php
}

function nagahAuthFoot(): void
{
    $base = getBaseUrl();
    ?>
<footer class="w-full py-8 border-t border-slate-100 mt-auto">
    <div class="max-w-7xl mx-auto px-5 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="graduation-cap" style="width:18px;height:18px;"></i>
            </span>
            <span class="display font-semibold text-sm">أكاديمية ماستر</span>
        </div>
        <p class="text-sm text-slate-500">&copy; <?php echo date('Y'); ?> جميع الحقوق محفوظة</p>
    </div>
</footer>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
    <?php
}

function nagahFeedbackClass(string $kind): string
{
    return $kind === 'success'
        ? 'rounded-xl px-4 py-3 text-sm font-medium bg-green-100 text-green-700'
        : 'rounded-xl px-4 py-3 text-sm font-medium bg-red-100 text-red-700';
}

function generateUsernameFromEmail(PDO $pdo, string $email): string
{
    $base = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]);
    if ($base === '') {
        $base = 'student';
    }
    $username = $base;
    $i = 1;
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    while (true) {
        $stmt->execute([$username]);
        if (!$stmt->fetch()) {
            return $username;
        }
        $username = $base . $i;
        $i++;
    }
}
