<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/subscription_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

requireRole('professor');

$userId  = getCurrentUserId();
$base    = nagahBaseUrl();
$pdo     = getDB();
$message = '';
$msgKind = '';

// كورسات المعلم
$cStmt = $pdo->prepare("SELECT id, course_name, course_code FROM courses WHERE professor_id=? ORDER BY course_name");
$cStmt->execute([$userId]);
$courses = $cStmt->fetchAll();

$courseId = (int)($_GET['course_id'] ?? ($courses[0]['id'] ?? 0));

// تحقق ملكية
if ($courseId) {
    $own = $pdo->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
    $own->execute([$courseId, $userId]);
    if (!$own->fetch()) $courseId = 0;
}

$attendanceDate = $_GET['date'] ?? date('Y-m-d');

// POST — حفظ الحضور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $cid      = (int)$_POST['course_id'];
    $date     = $_POST['lesson_date'] ?? date('Y-m-d');
    $records  = $_POST['attendance'] ?? [];

    // تحقق ملكية
    $own2 = $pdo->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
    $own2->execute([$cid, $userId]);
    if ($own2->fetch()) {
        foreach ($records as $sid => $status) {
            if (in_array($status, ['present','absent','late'])) {
                recordAttendance($cid, (int)$sid, $date, $status, $userId);
            }
        }
        $message = 'تم حفظ سجل الحضور بنجاح ✅';
        $msgKind = 'success';
        $courseId       = $cid;
        $attendanceDate = $date;
    }
}

// طلاب الكورس
$students = [];
if ($courseId) {
    $sStmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.phone, u.parent_phone
        FROM course_enrollments ce
        JOIN users u ON ce.student_id = u.id
        WHERE ce.course_id=? AND ce.status='active'
        ORDER BY u.full_name
    ");
    $sStmt->execute([$courseId]);
    $students = $sStmt->fetchAll();
}

// حضور التاريخ المختار
$todayAtt = [];
if ($courseId) {
    $aStmt = $pdo->prepare("SELECT student_id, status FROM attendance WHERE course_id=? AND lesson_date=?");
    $aStmt->execute([$courseId, $attendanceDate]);
    foreach ($aStmt->fetchAll() as $r) $todayAtt[$r['student_id']] = $r['status'];
}

// سجل الحضور الكامل للكورس (آخر 30 يوم)
$historyStmt = $pdo->prepare("
    SELECT a.lesson_date,
           SUM(a.status='present') AS present_count,
           SUM(a.status='absent')  AS absent_count,
           SUM(a.status='late')    AS late_count,
           COUNT(*) AS total
    FROM attendance a
    WHERE a.course_id=?
    GROUP BY a.lesson_date
    ORDER BY a.lesson_date DESC
    LIMIT 30
");
$history = [];
if ($courseId) { $historyStmt->execute([$courseId]); $history = $historyStmt->fetchAll(); }

$_activeSidebar = 'attendance';
$pageTitle = 'تسجيل الحضور | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>
<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_professor.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-4xl">

<div class="mb-7">
    <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
        <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
              style="background:linear-gradient(135deg,#16a34a,#4ade80)">
            <i data-lucide="user-check" style="width:17px;height:17px;"></i>
        </span>
        تسجيل الحضور
    </h1>
</div>

<!-- Course selector -->
<?php if (!empty($courses)): ?>
<div class="glass rounded-2xl p-4 mb-6 flex items-center gap-3 flex-wrap">
    <span class="text-sm font-semibold text-slate-600 shrink-0">المادة:</span>
    <?php foreach ($courses as $c): ?>
    <a href="?course_id=<?php echo $c['id']; ?>&date=<?php echo $attendanceDate; ?>"
       class="px-4 py-2 rounded-full text-sm font-bold transition
       <?php echo $courseId===(int)$c['id'] ? 'btn-primary-nagah shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
        <?php echo htmlspecialchars($c['course_name']); ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Feedback -->
<?php if ($message): ?>
<div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-6 flex items-center gap-3
    <?php echo $msgKind==='success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
    <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- Attendance form -->
<div class="glass rounded-3xl p-6 mb-8">
    <form method="POST" id="att-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">

        <div class="flex items-end gap-4 flex-wrap mb-6">
            <div>
                <label class="block text-sm font-semibold mb-1.5 text-slate-800">تاريخ الحصة</label>
                <input type="date" name="lesson_date" id="lesson-date"
                       value="<?php echo $attendanceDate; ?>"
                       max="<?php echo date('Y-m-d'); ?>"
                       class="field-input w-auto"
                       onchange="window.location='?course_id=<?php echo $courseId; ?>&date='+this.value">
            </div>
            <div class="flex gap-3 text-sm pb-1">
                <?php foreach (['present'=>['حاضر','#16a34a'],'absent'=>['غائب','#dc2626'],'late'=>['متأخر','#d97706']] as $v=>[$l,$c]): ?>
                <button type="button" onclick="markAll('<?php echo $v; ?>')"
                        class="px-4 py-2 rounded-full text-xs font-bold border-2 border-current hover:opacity-80 transition"
                        style="color:<?php echo $c; ?>;border-color:<?php echo $c; ?>">
                    الكل <?php echo $l; ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($students)): ?>
        <div class="text-center py-10 text-slate-400 text-sm">
            لا يوجد طلاب مسجلون في هذه المادة
        </div>
        <?php else: ?>
        <div class="space-y-2.5 mb-6" id="students-list">
            <?php foreach ($students as $s):
                $cur = $todayAtt[$s['id']] ?? 'present';
            ?>
            <div class="flex items-center justify-between gap-4 p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100/60 transition">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                          style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                        <?php echo mb_substr($s['full_name'],0,1); ?>
                    </span>
                    <div class="min-w-0">
                        <p class="font-semibold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($s['full_name']); ?></p>
                        <?php if ($s['parent_phone']): ?>
                        <a href="tel:<?php echo htmlspecialchars($s['parent_phone']); ?>"
                           class="text-xs text-slate-400 hover:text-green-600 flex items-center gap-1">
                            <i data-lucide="phone" style="width:10px;height:10px;"></i>
                            <?php echo htmlspecialchars($s['parent_phone']); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex gap-2 shrink-0 att-radio-group">
                    <?php foreach ([
                        'present' => ['حاضر', '#16a34a', 'bg-green-100', 'border-green-400'],
                        'absent'  => ['غائب',  '#dc2626', 'bg-red-100',   'border-red-400'],
                        'late'    => ['متأخر', '#d97706', 'bg-amber-100', 'border-amber-400'],
                    ] as $val => [$lbl, $col, $bg, $border]): ?>
                    <label class="cursor-pointer select-none">
                        <input type="radio" name="attendance[<?php echo $s['id']; ?>]"
                               value="<?php echo $val; ?>" class="sr-only peer"
                               <?php echo $cur === $val ? 'checked' : ''; ?>>
                        <span class="block px-3 py-1.5 rounded-xl text-xs font-bold border-2 transition-all
                               peer-checked:<?php echo $bg; ?> peer-checked:<?php echo $border; ?> peer-checked:border-current
                               border-transparent hover:bg-slate-200"
                              style="color:<?php echo $col; ?>">
                            <?php echo $lbl; ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="submit"
                class="btn-primary-nagah inline-flex items-center gap-2 px-7 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
            <i data-lucide="save" style="width:15px;height:15px;"></i>
            حفظ حضور <?php echo date('d/m/Y', strtotime($attendanceDate)); ?>
        </button>
        <?php endif; ?>
    </form>
</div>

<!-- History table -->
<?php if (!empty($history)): ?>
<div class="glass rounded-3xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
            <i data-lucide="history" style="width:15px;height:15px;color:#2563EB"></i>
            سجل الحضور (آخر 30 حصة)
        </h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:rgba(0,0,0,.02)">
                    <th class="px-5 py-3 text-right font-semibold text-slate-600">التاريخ</th>
                    <th class="px-5 py-3 text-center font-semibold text-green-600">حاضر</th>
                    <th class="px-5 py-3 text-center font-semibold text-red-500">غائب</th>
                    <th class="px-5 py-3 text-center font-semibold text-amber-500">متأخر</th>
                    <th class="px-5 py-3 text-center font-semibold text-slate-600">نسبة الحضور</th>
                    <th class="px-5 py-3 text-center font-semibold text-slate-600">تعديل</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($history as $h):
                $rate = $h['total'] > 0 ? round($h['present_count'] / $h['total'] * 100) : 0;
                $rCol = $rate >= 80 ? '#16a34a' : ($rate >= 60 ? '#d97706' : '#dc2626');
            ?>
            <tr class="border-t border-slate-50 hover:bg-slate-50/50 transition">
                <td class="px-5 py-3 font-medium text-slate-700">
                    <?php echo date('d/m/Y', strtotime($h['lesson_date'])); ?>
                </td>
                <td class="px-5 py-3 text-center font-bold text-green-600"><?php echo $h['present_count']; ?></td>
                <td class="px-5 py-3 text-center font-bold text-red-500"><?php echo $h['absent_count']; ?></td>
                <td class="px-5 py-3 text-center font-bold text-amber-500"><?php echo $h['late_count']; ?></td>
                <td class="px-5 py-3 text-center">
                    <span class="font-bold" style="color:<?php echo $rCol; ?>"><?php echo $rate; ?>%</span>
                </td>
                <td class="px-5 py-3 text-center">
                    <a href="?course_id=<?php echo $courseId; ?>&date=<?php echo $h['lesson_date']; ?>"
                       class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline">
                        <i data-lucide="pencil" style="width:12px;height:12px;"></i> تعديل
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

</div>
</main>
</div>

<script>
function markAll(status) {
    document.querySelectorAll(`input[type=radio][value="${status}"]`).forEach(r => r.checked = true);
}
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
