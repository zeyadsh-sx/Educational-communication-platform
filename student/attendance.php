<?php
require_once __DIR__ . '/_layout.php';
studentPageStart('سجل حضوري', 'attendance');

$userId = getCurrentUserId();
$pdo    = getDB();
$base   = nagahBaseUrl();

// كورسات الطالب
$csStmt = $pdo->prepare("
    SELECT c.id, c.course_name, c.course_code, u.full_name AS professor_name
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.id
    JOIN users u ON c.professor_id = u.id
    WHERE ce.student_id = ? AND ce.status = 'active'
    ORDER BY c.course_name
");
$csStmt->execute([$userId]);
$courses = $csStmt->fetchAll();

// حضور كل الكورسات
$allAtt = [];
foreach ($courses as $c) {
    $aStmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id=? AND course_id=? ORDER BY lesson_date DESC");
    $aStmt->execute([$userId, $c['id']]);
    $allAtt[$c['id']] = $aStmt->fetchAll();
}

$statusCfg = [
    'present' => ['حاضر','bg-green-100','text-green-700','check-circle'],
    'absent'  => ['غائب', 'bg-red-100',  'text-red-700',  'x-circle'],
    'late'    => ['متأخر','bg-amber-100','text-amber-700','clock'],
];
?>

<!-- Page header -->
<div class="mb-8">
    <h1 class="display font-semibold text-2xl sm:text-3xl text-slate-900 flex items-center gap-3">
        <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
              style="background:linear-gradient(135deg,#16a34a,#4ade80)">
            <i data-lucide="user-check" style="width:17px;height:17px;"></i>
        </span>
        سجل الحضور
    </h1>
    <p class="text-slate-500 mt-1 text-sm">تابع حضورك وغيابك في جميع المواد</p>
</div>

<?php if (empty($courses)): ?>
<div class="glass rounded-3xl p-14 text-center text-slate-400">
    <i data-lucide="calendar-x" style="width:52px;height:52px;" class="mx-auto mb-4 opacity-30"></i>
    <p class="text-sm">لا توجد مواد مسجلة</p>
</div>
<?php else: ?>

<!-- Summary cards -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
<?php
$totalPresent = $totalAbsent = $totalLate = $totalSessions = 0;
foreach ($allAtt as $records) {
    foreach ($records as $r) {
        $totalSessions++;
        if ($r['status'] === 'present') $totalPresent++;
        elseif ($r['status'] === 'absent') $totalAbsent++;
        elseif ($r['status'] === 'late')   $totalLate++;
    }
}
$overallRate = $totalSessions > 0 ? round($totalPresent / $totalSessions * 100) : 0;
$rateColor   = $overallRate >= 80 ? '#16a34a' : ($overallRate >= 60 ? '#d97706' : '#dc2626');
foreach ([
    [$overallRate.'%', 'نسبة الحضور', 'user-check',   $rateColor,  'rgba(22,163,74,.1)'],
    [$totalPresent,    'حضور',         'check-circle', '#16a34a',   'rgba(22,163,74,.1)'],
    [$totalAbsent,     'غياب',         'x-circle',     '#dc2626',   'rgba(220,38,38,.1)'],
    [$totalLate,       'تأخير',        'clock',        '#d97706',   'rgba(217,119,6,.1)'],
] as [$val,$lbl,$icon,$col,$bg]):
?>
<div class="glass rounded-2xl p-4 text-center reveal">
    <span class="w-9 h-9 rounded-xl mx-auto flex items-center justify-center mb-2" style="background:<?php echo $bg; ?>">
        <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;color:<?php echo $col; ?>"></i>
    </span>
    <p class="display font-semibold text-2xl" style="color:<?php echo $col; ?>"><?php echo $val; ?></p>
    <p class="text-xs text-slate-500 mt-0.5"><?php echo $lbl; ?></p>
</div>
<?php endforeach; ?>
</div>

<!-- Per-course attendance -->
<?php foreach ($courses as $c):
    $records = $allAtt[$c['id']] ?? [];
    $cnt     = count($records);
    $prCnt   = count(array_filter($records, fn($r) => $r['status'] === 'present'));
    $rate    = $cnt > 0 ? round($prCnt / $cnt * 100) : 0;
    $rCol    = $rate >= 80 ? '#16a34a' : ($rate >= 60 ? '#d97706' : '#dc2626');
?>
<div class="glass rounded-3xl overflow-hidden mb-6">
    <!-- Course header -->
    <div class="px-6 py-4 flex items-center justify-between gap-4"
         style="background:linear-gradient(135deg,rgba(22,163,74,.05),rgba(74,222,128,.05))">
        <div class="flex items-center gap-3 min-w-0">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0"
                  style="background:linear-gradient(135deg,#16a34a,#4ade80)">
                <i data-lucide="book" style="width:16px;height:16px;"></i>
            </span>
            <div class="min-w-0">
                <h2 class="font-bold text-slate-800"><?php echo htmlspecialchars($c['course_name']); ?></h2>
                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($c['professor_name']); ?></p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <div class="text-center">
                <p class="display font-semibold text-xl" style="color:<?php echo $rCol; ?>"><?php echo $rate; ?>%</p>
                <p class="text-xs text-slate-400"><?php echo $cnt; ?> حصة</p>
            </div>
            <div class="w-14 bg-slate-200 rounded-full h-2 hidden sm:block">
                <div class="h-2 rounded-full transition-all" style="width:<?php echo $rate; ?>%;background:<?php echo $rCol; ?>"></div>
            </div>
        </div>
    </div>

    <?php if (empty($records)): ?>
    <div class="text-center py-8 text-slate-400">
        <p class="text-sm">لم يتم تسجيل حضور بعد</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:rgba(0,0,0,.02)">
                    <th class="px-5 py-3 text-right font-semibold text-slate-600">التاريخ</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600">الحالة</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600 hidden sm:table-cell">ملاحظات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($records, 0, 20) as $r):
                [$slbl,$sbg,$stxt,$sicon] = $statusCfg[$r['status']] ?? ['—','bg-slate-100','text-slate-500','circle'];
            ?>
            <tr class="border-t border-slate-50 hover:bg-slate-50/50 transition">
                <td class="px-5 py-3 font-medium text-slate-700">
                    <?php echo date('d/m/Y', strtotime($r['lesson_date'])); ?>
                    <span class="text-xs text-slate-400 mr-1"><?php echo date('l', strtotime($r['lesson_date'])); ?></span>
                </td>
                <td class="px-5 py-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full <?php echo "$sbg $stxt"; ?>">
                        <i data-lucide="<?php echo $sicon; ?>" style="width:12px;height:12px;"></i>
                        <?php echo $slbl; ?>
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-slate-400 hidden sm:table-cell">
                    <?php echo $r['notes'] ? htmlspecialchars($r['notes']) : '—'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (count($records) > 20): ?>
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-400 text-center">
        يُعرض آخر 20 سجل من أصل <?php echo count($records); ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- Warning if below 75% -->
<?php if ($overallRate < 75 && $totalSessions > 0): ?>
<div class="glass rounded-3xl p-5 flex items-start gap-4"
     style="border:2px solid #fca5a5;background:rgba(254,242,242,.8)">
    <i data-lucide="alert-triangle" style="width:22px;height:22px;color:#dc2626;flex-shrink:0;margin-top:2px"></i>
    <div>
        <p class="font-bold text-red-700">تحذير: نسبة حضورك منخفضة (<?php echo $overallRate; ?>%)</p>
        <p class="text-sm text-red-600 mt-1">يُنصح بالحفاظ على نسبة حضور لا تقل عن 75% لضمان استمرار الاشتراك.</p>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php studentPageEnd(); ?>
