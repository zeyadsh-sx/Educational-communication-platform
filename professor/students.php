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

// اختيار الكورس
$courseId = (int)($_GET['course_id'] ?? 0);

// كورسات المعلم
$myCourses = $pdo->prepare("SELECT id, course_name, course_code FROM courses WHERE professor_id=? ORDER BY course_name");
$myCourses->execute([$userId]);
$courses = $myCourses->fetchAll();

if (!$courseId && !empty($courses)) $courseId = (int)$courses[0]['id'];

// التحقق من ملكية الكورس
if ($courseId) {
    $ownerCheck = $pdo->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
    $ownerCheck->execute([$courseId, $userId]);
    if (!$ownerCheck->fetch()) { $courseId = 0; }
}

// POST — تسجيل حضور أو درجة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'record_attendance') {
        $date    = $_POST['lesson_date'] ?? date('Y-m-d');
        $records = $_POST['attendance']  ?? [];
        foreach ($records as $sid => $status) {
            if (in_array($status, ['present','absent','late'])) {
                recordAttendance($courseId, (int)$sid, $date, $status, $userId);
            }
        }
        $message = 'تم حفظ الحضور بنجاح'; $msgKind = 'success';

    } elseif ($action === 'add_grade') {
        $sid      = (int)($_POST['student_id']  ?? 0);
        $title    = trim($_POST['title']         ?? '');
        $type     = $_POST['grade_type']         ?? 'exam';
        $score    = (float)($_POST['score']      ?? 0);
        $maxScore = (float)($_POST['max_score']  ?? 100);
        $notes    = trim($_POST['notes']         ?? '');

        if ($sid && $title && $maxScore > 0) {
            addGrade($courseId, $sid, $title,
                     in_array($type,['exam','homework','quiz','participation']) ? $type : 'exam',
                     $score, $maxScore, $userId, $notes ?: null);
            $message = 'تم إضافة الدرجة بنجاح'; $msgKind = 'success';
        } else {
            $message = 'تحقق من البيانات المدخلة'; $msgKind = 'error';
        }
    }
}

// جلب طلاب الكورس المختار
$students = [];
if ($courseId) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, u.phone, u.parent_phone,
               ce.enrolled_at, ce.status AS enroll_status
        FROM course_enrollments ce
        JOIN users u ON ce.student_id = u.id
        WHERE ce.course_id = ? AND ce.status = 'active'
        ORDER BY u.full_name
    ");
    $stmt->execute([$courseId]);
    $students = $stmt->fetchAll();
}

// حضور اليوم المختار
$attendanceDate = $_GET['date'] ?? date('Y-m-d');
$todayAttendance = [];
if ($courseId) {
    $attStmt = $pdo->prepare("SELECT student_id, status FROM attendance WHERE course_id=? AND lesson_date=?");
    $attStmt->execute([$courseId, $attendanceDate]);
    foreach ($attStmt->fetchAll() as $r) $todayAttendance[$r['student_id']] = $r['status'];
}

// درجات الكورس
$grades = $courseId ? getCourseGrades($courseId) : [];

// Tab
$tab = in_array($_GET['tab'] ?? '', ['attendance','grades']) ? $_GET['tab'] : 'students';

$_activeSidebar = 'students';
$pageTitle = 'إدارة الطلاب | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_professor.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-5xl">

<!-- Header -->
<div class="mb-7 flex items-start justify-between flex-wrap gap-4">
    <div>
        <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
            <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="users" style="width:17px;height:17px;"></i>
            </span>
            إدارة الطلاب
        </h1>
        <p class="text-slate-500 mt-1 text-sm">الحضور — الدرجات — التواصل مع ولي الأمر</p>
    </div>
</div>

<!-- Course selector -->
<?php if (!empty($courses)): ?>
<div class="glass rounded-2xl p-4 mb-6 flex items-center gap-4 flex-wrap">
    <span class="text-sm font-semibold text-slate-600 flex items-center gap-2 shrink-0">
        <i data-lucide="book" style="width:15px;height:15px;color:#2563EB"></i> المادة:
    </span>
    <div class="flex gap-2 flex-wrap">
        <?php foreach ($courses as $c): ?>
        <a href="?course_id=<?php echo $c['id']; ?>&tab=<?php echo $tab; ?>"
           class="px-4 py-2 rounded-full text-sm font-bold transition-all
           <?php echo $courseId === (int)$c['id'] ? 'btn-primary-nagah shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
            <?php echo htmlspecialchars($c['course_name']); ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Feedback -->
<?php if ($message): ?>
<div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-6 flex items-center gap-3
    <?php echo $msgKind === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
    <i data-lucide="<?php echo $msgKind==='success' ? 'check-circle' : 'alert-circle'; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="flex gap-1 mb-6 bg-slate-100 p-1 rounded-2xl w-fit">
    <?php foreach ([
        ['students',   'users',       'الطلاب ('    . count($students) . ')'],
        ['attendance', 'user-check',  'الحضور'],
        ['grades',     'bar-chart-2', 'الدرجات (' . count($grades) . ')'],
    ] as [$t, $icon, $label]): ?>
    <a href="?course_id=<?php echo $courseId; ?>&tab=<?php echo $t; ?>"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all
       <?php echo $tab === $t ? 'bg-white shadow text-blue-700' : 'text-slate-500 hover:text-slate-700'; ?>">
        <i data-lucide="<?php echo $icon; ?>" style="width:14px;height:14px;"></i>
        <?php echo $label; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- ═══════ TAB: STUDENTS ═══════ -->
<?php if ($tab === 'students'): ?>
<?php if (empty($students)): ?>
<div class="glass rounded-3xl p-14 text-center text-slate-400">
    <i data-lucide="users" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-30"></i>
    <p>لا يوجد طلاب مسجلون في هذه المادة</p>
</div>
<?php else: ?>
<div class="glass rounded-3xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:rgba(37,99,235,.04)">
                    <th class="px-5 py-4 text-right font-semibold text-slate-600">الطالب</th>
                    <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden sm:table-cell">البريد</th>
                    <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">هاتف ولي الأمر</th>
                    <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">نسبة الحضور</th>
                    <th class="px-5 py-4 text-center font-semibold text-slate-600">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s):
                    $attRate = getStudentAttendanceRate($s['id'], $courseId);
                    $attColor = $attRate >= 80 ? '#16a34a' : ($attRate >= 60 ? '#d97706' : '#dc2626');
                ?>
                <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                                <?php echo mb_substr($s['full_name'], 0, 1); ?>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($s['full_name']); ?></p>
                                <p class="text-xs text-slate-400"><?php echo date('d/m/Y', strtotime($s['enrolled_at'])); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 hidden sm:table-cell text-slate-500 text-xs"><?php echo htmlspecialchars($s['email']); ?></td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <?php if (!empty($s['parent_phone'])): ?>
                        <a href="tel:<?php echo htmlspecialchars($s['parent_phone']); ?>"
                           class="flex items-center gap-1.5 text-xs font-bold text-green-700 hover:underline">
                            <i data-lucide="phone" style="width:12px;height:12px;"></i>
                            <?php echo htmlspecialchars($s['parent_phone']); ?>
                        </a>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/','',$s['parent_phone']); ?>"
                           target="_blank"
                           class="flex items-center gap-1 text-[10px] text-green-600 mt-0.5 hover:underline">
                            <i data-lucide="message-circle" style="width:10px;height:10px;"></i> واتساب
                        </a>
                        <?php else: ?>
                        <span class="text-xs text-slate-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-slate-200 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full" style="width:<?php echo $attRate; ?>%;background:<?php echo $attColor; ?>"></div>
                            </div>
                            <span class="text-xs font-bold" style="color:<?php echo $attColor; ?>"><?php echo $attRate; ?>%</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <button onclick="openGradeModal(<?php echo $s['id']; ?>, '<?php echo htmlspecialchars(addslashes($s['full_name'])); ?>')"
                                    class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-xs font-bold hover:bg-blue-100 transition flex items-center gap-1">
                                <i data-lucide="plus" style="width:11px;height:11px;"></i> درجة
                            </button>
                            <?php if (!empty($s['parent_phone'])): ?>
                            <a href="tel:<?php echo htmlspecialchars($s['parent_phone']); ?>"
                               class="px-3 py-1.5 rounded-xl bg-green-50 text-green-700 text-xs font-bold hover:bg-green-100 transition flex items-center gap-1">
                                <i data-lucide="phone" style="width:11px;height:11px;"></i> اتصال
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ═══════ TAB: ATTENDANCE ═══════ -->
<?php elseif ($tab === 'attendance'): ?>
<div class="glass rounded-3xl p-6 mb-6">
    <form method="POST" id="att-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="record_attendance">

        <div class="flex items-center gap-4 flex-wrap mb-6">
            <div>
                <label class="text-sm font-semibold text-slate-700 block mb-1.5">تاريخ الحصة</label>
                <input type="date" name="lesson_date" value="<?php echo $attendanceDate; ?>"
                       class="field-input w-auto" max="<?php echo date('Y-m-d'); ?>"
                       onchange="this.form.submit()">
                <input type="hidden" name="action" value="none">
            </div>
            <p class="text-sm text-slate-500 mt-5"><?php echo count($students); ?> طالب</p>
        </div>
        <input type="hidden" name="action" value="record_attendance">

        <?php if (empty($students)): ?>
        <p class="text-center text-slate-400 py-8 text-sm">لا يوجد طلاب</p>
        <?php else: ?>
        <div class="grid gap-3">
            <?php foreach ($students as $s):
                $currentStatus = $todayAttendance[$s['id']] ?? 'present';
            ?>
            <div class="flex items-center justify-between gap-4 p-3.5 rounded-2xl bg-slate-50 hover:bg-blue-50/30 transition">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                          style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                        <?php echo mb_substr($s['full_name'], 0, 1); ?>
                    </span>
                    <p class="font-semibold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($s['full_name']); ?></p>
                </div>
                <div class="flex gap-2 shrink-0">
                    <?php foreach (['present'=>['حاضر','#16a34a','bg-green-100'], 'absent'=>['غائب','#dc2626','bg-red-100'], 'late'=>['متأخر','#d97706','bg-amber-100']] as $val => [$lbl, $col, $bg]): ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="attendance[<?php echo $s['id']; ?>]"
                               value="<?php echo $val; ?>" class="sr-only peer"
                               <?php echo $currentStatus === $val ? 'checked' : ''; ?>>
                        <span class="block px-3 py-1.5 rounded-xl text-xs font-bold border-2 border-transparent transition-all
                               peer-checked:border-current"
                              style="background:<?php echo $currentStatus === $val ? $bg : 'white'; ?>;color:<?php echo $col; ?>">
                            <?php echo $lbl; ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-5">
            <button type="submit" name="action" value="record_attendance"
                    class="btn-primary-nagah inline-flex items-center gap-2 px-7 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
                <i data-lucide="save" style="width:15px;height:15px;"></i> حفظ الحضور
            </button>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- ═══════ TAB: GRADES ═══════ -->
<?php elseif ($tab === 'grades'): ?>
<div class="glass rounded-3xl overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100">
        <h2 class="font-bold text-slate-800 flex items-center gap-2">
            <i data-lucide="bar-chart-2" style="width:17px;height:17px;color:#2563EB"></i>
            سجل الدرجات
        </h2>
    </div>
    <?php if (empty($grades)): ?>
    <div class="text-center py-14 text-slate-400">
        <i data-lucide="bar-chart-2" style="width:44px;height:44px;" class="mx-auto mb-3 opacity-30"></i>
        <p class="text-sm">لا توجد درجات مسجلة بعد</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:rgba(37,99,235,.04)">
                    <th class="px-5 py-3.5 text-right font-semibold text-slate-600">الطالب</th>
                    <th class="px-5 py-3.5 text-right font-semibold text-slate-600">العنوان</th>
                    <th class="px-5 py-3.5 text-right font-semibold text-slate-600">النوع</th>
                    <th class="px-5 py-3.5 text-right font-semibold text-slate-600">الدرجة</th>
                    <th class="px-5 py-3.5 text-right font-semibold text-slate-600 hidden md:table-cell">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $typeLabels = ['exam'=>'امتحان','homework'=>'واجب','quiz'=>'كويز','participation'=>'مشاركة'];
                $typeColors = ['exam'=>'#2563EB','homework'=>'#7c3aed','quiz'=>'#16a34a','participation'=>'#d97706'];
                foreach ($grades as $g):
                    $pct = $g['max_score'] > 0 ? round($g['score'] / $g['max_score'] * 100) : 0;
                    $barColor = $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#d97706' : '#dc2626');
                ?>
                <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition">
                    <td class="px-5 py-3.5 font-semibold text-slate-800"><?php echo htmlspecialchars($g['student_name']); ?></td>
                    <td class="px-5 py-3.5 text-slate-700"><?php echo htmlspecialchars($g['title']); ?></td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100" style="color:<?php echo $typeColors[$g['grade_type']] ?? '#2563EB'; ?>">
                            <?php echo $typeLabels[$g['grade_type']] ?? $g['grade_type']; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-800"><?php echo $g['score']; ?>/<?php echo $g['max_score']; ?></span>
                            <div class="w-14 bg-slate-200 rounded-full h-1.5 hidden sm:block">
                                <div class="h-1.5 rounded-full" style="width:<?php echo $pct; ?>%;background:<?php echo $barColor; ?>"></div>
                            </div>
                            <span class="text-xs font-bold hidden sm:block" style="color:<?php echo $barColor; ?>"><?php echo $pct; ?>%</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-slate-400 hidden md:table-cell">
                        <?php echo date('d/m/Y', strtotime($g['created_at'])); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

</div>
</main>
</div>

<!-- Add Grade Modal -->
<div id="gradeModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,.6);backdrop-filter:blur(6px)">
    <div class="glass rounded-3xl w-full max-w-md p-7">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="plus-circle" style="width:17px;height:17px;color:#2563EB"></i>
                إضافة درجة لـ <span id="grade-student-name" class="text-blue-600"></span>
            </h3>
            <button onclick="closeGradeModal()" class="p-1.5 rounded-xl hover:bg-slate-100 text-slate-400"><i data-lucide="x" style="width:17px;height:17px;"></i></button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="add_grade">
            <input type="hidden" name="student_id" id="grade-student-id">

            <div>
                <label class="block text-sm font-semibold mb-1.5">عنوان التقييم</label>
                <input type="text" name="title" class="field-input" required placeholder="مثال: امتحان الفصل الأول">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">النوع</label>
                <select name="grade_type" class="field-input">
                    <option value="exam">امتحان</option>
                    <option value="homework">واجب</option>
                    <option value="quiz">كويز</option>
                    <option value="participation">مشاركة</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">الدرجة</label>
                    <input type="number" name="score" step="0.5" min="0" class="field-input" required placeholder="85">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">من</label>
                    <input type="number" name="max_score" step="0.5" min="1" value="100" class="field-input" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="field-input resize-none" placeholder="اختياري…"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 rounded-full btn-primary-nagah font-bold text-sm">حفظ الدرجة</button>
                <button type="button" onclick="closeGradeModal()" class="flex-1 py-2.5 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGradeModal(id, name) {
    document.getElementById('grade-student-id').value = id;
    document.getElementById('grade-student-name').textContent = name;
    const m = document.getElementById('gradeModal');
    m.classList.remove('hidden'); m.classList.add('flex');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function closeGradeModal() {
    const m = document.getElementById('gradeModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
