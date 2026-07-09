<?php
require_once __DIR__ . '/_layout.php';
studentPageStart('الامتحانات', 'exams');

$userId = getCurrentUserId();
$pdo    = getDB();
$base   = nagahBaseUrl();

// Fetch exams for enrolled courses
$exams = [];
try {
    $stmt = $pdo->prepare("
        SELECT e.*, c.course_name, u.full_name AS professor_name,
               er.score AS my_score, er.submitted_at,
               er.status AS my_status
        FROM exams e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON c.professor_id = u.id
        JOIN course_enrollments ce ON ce.course_id = e.course_id AND ce.student_id = ?
        LEFT JOIN exam_results er ON er.exam_id = e.id AND er.student_id = ?
        WHERE ce.status = 'active'
        ORDER BY e.exam_date DESC
    ");
    $stmt->execute([$userId, $userId]);
    $exams = $stmt->fetchAll();
} catch (PDOException $e) { /* table may not exist yet */ }

$now = new DateTime();
$upcoming = array_filter($exams, fn($e) => (new DateTime($e['exam_date'])) >= $now && $e['my_status'] !== 'completed');
$past     = array_filter($exams, fn($e) => (new DateTime($e['exam_date'])) < $now || $e['my_status'] === 'completed');

$statusCfg = [
    'upcoming'  => ['bg-blue-100',  'text-blue-700',  'calendar',    'قادم'],
    'completed' => ['bg-green-100', 'text-green-700', 'check-circle','مكتمل'],
    'missed'    => ['bg-red-100',   'text-red-700',   'x-circle',    'فائت'],
];
?>

<div class="mb-8">
    <h1 class="display font-semibold text-2xl sm:text-3xl text-slate-900 flex items-center gap-3">
        <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
              style="background:linear-gradient(135deg,#dc2626,#f87171)">
            <i data-lucide="file-check-2" style="width:17px;height:17px;"></i>
        </span>
        الامتحانات
    </h1>
    <p class="text-slate-500 mt-1 text-sm"><?php echo count($upcoming); ?> قادم · <?php echo count($past); ?> سابق</p>
</div>

<?php if (empty($exams)): ?>
<div class="glass rounded-3xl p-14 text-center text-slate-400">
    <i data-lucide="file-check-2" style="width:52px;height:52px;" class="mx-auto mb-4 opacity-30"></i>
    <h3 class="font-bold text-lg text-slate-500">لا توجد امتحانات حالياً</h3>
    <p class="text-sm mt-2">ستظهر هنا امتحانات مواد اشتراكاتك</p>
</div>
<?php else: ?>

<!-- Upcoming -->
<?php if (!empty($upcoming)): ?>
<h2 class="font-bold text-slate-700 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
    <i data-lucide="calendar" style="width:14px;height:14px;color:#2563EB"></i> الامتحانات القادمة
</h2>
<div class="grid sm:grid-cols-2 gap-4 mb-8">
<?php foreach ($upcoming as $ex):
    $examDate  = new DateTime($ex['exam_date']);
    $daysLeft  = (int)$now->diff($examDate)->days;
    $isToday   = $examDate->format('Y-m-d') === $now->format('Y-m-d');
    $isSoon    = $daysLeft <= 3;
?>
<div class="glass rounded-3xl p-5 <?php echo $isSoon ? 'ring-2 ring-red-300' : ''; ?> reveal">
    <div class="flex items-start justify-between gap-3 mb-3">
        <div>
            <span class="tag-pill text-xs font-bold px-2.5 py-1 rounded-full mb-2 block w-fit">
                <?php echo htmlspecialchars($ex['course_name']); ?>
            </span>
            <h3 class="font-bold text-slate-900"><?php echo htmlspecialchars($ex['title']); ?></h3>
        </div>
        <div class="text-center shrink-0">
            <?php if ($isToday): ?>
            <span class="inline-block bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">اليوم!</span>
            <?php else: ?>
            <p class="display font-semibold text-2xl" style="color:<?php echo $isSoon ? '#dc2626' : '#2563EB'; ?>"><?php echo $daysLeft; ?></p>
            <p class="text-xs text-slate-400">يوم</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-2 text-xs">
        <div class="bg-slate-50 rounded-xl p-2.5">
            <p class="text-slate-400 mb-0.5">التاريخ</p>
            <p class="font-semibold text-slate-700"><?php echo date('d/m/Y', strtotime($ex['exam_date'])); ?></p>
        </div>
        <div class="bg-slate-50 rounded-xl p-2.5">
            <p class="text-slate-400 mb-0.5">الوقت</p>
            <p class="font-semibold text-slate-700"><?php echo !empty($ex['exam_time']) ? date('H:i', strtotime($ex['exam_time'])) : '—'; ?></p>
        </div>
        <div class="bg-slate-50 rounded-xl p-2.5">
            <p class="text-slate-400 mb-0.5">المدة</p>
            <p class="font-semibold text-slate-700"><?php echo $ex['duration'] ?? '—'; ?> دقيقة</p>
        </div>
        <div class="bg-slate-50 rounded-xl p-2.5">
            <p class="text-slate-400 mb-0.5">الدرجة الكلية</p>
            <p class="font-semibold text-slate-700"><?php echo $ex['total_marks'] ?? '—'; ?></p>
        </div>
    </div>
    <?php if (!empty($ex['description'])): ?>
    <p class="text-xs text-slate-500 mt-3 leading-relaxed"><?php echo nl2br(htmlspecialchars($ex['description'])); ?></p>
    <?php endif; ?>
    <?php if ($isToday && !empty($ex['exam_link'])): ?>
    <a href="<?php echo htmlspecialchars($ex['exam_link']); ?>" target="_blank"
       class="mt-4 btn-primary-nagah flex items-center justify-center gap-2 w-full py-2.5 rounded-xl font-bold text-sm">
        <i data-lucide="external-link" style="width:14px;height:14px;"></i> ادخل الامتحان الآن
    </a>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Past -->
<?php if (!empty($past)): ?>
<h2 class="font-bold text-slate-700 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
    <i data-lucide="history" style="width:14px;height:14px;color:#64748b"></i> الامتحانات السابقة
</h2>
<div class="glass rounded-3xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:rgba(0,0,0,.02)">
                    <th class="px-5 py-3.5 text-right font-semibold text-slate-600">الامتحان</th>
                    <th class="px-5 py-3.5 text-right font-semibold text-slate-600 hidden sm:table-cell">المادة</th>
                    <th class="px-5 py-3.5 text-right font-semibold text-slate-600">الدرجة</th>
                    <th class="px-5 py-3.5 text-right font-semibold text-slate-600 hidden md:table-cell">التاريخ</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($past as $ex):
                $pct  = ($ex['my_score'] !== null && $ex['total_marks'] > 0)
                    ? round($ex['my_score'] / $ex['total_marks'] * 100) : null;
                $pCol = $pct === null ? '#94a3b8' : ($pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#d97706' : '#dc2626'));
            ?>
            <tr class="border-t border-slate-50 hover:bg-slate-50/50 transition">
                <td class="px-5 py-3.5 font-semibold text-slate-800"><?php echo htmlspecialchars($ex['title']); ?></td>
                <td class="px-5 py-3.5 hidden sm:table-cell">
                    <span class="tag-pill text-xs font-bold px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($ex['course_name']); ?></span>
                </td>
                <td class="px-5 py-3.5">
                    <?php if ($ex['my_score'] !== null): ?>
                    <div class="flex items-center gap-2">
                        <span class="font-bold" style="color:<?php echo $pCol; ?>"><?php echo $ex['my_score']; ?>/<?php echo $ex['total_marks']; ?></span>
                        <div class="w-12 bg-slate-200 rounded-full h-1.5 hidden sm:block">
                            <div class="h-1.5 rounded-full" style="width:<?php echo $pct; ?>%;background:<?php echo $pCol; ?>"></div>
                        </div>
                    </div>
                    <?php else: ?>
                    <span class="text-xs text-slate-400">لم يُصحَّح بعد</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-400 hidden md:table-cell">
                    <?php echo date('d/m/Y', strtotime($ex['exam_date'])); ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php studentPageEnd(); ?>
