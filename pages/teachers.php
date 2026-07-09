<?php
require_once __DIR__ . '/../includes/nagah/layout.php';
require_once __DIR__ . '/../includes/academy_data.php';
nagahPageStart('المعلمون | أكاديمية ماستر');
$teachers = getLandingTeachers();
$base = nagahBaseUrl();
?>

<section class="w-full py-20">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="tag-pill inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">فريقنا</span>
            <h1 class="display font-semibold text-3xl sm:text-4xl mt-4 text-slate-900">المعلمون</h1>
            <p class="mt-3 text-slate-500">تعرّف على نخبة معلمينا المتخصصين</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach (array_merge(getLandingTeachers(), array_slice(getAcademyTeachers(), 4)) as $i => $teacher):
                $photo = $teacher['photo'] ?? null;
                $avatar = $teacher['avatar'] ?? mb_substr($teacher['name'], 0, 1);
            ?>
            <article class="lift bg-white rounded-3xl overflow-hidden border border-slate-100">
                <?php if ($photo): ?>
                <div class="h-48 overflow-hidden"><img src="<?php echo $photo; ?>" alt="" class="w-full h-full object-cover"></div>
                <?php endif; ?>
                <div class="p-6 <?php echo $photo ? '' : 'text-center'; ?>">
                    <?php if (!$photo): ?>
                    <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center text-white text-2xl font-bold mb-4" style="background:linear-gradient(135deg,#2563EB,#60A5FA)"><?php echo $avatar; ?></div>
                    <?php endif; ?>
                    <h3 class="font-bold text-lg"><?php echo $teacher['name']; ?></h3>
                    <p class="text-sm font-medium" style="color:#2563EB"><?php echo $teacher['subject']; ?></p>
                    <p class="text-xs text-slate-500 mt-2"><?php echo $teacher['experience'] ?? ($teacher['exp'] ?? ''); ?></p>
                    <?php if (!empty($teacher['bio'])): ?><p class="text-sm text-slate-600 mt-3"><?php echo mb_substr($teacher['bio'], 0, 100); ?>...</p><?php endif; ?>
                    <div class="flex items-center <?php echo $photo ? '' : 'justify-center'; ?> gap-1 mt-3 star"><?php echo renderLucideStars(); ?> <span class="text-xs font-bold mr-1"><?php echo $teacher['rating']; ?></span></div>
                    <a href="<?php echo $base; ?>/auth/register.php" class="mt-4 inline-block px-6 py-2.5 rounded-xl text-sm font-bold btn-primary-nagah">سجّل الآن</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php nagahPageEnd(); ?>
