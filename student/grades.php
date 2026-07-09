<?php
require_once __DIR__ . '/_layout.php';
studentPageStart('درجاتي', 'grades');

$userId = getCurrentUserId();
$pdo    = getDB();
$base   = nagahBaseUrl();

// جلب الكورسات المسجل فيها
$coursesStmt = $pdo->prepare("
    SELECT c.id, c.course_name, c.course_code, u.full_name AS professor_name
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.id
    JOIN users u ON c.professor_id = u.id
    WHERE ce.student_id = ? AND ce.status = 'active'
    ORDER BY c.course_name
");
$coursesStmt->execute([$userId]);
$courses = $coursesStmt->fetchAll();

// درجات كل الكورسات
$allGrades = [];
foreach ($courses as $c) {
    $gStmt = $pdo->prepare("SELECT * FROM grades WHERE student_id=? AND course_id=? ORDER BY created_at DESC");
    $gStmt->execute([$userId, $c['id']]);
    $allGrades[$c['id']] = $gStmt->fetchAll();
}

$typeLabels = ['exam'=>'امتحان','homework'=>'واجب','quiz'=>'كويز','participation'=>'مشاركة'];
$typeColors = ['exam'=>'#2563EB','homework'=>'#7c3aed','quiz'=>'#16a34a','participation'=>'#d97706'];
$typeBg     = ['exam'=>'bg-blue-100','homework'=>'bg-purple-100','quiz'=>'bg-green-100','participation'=>'bg-amber-100'];
?>

<!-- Page header -->
<div class="mb-8">
    <h1 class="display font-semibold text-2xl sm:text-3xl text-slate-900 flex items-center gap-3">
        <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
              style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
            <i data-lucide="bar-chart-2" style="width:17px;height:17px;"></i>
        </span>
        درجاتي
    </h1>
    <p class="text-slate-500 mt-1 text-sm">متابعة شاملة لجميع درجاتك في المواد</p>
</div>

<?php if (empty($courses)): ?>
<div class="glass rounded-3xl p-14 text-center text-slate-400">
    <i data-lucide="bar-chart-2" style="width:52px;height:52px;" class="mx-auto mb-4 opacity-30"></i>
    <h3 class="font-bold text-lg text-slate-500">لا توجد مواد مسجلة</h3>
    <p class="text-sm mt-2 mb-5">اشترك في مادة لتبدأ متابعة درجاتك</p>
    <a href="<?php echo $base; ?>/subscriptions/subscribe.php"
       class="btn-primary-nagah inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-bold shadow-lg">
        <i data-lucide="plus" style="width:15px;height:15px;"></i> اشترك في مادة
    </a>
</div>
<?php else: ?>

<?php foreach ($courses as $c):
    $grades   = $allGrades[$c['id']] ?? [];
    $total    = count($grades);
    $avgScore = 0;
    if ($total > 0) {
        $sumPct = 0;
        foreach ($grades as $g) $sumPct += ($g['max_score'] > 0 ? $g['score'] / $g['max_score'] * 100 : 0);
        $avgScore = round($sumPct / $total);
    }
    $avgColor = $avgScore >= 80 ? '#16a34a' : ($avgScore >= 60 ? '#d97706' : '#dc2626');
?>
<div class="glass rounded-3xl overflow-hidden mb-6">

    <!-- Course header -->
    <div class="px-6 py-4 flex items-center justify-between gap-4"
         style="background:linear-gradient(135deg,rgba(37,99,235,.06),rgba(96,165,250,.06))">
        <div class="flex items-center gap-3 min-w-0">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0"
                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="book" style="width:16px;height:16px;"></i>
            </span>
            <div class="min-w-0">
                <h2 class="font-bold text-slate-800"><?php echo htmlspecialchars($c['course_name']); ?></h2>
                <p class="text-xs text-slate-500 flex items-center gap-1">
                    <i data-lucide="user-round" style="width:11px;height:11px;"></i>
                    <?php echo htmlspecialchars($c['professor_name']); ?>
                </p>
            </div>
        </div>
        <?php if ($total > 0): ?>
        <div class="text-center shrink-0">
            <p class="display font-semibold text-2xl" style="color:<?php echo $avgColor; ?>">
                <?php echo $avgScore; ?>%
            </p>
            <p class="text-xs text-slate-400">المتوسط</p>
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($grades)): ?>
    <div class="text-center py-10 text-slate-400">
        <i data-lucide="clipboard-list" style="width:36px;height:36px;" class="mx-auto mb-2 opacity-30"></i>
        <p class="text-sm">لم يتم تسجيل درجات بعد</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:rgba(0,0,0,.02)">
                    <th class="px-5 py-3 text-right font-semibold text-slate-600">التقييم</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600">النوع</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600">الدرجة</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600 hidden sm:table-cell">التاريخ</th>
                    <th class="px-5 py-3 text-right font-semibold text-slate-600 hidden md:table-cell">ملاحظات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($grades as $g):
                $pct   = $g['max_score'] > 0 ? round($g['score'] / $g['max_score'] * 100) : 0;
                $bCol  = $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#d97706' : '#dc2626');
                $tType = $g['grade_type'];
            ?>
            <tr class="border-t border-slate-50 hover:bg-slate-50/50 transition">
                <td class="px-5 py-3.5 font-semibold text-slate-800">
                    <?php echo htmlspecialchars($g['title']); ?>
                </td>
                <td class="px-5 py-3.5">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full <?php echo $typeBg[$tType] ?? 'bg-slate-100'; ?>"
                          style="color:<?php echo $typeColors[$tType] ?? '#64748b'; ?>">
                        <?php echo $typeLabels[$tType] ?? $tType; ?>
                    </span>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2.5">
                        <span class="font-bold text-slate-900">
                            <?php echo $g['score']; ?><span class="text-slate-400 font-normal">/<?php echo $g['max_score']; ?></span>
                        </span>
                        <div class="w-16 bg-slate-200 rounded-full h-1.5 hidden sm:block">
                            <div class="h-1.5 rounded-full transition-all"
                                 style="width:<?php echo $pct; ?>%;background:<?php echo $bCol; ?>"></div>
                        </div>
                        <span class="text-xs font-bold" style="color:<?php echo $bCol; ?>">
                            <?php echo $pct; ?>%
                        </span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-400 hidden sm:table-cell">
                    <?php echo date('d/m/Y', strtotime($g['created_at'])); ?>
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-500 hidden md:table-cell">
                    <?php echo $g['notes'] ? htmlspecialchars(mb_substr($g['notes'], 0, 60)) : '—'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Summary bar -->
    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center gap-6 flex-wrap text-xs text-slate-500">
        <span><?php echo $total; ?> تقييم</span>
        <?php
        foreach ($typeLabels as $key => $lbl) {
            $cnt = count(array_filter($grades, fn($g) => $g['grade_type'] === $key));
            if ($cnt) echo "<span>$lbl: <strong>$cnt</strong></span>";
        }
        ?>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- Overall summary card -->
<?php
$allGradesFlat = array_merge(...array_values($allGrades));
if (!empty($allGradesFlat)):
    $overallPct = 0;
    foreach ($allGradesFlat as $g) {
        $overallPct += ($g['max_score'] > 0 ? $g['score'] / $g['max_score'] * 100 : 0);
    }
    $overallPct  = round($overallPct / count($allGradesFlat));
    $overallColor = $overallPct >= 80 ? '#16a34a' : ($overallPct >= 60 ? '#d97706' : '#dc2626');
?>
<div class="glass rounded-3xl p-6 flex flex-col sm:flex-row items-center gap-6">
    <div class="w-24 h-24 rounded-full flex items-center justify-center shrink-0 relative"
         style="background:conic-gradient(<?php echo $overallColor; ?> <?php echo $overallPct*3.6; ?>deg, #e2e8f0 0)">
        <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center">
            <span class="display font-semibold text-lg" style="color:<?php echo $overallColor; ?>"><?php echo $overallPct; ?>%</span>
        </div>
    </div>
    <div>
        <h3 class="font-bold text-slate-800 text-lg">المتوسط العام</h3>
        <p class="text-slate-500 text-sm mt-1">
            <?php echo count($allGradesFlat); ?> تقييم في <?php echo count($courses); ?> مادة
        </p>
        <p class="text-sm font-bold mt-2" style="color:<?php echo $overallColor; ?>">
            <?php echo $overallPct >= 85 ? '🌟 ممتاز — استمر!' : ($overallPct >= 70 ? '👍 جيد — يمكنك التحسين' : '⚠️ تحتاج مراجعة'); ?>
        </p>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php studentPageEnd(); ?>
