<?php
require_once __DIR__ . '/../includes/nagah/layout.php';
require_once __DIR__ . '/../includes/academy_data.php';
nagahPageStart('الجدول الدراسي | أكاديمية ماستر');
$schedule = getWeeklySchedule();
$base     = nagahBaseUrl();

// Group by day
$byDay = [];
foreach ($schedule as $row) {
    $byDay[$row['day']][] = $row;
}

$dayColors = [
    'السبت'   => ['#2563EB','rgba(37,99,235,.1)'],
    'الأحد'   => ['#16a34a','rgba(22,163,74,.1)'],
    'الاثنين' => ['#7c3aed','rgba(124,58,237,.1)'],
    'الثلاثاء'=> ['#F59E0B','rgba(245,158,11,.1)'],
    'الأربعاء'=> ['#0ea5e9','rgba(14,165,233,.1)'],
    'الخميس' => ['#dc2626','rgba(220,38,38,.1)'],
];
?>

<!-- Hero -->
<section class="relative w-full overflow-hidden py-16 sm:py-20">
    <span class="blob" style="width:360px;height:360px;background:#60A5FA;top:-100px;right:-80px;"></span>
    <span class="blob" style="width:300px;height:300px;background:#2563EB;bottom:-80px;left:-60px;opacity:.4;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>
    <div class="relative z-10 max-w-3xl mx-auto px-5 text-center">
        <span class="reveal tag-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">
            <i data-lucide="calendar-days" style="width:13px;height:13px;"></i> المواعيد
        </span>
        <h1 class="display reveal mt-5 text-3xl sm:text-4xl font-semibold text-slate-900" style="animation-delay:.1s">الجدول الأسبوعي</h1>
        <p class="reveal mt-3 text-slate-500 max-w-md mx-auto" style="animation-delay:.2s">مواعيد الحصص الأسبوعية — أونلاين وحضوري</p>

        <!-- Legend -->
        <div class="reveal flex justify-center gap-4 mt-6 text-sm" style="animation-delay:.3s">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span> أونلاين
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span> حضوري
            </span>
        </div>
    </div>
</section>

<main class="max-w-6xl mx-auto px-5 pb-20">

    <!-- Card view by day -->
    <div class="space-y-8">
        <?php foreach ($byDay as $day => $rows):
            [$dayColor, $dayBg] = $dayColors[$day] ?? ['#64748b','rgba(100,116,139,.1)'];
        ?>
        <div class="reveal">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shrink-0" style="background:<?php echo $dayColor; ?>">
                    <i data-lucide="calendar" style="width:17px;height:17px;"></i>
                </span>
                <h2 class="display font-semibold text-xl text-slate-800"><?php echo $day; ?></h2>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:<?php echo $dayBg; ?>;color:<?php echo $dayColor; ?>"><?php echo count($rows); ?> حصص</span>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($rows as $row): ?>
                <div class="glass rounded-2xl p-5 lift">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="font-bold text-slate-800"><?php echo htmlspecialchars($row['subject']); ?></h3>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full shrink-0 <?php echo $row['online'] ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'; ?>">
                            <?php echo $row['online'] ? 'أونلاين' : 'حضوري'; ?>
                        </span>
                    </div>
                    <div class="space-y-2 text-sm text-slate-600">
                        <div class="flex items-center gap-2">
                            <i data-lucide="user-round" style="width:14px;height:14px;color:#2563EB"></i>
                            <?php echo htmlspecialchars($row['teacher']); ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" style="width:14px;height:14px;color:#F59E0B"></i>
                            <?php echo htmlspecialchars($row['time']); ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="<?php echo $row['online'] ? 'globe' : 'map-pin'; ?>" style="width:14px;height:14px;color:#7c3aed"></i>
                            <?php echo htmlspecialchars($row['classroom']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Full table view (desktop) -->
    <div class="mt-14 hidden lg:block">
        <h2 class="display font-semibold text-xl text-slate-800 mb-5 flex items-center gap-2">
            <i data-lucide="table" style="width:20px;height:20px;color:#2563EB"></i> الجدول الكامل
        </h2>
        <div class="glass rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background:rgba(37,99,235,.06)">
                            <th class="px-5 py-4 text-right font-bold text-blue-700">اليوم</th>
                            <th class="px-5 py-4 text-right font-bold text-blue-700">المادة</th>
                            <th class="px-5 py-4 text-right font-bold text-blue-700">المعلم</th>
                            <th class="px-5 py-4 text-right font-bold text-blue-700">الوقت</th>
                            <th class="px-5 py-4 text-right font-bold text-blue-700">القاعة</th>
                            <th class="px-5 py-4 text-right font-bold text-blue-700">النوع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedule as $row): ?>
                        <tr class="border-t border-slate-100 hover:bg-blue-50/20 transition">
                            <td class="px-5 py-3.5 font-semibold text-slate-700"><?php echo htmlspecialchars($row['day']); ?></td>
                            <td class="px-5 py-3.5 font-medium"><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td class="px-5 py-3.5 text-slate-600"><?php echo htmlspecialchars($row['teacher']); ?></td>
                            <td class="px-5 py-3.5 text-slate-600"><?php echo htmlspecialchars($row['time']); ?></td>
                            <td class="px-5 py-3.5 text-slate-600"><?php echo htmlspecialchars($row['classroom']); ?></td>
                            <td class="px-5 py-3.5">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $row['online'] ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'; ?>">
                                    <?php echo $row['online'] ? 'أونلاين' : 'حضوري'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="mt-12 glass rounded-3xl p-8 text-center" style="background:linear-gradient(135deg,rgba(37,99,235,.06),rgba(96,165,250,.06))">
        <h3 class="display font-semibold text-xl mb-3">هل تريد الانضمام لحصة؟</h3>
        <p class="text-slate-500 text-sm mb-6">سجّل الآن للوصول لجميع الحصص والمواد الدراسية</p>
        <a href="<?php echo $base; ?>/auth/register.php" class="btn-primary-nagah inline-flex items-center gap-2 px-7 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
            <i data-lucide="user-plus" style="width:16px;height:16px;"></i> سجّل الآن مجاناً
        </a>
    </div>

</main>

<?php nagahPageEnd(); ?>
