<?php
require_once __DIR__ . '/../includes/nagah/layout.php';
require_once __DIR__ . '/../includes/academy_data.php';
require_once __DIR__ . '/../includes/nagah_theme.php';
nagahPageStart('المعلمون | أكاديمية ماستر');
$teachers = getAcademyTeachers();
$base     = nagahBaseUrl();

$subjectColors = [
    'الرياضيات'       => ['#2563EB','rgba(37,99,235,.1)','sigma'],
    'الفيزياء'        => ['#F59E0B','rgba(245,158,11,.1)','atom'],
    'الكيمياء'        => ['#0ea5e9','rgba(14,165,233,.1)','flask-conical'],
    'اللغة الإنجليزية'=> ['#7c3aed','rgba(124,58,237,.1)','languages'],
    'اللغة العربية'   => ['#d97706','rgba(217,119,6,.1)','book-open-text'],
    'علوم الحاسب'     => ['#4338ca','rgba(67,56,202,.1)','cpu'],
];
?>

<!-- Hero -->
<section class="relative w-full overflow-hidden py-16 sm:py-20">
    <span class="blob" style="width:360px;height:360px;background:#60A5FA;top:-100px;right:-80px;"></span>
    <span class="blob" style="width:300px;height:300px;background:#2563EB;bottom:-80px;left:-60px;opacity:.4;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>
    <div class="relative z-10 max-w-3xl mx-auto px-5 text-center">
        <span class="reveal tag-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">
            <i data-lucide="users" style="width:13px;height:13px;"></i> فريقنا
        </span>
        <h1 class="display reveal mt-5 text-3xl sm:text-4xl font-semibold text-slate-900" style="animation-delay:.1s">
            نخبة <span style="color:#2563EB">المعلمين</span>
        </h1>
        <p class="reveal mt-3 text-slate-500 max-w-md mx-auto" style="animation-delay:.2s">
            معلمون محترفون بسنوات خبرة في تدريس الثانوية العامة والبكالوريا
        </p>
    </div>
</section>

<main class="max-w-7xl mx-auto px-5 pb-20">

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7">
        <?php foreach ($teachers as $i => $t):
            [$tcolor, $tbg, $ticon] = $subjectColors[$t['subject']] ?? ['#2563EB','rgba(37,99,235,.1)','user'];
            $initials = mb_substr($t['name'], 0, 2);
            $photo    = $t['photo'] ?? null;
        ?>
        <article class="glass rounded-3xl overflow-hidden lift reveal flex flex-col" style="animation-delay:<?php echo $i * 0.07; ?>s">
            <!-- Top photo / avatar -->
            <?php if ($photo): ?>
            <div class="h-52 overflow-hidden">
                <img src="<?php echo $photo; ?>" alt="<?php echo htmlspecialchars($t['name']); ?>" class="w-full h-full object-cover">
            </div>
            <?php else: ?>
            <div class="h-36 relative overflow-hidden flex items-center justify-center" style="background:linear-gradient(135deg,<?php echo $tcolor; ?>,<?php echo $tcolor; ?>99)">
                <div class="absolute inset-0 grid-dots opacity-20"></div>
                <span class="relative z-10 w-20 h-20 rounded-3xl flex items-center justify-center text-white text-3xl font-bold" style="background:rgba(255,255,255,.2);backdrop-filter:blur(8px)">
                    <?php echo $initials; ?>
                </span>
            </div>
            <?php endif; ?>

            <div class="p-6 flex flex-col flex-1">
                <!-- Subject badge -->
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:<?php echo $tbg; ?>">
                        <i data-lucide="<?php echo $ticon; ?>" style="width:14px;height:14px;color:<?php echo $tcolor; ?>"></i>
                    </span>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:<?php echo $tbg; ?>;color:<?php echo $tcolor; ?>">
                        <?php echo htmlspecialchars($t['subject']); ?>
                    </span>
                </div>

                <h3 class="font-bold text-slate-900 text-lg leading-snug"><?php echo htmlspecialchars($t['name']); ?></h3>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                    <i data-lucide="briefcase" style="width:12px;height:12px;"></i>
                    <?php echo htmlspecialchars($t['experience']); ?> خبرة
                </p>

                <?php if (!empty($t['bio'])): ?>
                <p class="text-sm text-slate-600 mt-3 leading-relaxed flex-1"><?php echo htmlspecialchars(mb_substr($t['bio'], 0, 110)); ?>…</p>
                <?php else: ?>
                <div class="flex-1"></div>
                <?php endif; ?>

                <!-- Rating + students -->
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-1" style="color:#F59E0B">
                        <?php echo renderLucideStars(); ?>
                        <span class="text-xs font-bold text-slate-700 mr-1"><?php echo $t['rating']; ?></span>
                    </div>
                    <span class="text-xs text-slate-500 flex items-center gap-1">
                        <i data-lucide="users" style="width:13px;height:13px;"></i>
                        <?php echo number_format($t['students']); ?> طالب
                    </span>
                </div>

                <a href="<?php echo $base; ?>/auth/register.php"
                   class="mt-4 btn-primary-nagah block text-center py-2.5 rounded-xl text-sm font-bold hover:-translate-y-0.5 transition-all shadow">
                    سجّل في كورساتي
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <!-- CTA banner -->
    <div class="mt-16 rounded-3xl overflow-hidden relative" style="background:linear-gradient(135deg,#1e3a8a,#2563EB,#3b82f6)">
        <div class="absolute inset-0 grid-dots opacity-20"></div>
        <div class="relative z-10 px-8 py-12 sm:py-14 text-center">
            <h2 class="display font-semibold text-2xl sm:text-3xl text-white mb-3">هل تريد التعلم مع أفضل المعلمين؟</h2>
            <p class="text-white/70 mb-7 max-w-lg mx-auto">انضم لأكثر من 10,000 طالب يستفيدون من خبرة معلمينا المتميزين</p>
            <a href="<?php echo $base; ?>/auth/register.php"
               class="inline-flex items-center gap-2 bg-white text-blue-600 font-bold px-8 py-3.5 rounded-full shadow-xl hover:-translate-y-0.5 transition-all">
                <i data-lucide="user-plus" style="width:17px;height:17px;"></i>
                سجّل الآن مجاناً
            </a>
        </div>
    </div>

</main>

<?php nagahPageEnd(); ?>
