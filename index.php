<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    session_start();
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/includes/nagah_theme.php';

    $pageTitle = 'أكاديمية ماستر | منصة التعليم للثانوية والبكالوريا';
    $subjects = getLandingSubjects();
    $features = getLandingFeatures();
    $teachers = getLandingTeachers();
    $base = nagahBaseUrl();

    require_once __DIR__ . '/includes/nagah/head.php';
    require_once __DIR__ . '/includes/nagah/nav.php';
} catch (Exception $e) {
    die('<h1>خطأ في تحميل التطبيق</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>');
}

$heroImage = 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=900&h=760&fit=crop';
$ctaImage = 'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?w=900&h=600&fit=crop';
?>

<!-- HERO -->
<section id="top" class="relative w-full overflow-hidden">
    <span class="blob" style="width:420px;height:420px;background:#60A5FA;top:-120px;right:-100px;"></span>
    <span class="blob" style="width:360px;height:360px;background:#2563EB;bottom:-140px;left:-80px;opacity:.4;"></span>
    <span class="blob" style="width:260px;height:260px;background:#F59E0B;top:40%;left:22%;opacity:.28;"></span>
    <div class="absolute inset-0 grid-dots opacity-70"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-5 pt-16 pb-24 grid lg:grid-cols-2 gap-14 items-center">
        <div>
            <span class="reveal tag-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide" style="animation-delay:.05s">
                <i data-lucide="sparkles" style="width:14px;height:14px;"></i>
                أفضل منصة تعليمية في مصر
            </span>
            <h1 class="display reveal mt-5 text-4xl sm:text-5xl lg:text-[3.25rem] leading-[1.15] font-semibold text-slate-900" style="animation-delay:.15s">
                ابدأ رحلتك نحو<br><span style="color:#2563EB">التميز الأكاديمي</span>
            </h1>
            <p class="reveal mt-5 text-lg text-slate-600 max-w-lg leading-relaxed" style="animation-delay:.25s">
                أكاديمية ماستر — منصة متكاملة للثانوية العامة ونظام البكالوريا. معلمون محترفون، دروس مسجلة، حصص مباشرة، وامتحانات تفاعلية.
            </p>
            <div class="reveal mt-8 flex flex-wrap gap-3" style="animation-delay:.35s">
                <a href="#subjects" class="btn-primary-nagah px-7 py-3.5 rounded-full font-bold shadow-xl hover:-translate-y-0.5 transition-all inline-flex items-center gap-2">
                    <i data-lucide="book-open" style="width:18px;height:18px;"></i>
                    استكشف المواد
                </a>
                <a href="<?php echo $base; ?>/auth/register.php" class="btn-outline-nagah px-7 py-3.5 rounded-full font-bold transition-all hover:-translate-y-0.5 inline-flex items-center gap-2">
                    سجّل الآن
                </a>
            </div>
            <div class="reveal mt-8 flex items-center gap-4" style="animation-delay:.45s">
                <div class="flex hero-trust-avatars">
                    <span class="w-9 h-9 rounded-full border-2 border-white bg-blue-500"></span>
                    <span class="w-9 h-9 rounded-full border-2 border-white bg-blue-400 -mr-3"></span>
                    <span class="w-9 h-9 rounded-full border-2 border-white bg-amber-400 -mr-3"></span>
                    <span class="w-9 h-9 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-bold -mr-3" style="background:#2563EB">+9k</span>
                </div>
                <p class="text-sm font-medium text-slate-600">أكثر من 10,000 طالب يثقون بنا</p>
            </div>
        </div>

        <div class="relative reveal" style="animation-delay:.3s">
            <div class="glass rounded-[28px] p-3 lift">
                <img src="<?php echo $heroImage; ?>" alt="طلاب يدرسون" loading="lazy" class="w-full h-[380px] object-cover rounded-[20px]">
            </div>
            <div class="glass floaty absolute float-chip-left -right-4 top-10 rounded-2xl px-4 py-3 flex items-center gap-2.5 shadow-lg" style="--r:-4deg">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white" style="background:#2563EB"><i data-lucide="sigma" style="width:18px;height:18px;"></i></span>
                <span class="text-sm font-bold text-slate-800">رياضيات — A+</span>
            </div>
            <div class="glass floaty absolute float-chip-right -left-5 top-1/3 rounded-2xl px-4 py-3 flex items-center gap-2.5 shadow-lg" style="--r:3deg;animation-delay:1s">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white" style="background:#F59E0B"><i data-lucide="atom" style="width:18px;height:18px;"></i></span>
                <span class="text-sm font-bold text-slate-800">فيزياء — ممتاز</span>
            </div>
            <div class="glass floaty absolute -bottom-6 right-6 rounded-2xl px-5 py-3 shadow-lg" style="animation-delay:.5s">
                <p class="display font-semibold text-2xl leading-none" style="color:#2563EB">98%</p>
                <p class="text-xs text-slate-500 mt-0.5">نسبة نجاح الطلاب</p>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section id="stats-section" class="w-full">
    <div class="max-w-7xl mx-auto px-5 -mt-12 relative z-20">
        <div class="glass rounded-3xl grid grid-cols-2 lg:grid-cols-4 gap-6 px-6 py-8">
            <div class="text-center">
                <p class="stat-num display font-semibold text-4xl" data-target="10000" data-suffix="+" style="color:#2563EB">0</p>
                <p class="text-sm font-medium text-slate-600 mt-1">طالب وطالبة</p>
            </div>
            <div class="text-center">
                <p class="stat-num display font-semibold text-4xl" data-target="150" data-suffix="+" style="color:#F59E0B">0</p>
                <p class="text-sm font-medium text-slate-600 mt-1">معلم محترف</p>
            </div>
            <div class="text-center">
                <p class="stat-num display font-semibold text-4xl" data-target="50" data-suffix="+" style="color:#2563EB">0</p>
                <p class="text-sm font-medium text-slate-600 mt-1">كورس دراسي</p>
            </div>
            <div class="text-center">
                <p class="stat-num display font-semibold text-4xl" data-target="98" data-suffix="%" style="color:#F59E0B">0</p>
                <p class="text-sm font-medium text-slate-600 mt-1">نسبة النجاح</p>
            </div>
        </div>
    </div>
</section>

<!-- SUBJECTS -->
<section id="subjects" class="w-full py-24">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center max-w-2xl mx-auto">
            <span class="tag-pill inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">المواد الدراسية</span>
            <h2 class="display font-semibold text-3xl sm:text-4xl mt-4 text-slate-900">كل المواد التي تحتاجها</h2>
            <p class="mt-3 text-slate-500">ثانوية عامة وبكالوريا — شرح شامل وامتحانات تدريبية</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-12">
            <?php foreach ($subjects as $subject): ?>
            <article class="lift bg-white rounded-3xl p-6 border border-slate-100">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center text-white mb-4" style="background:<?php echo $subject['gradient']; ?>">
                    <i data-lucide="<?php echo $subject['lucide']; ?>"></i>
                </span>
                <h3 class="font-bold text-lg text-slate-900"><?php echo $subject['title']; ?></h3>
                <p class="text-sm text-slate-500 mt-1"><?php echo $subject['desc']; ?></p>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="<?php echo $base; ?>/courses/list.php" class="btn-outline-nagah inline-flex px-7 py-3 rounded-full font-bold transition-all hover:-translate-y-0.5">
                عرض جميع الكورسات
            </a>
        </div>
    </div>
</section>

<!-- TEACHERS -->
<section id="teachers" class="w-full py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center max-w-2xl mx-auto">
            <span class="tag-pill inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">فريقنا</span>
            <h2 class="display font-semibold text-3xl sm:text-4xl mt-4 text-slate-900">تعلّم على يد الأفضل</h2>
            <p class="mt-3 text-slate-500">نخبة من المعلمين ذوي الخبرة في الثانوية والبكالوريا</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
            <?php foreach ($teachers as $teacher): ?>
            <article class="lift bg-white rounded-3xl overflow-hidden border border-slate-100">
                <div class="h-52 overflow-hidden">
                    <img src="<?php echo $teacher['photo']; ?>" alt="<?php echo htmlspecialchars($teacher['name']); ?>" loading="lazy" class="w-full h-full object-cover">
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-lg text-slate-900"><?php echo $teacher['name']; ?></h3>
                    <p class="text-sm font-medium" style="color:#2563EB"><?php echo $teacher['subject']; ?></p>
                    <p class="text-xs text-slate-500 mt-1"><?php echo $teacher['exp']; ?></p>
                    <div class="flex items-center gap-1 mt-3">
                        <span class="star flex gap-0.5"><?php echo renderLucideStars(); ?></span>
                        <span class="text-xs font-bold text-slate-600 mr-1"><?php echo $teacher['rating']; ?></span>
                    </div>
                    <a href="<?php echo $base; ?>/auth/register.php" class="mt-4 block text-center py-2.5 rounded-xl text-sm font-bold transition hover:-translate-y-0.5 btn-primary-nagah">
                        سجّل في كورساته
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section id="features" class="w-full py-24">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center max-w-2xl mx-auto">
            <span class="tag-pill inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">لماذا نحن</span>
            <h2 class="display font-semibold text-3xl sm:text-4xl mt-4 text-slate-900">لماذا أكاديمية ماستر؟</h2>
            <p class="mt-3 text-slate-500">تجربة تعليمية متكاملة تجمع بين الجودة والمرونة</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
            <?php foreach ($features as $feature): ?>
            <article class="lift glass rounded-3xl p-6">
                <span class="w-11 h-11 rounded-2xl flex items-center justify-center text-white mb-4" style="background:<?php echo $feature['color']; ?>">
                    <i data-lucide="<?php echo $feature['lucide']; ?>"></i>
                </span>
                <h3 class="font-bold text-slate-900"><?php echo $feature['title']; ?></h3>
                <p class="text-sm text-slate-500 mt-1"><?php echo $feature['desc']; ?></p>
            </article>
            <?php endforeach; ?>
            <article class="lift rounded-3xl p-6 text-white flex flex-col justify-center min-h-[180px]" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="sparkles" class="mb-3" style="width:28px;height:28px;"></i>
                <h3 class="font-bold text-lg">ابدأ مجاناً اليوم</h3>
                <p class="text-sm mt-1 opacity-90">انضم لآلاف الطلاب وحقق أحلامك الأكاديمية</p>
            </article>
        </div>
    </div>
</section>

<!-- CTA -->
<section id="cta" class="w-full py-24">
    <div class="max-w-6xl mx-auto px-5">
        <div class="rounded-[32px] overflow-hidden grid lg:grid-cols-2 items-stretch shadow-2xl" style="background:linear-gradient(135deg,#1e3a8a,#2563EB)">
            <div class="p-10 lg:p-14 flex flex-col justify-center">
                <span class="inline-block w-fit px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide bg-white/15 text-blue-100">انضم إلينا</span>
                <h2 class="display font-semibold text-white text-3xl sm:text-4xl mt-4 leading-tight">جاهز لبدء رحلتك التعليمية؟</h2>
                <p class="text-blue-100 mt-4 leading-relaxed">سجّل كطالب أو تواصل معنا لتصبح معلماً في أكاديمية ماستر</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="<?php echo $base; ?>/auth/register.php" class="btn-white-nagah px-7 py-3.5 rounded-full font-bold shadow-xl hover:-translate-y-0.5 transition-all inline-block">
                        سجّل كطالب
                    </a>
                    <a href="<?php echo $base; ?>/pages/contact.php" class="btn-outline-white px-7 py-3.5 rounded-full font-bold transition-all hover:-translate-y-0.5 inline-block">
                        تواصل معنا
                    </a>
                </div>
            </div>
            <div class="min-h-[280px]">
                <img src="<?php echo $ctaImage; ?>" alt="طلاب ناجحون" loading="lazy" class="w-full h-full object-cover">
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/nagah/footer.php'; ?>
