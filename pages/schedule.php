<?php
require_once __DIR__ . '/../includes/nagah/layout.php';
require_once __DIR__ . '/../includes/academy_data.php';
nagahPageStart('الجدول الدراسي | أكاديمية ماستر');
$schedule = getWeeklySchedule();
?>

<section class="w-full py-20">
    <div class="max-w-5xl mx-auto px-5">
        <div class="text-center mb-12">
            <span class="tag-pill inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">المواعيد</span>
            <h1 class="display font-semibold text-3xl sm:text-4xl mt-4">الجدول الأسبوعي</h1>
            <p class="mt-3 text-slate-500">مواعيد الحصص أونلاين وحضورياً</p>
        </div>
        <div class="glass rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-blue-50/80">
                            <th class="p-4 text-right font-bold text-blue-700">اليوم</th>
                            <th class="p-4 text-right font-bold text-blue-700">المادة</th>
                            <th class="p-4 text-right font-bold text-blue-700">المعلم</th>
                            <th class="p-4 text-right font-bold text-blue-700">الوقت</th>
                            <th class="p-4 text-right font-bold text-blue-700">النوع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedule as $row): ?>
                        <tr class="border-t border-slate-100 hover:bg-blue-50/30">
                            <td class="p-4 font-semibold"><?php echo $row['day']; ?></td>
                            <td class="p-4"><?php echo $row['subject']; ?></td>
                            <td class="p-4 text-slate-600"><?php echo $row['teacher']; ?></td>
                            <td class="p-4"><?php echo $row['time']; ?></td>
                            <td class="p-4">
                                <?php if ($row['online']): ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">أونلاين</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">حضوري</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php nagahPageEnd(); ?>
