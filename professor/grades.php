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

$cStmt = $pdo->prepare("SELECT id, course_name, course_code FROM courses WHERE professor_id=? ORDER BY course_name");
$cStmt->execute([$userId]);
$courses = $cStmt->fetchAll();

$courseId = (int)($_GET['course_id'] ?? ($courses[0]['id'] ?? 0));
if ($courseId) {
    $own = $pdo->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
    $own->execute([$courseId, $userId]);
    if (!$own->fetch()) $courseId = 0;
}

// POST — add / delete grade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_grade') {
        $sid      = (int)$_POST['student_id'];
        $cid      = (int)$_POST['course_id'];
        $title    = trim($_POST['title'] ?? '');
        $type     = in_array($_POST['grade_type']??'', ['exam','homework','quiz','participation']) ? $_POST['grade_type'] : 'exam';
        $score    = (float)($_POST['score'] ?? 0);
        $maxScore = (float)($_POST['max_score'] ?? 100);
        $notes    = trim($_POST['notes'] ?? '');

        $ownC = $pdo->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
        $ownC->execute([$cid, $userId]);
        if ($ownC->fetch() && $title && $maxScore > 0) {
            addGrade($cid, $sid, $title, $type, $score, $maxScore, $userId, $notes ?: null);
            $message = 'تم إضافة الدرجة بنجاح'; $msgKind = 'success'; $courseId = $cid;
        } else {
            $message = 'بيانات غير صحيحة'; $msgKind = 'error';
        }

    } elseif ($action === 'delete_grade') {
        $gid = (int)$_POST['grade_id'];
        $pdo->prepare("DELETE FROM grades WHERE id=? AND graded_by=?")->execute([$gid, $userId]);
        $message = 'تم حذف الدرجة'; $msgKind = 'success';
    }
}

// طلاب الكورس
$students = [];
if ($courseId) {
    $sStmt = $pdo->prepare("
        SELECT u.id, u.full_name FROM course_enrollments ce
        JOIN users u ON ce.student_id=u.id
        WHERE ce.course_id=? AND ce.status='active' ORDER BY u.full_name
    ");
    $sStmt->execute([$courseId]);
    $students = $sStmt->fetchAll();
}

// درجات الكورس
$grades = $courseId ? getCourseGrades($courseId) : [];

$typeLabels = ['exam'=>'امتحان','homework'=>'واجب','quiz'=>'كويز','participation'=>'مشاركة'];
$typeCols   = ['exam'=>'#2563EB','homework'=>'#7c3aed','quiz'=>'#16a34a','participation'=>'#d97706'];
$typeBg     = ['exam'=>'bg-blue-100','homework'=>'bg-purple-100','quiz'=>'bg-green-100','participation'=>'bg-amber-100'];

$_activeSidebar = 'grades';
$pageTitle = 'الدرجات | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>
<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_professor.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-5xl">

<div class="mb-7 flex items-start justify-between flex-wrap gap-4">
    <div>
        <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
            <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="bar-chart-2" style="width:17px;height:17px;"></i>
            </span>
            الدرجات
        </h1>
    </div>
    <button onclick="openAddModal()"
            class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all">
        <i data-lucide="plus" style="width:15px;height:15px;"></i> إضافة درجة
    </button>
</div>

<!-- Course selector -->
<?php if (!empty($courses)): ?>
<div class="glass rounded-2xl p-4 mb-6 flex items-center gap-3 flex-wrap">
    <span class="text-sm font-semibold text-slate-600 shrink-0">المادة:</span>
    <?php foreach ($courses as $c): ?>
    <a href="?course_id=<?php echo $c['id']; ?>"
       class="px-4 py-2 rounded-full text-sm font-bold transition
       <?php echo $courseId===(int)$c['id'] ? 'btn-primary-nagah shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
        <?php echo htmlspecialchars($c['course_name']); ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($message): ?>
<div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-6 flex items-center gap-3
    <?php echo $msgKind==='success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
    <i data-lucide="<?php echo $msgKind==='success'?'check-circle':'alert-circle'; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- Stats row -->
<?php if (!empty($grades)): ?>
<?php
$statsByType = [];
foreach ($grades as $g) {
    $t = $g['grade_type'];
    if (!isset($statsByType[$t])) $statsByType[$t] = ['count'=>0,'sum'=>0,'maxSum'=>0];
    $statsByType[$t]['count']++;
    $statsByType[$t]['sum']    += $g['score'];
    $statsByType[$t]['maxSum'] += $g['max_score'];
}
?>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-7">
    <?php foreach (['exam','homework','quiz','participation'] as $t):
        $st   = $statsByType[$t] ?? ['count'=>0,'sum'=>0,'maxSum'=>0];
        $avg  = $st['maxSum'] > 0 ? round($st['sum']/$st['maxSum']*100) : 0;
        $col  = $typeCols[$t] ?? '#64748b';
        $bg   = 'rgba('.implode(',',sscanf(ltrim($col,'#'),'%02x%02x%02x')).',.1)';
    ?>
    <div class="glass rounded-2xl p-4 text-center">
        <span class="w-8 h-8 rounded-lg mx-auto flex items-center justify-center mb-1.5"
              style="background:<?php echo $bg; ?>">
            <i data-lucide="award" style="width:14px;height:14px;color:<?php echo $col; ?>"></i>
        </span>
        <p class="display font-semibold text-xl" style="color:<?php echo $col; ?>">
            <?php echo $st['count'] > 0 ? $avg.'%' : '—'; ?>
        </p>
        <p class="text-xs text-slate-500"><?php echo $typeLabels[$t]; ?> (<?php echo $st['count']; ?>)</p>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Grades table -->
<div class="glass rounded-3xl overflow-hidden">
    <?php if (empty($grades)): ?>
    <div class="text-center py-14 text-slate-400">
        <i data-lucide="bar-chart-2" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-30"></i>
        <p class="text-sm mb-4">لا توجد درجات مسجلة بعد</p>
        <button onclick="openAddModal()"
                class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> أضف أول درجة
        </button>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:rgba(37,99,235,.04)">
                    <th class="px-5 py-4 text-right font-semibold text-slate-600">الطالب</th>
                    <th class="px-5 py-4 text-right font-semibold text-slate-600">التقييم</th>
                    <th class="px-5 py-4 text-right font-semibold text-slate-600">النوع</th>
                    <th class="px-5 py-4 text-right font-semibold text-slate-600">الدرجة</th>
                    <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">التاريخ</th>
                    <th class="px-5 py-4 text-center font-semibold text-slate-600">حذف</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($grades as $g):
                $pct  = $g['max_score'] > 0 ? round($g['score']/$g['max_score']*100) : 0;
                $bCol = $pct>=80?'#16a34a':($pct>=60?'#d97706':'#dc2626');
                $t    = $g['grade_type'];
            ?>
            <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition">
                <td class="px-5 py-3.5 font-semibold text-slate-800"><?php echo htmlspecialchars($g['student_name']); ?></td>
                <td class="px-5 py-3.5 text-slate-700"><?php echo htmlspecialchars($g['title']); ?></td>
                <td class="px-5 py-3.5">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full <?php echo $typeBg[$t]??'bg-slate-100'; ?>"
                          style="color:<?php echo $typeCols[$t]??'#64748b'; ?>">
                        <?php echo $typeLabels[$t]??$t; ?>
                    </span>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        <span class="font-bold" style="color:<?php echo $bCol; ?>"><?php echo $g['score']; ?></span>
                        <span class="text-slate-400 text-xs">/<?php echo $g['max_score']; ?></span>
                        <div class="w-14 bg-slate-200 rounded-full h-1.5 hidden sm:block">
                            <div class="h-1.5 rounded-full" style="width:<?php echo $pct; ?>%;background:<?php echo $bCol; ?>"></div>
                        </div>
                        <span class="text-xs font-bold hidden sm:block" style="color:<?php echo $bCol; ?>"><?php echo $pct; ?>%</span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-400 hidden md:table-cell"><?php echo date('d/m/Y', strtotime($g['created_at'])); ?></td>
                <td class="px-5 py-3.5 text-center">
                    <form method="POST" onsubmit="return confirm('حذف هذه الدرجة؟')" class="inline">
                        <input type="hidden" name="csrf_token"  value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action"      value="delete_grade">
                        <input type="hidden" name="grade_id"    value="<?php echo $g['id']; ?>">
                        <button type="submit" class="p-1.5 rounded-xl bg-red-50 text-red-500 hover:bg-red-100 transition">
                            <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

</div>
</main>
</div>

<!-- Add Grade Modal -->
<div id="gradeModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,.6);backdrop-filter:blur(6px)">
    <div class="glass rounded-3xl w-full max-w-md p-7">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="plus-circle" style="width:17px;height:17px;color:#2563EB"></i> إضافة درجة
            </h3>
            <button onclick="closeAddModal()" class="p-1.5 rounded-xl hover:bg-slate-100">
                <i data-lucide="x" style="width:16px;height:16px;"></i>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="add_grade">
            <input type="hidden" name="course_id" value="<?php echo $courseId; ?>">
            <div>
                <label class="block text-sm font-semibold mb-1.5">الطالب <span class="text-red-500">*</span></label>
                <select name="student_id" class="field-input" required>
                    <option value="">— اختر طالباً —</option>
                    <?php foreach ($students as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">عنوان التقييم <span class="text-red-500">*</span></label>
                <input name="title" required class="field-input" placeholder="مثال: امتحان الفصل الأول">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">النوع</label>
                    <select name="grade_type" class="field-input">
                        <?php foreach ($typeLabels as $v=>$l): ?>
                        <option value="<?php echo $v; ?>"><?php echo $l; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">من</label>
                    <input type="number" name="max_score" value="100" min="1" step="0.5" class="field-input" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">الدرجة <span class="text-red-500">*</span></label>
                <input type="number" name="score" min="0" step="0.5" class="field-input" required placeholder="85">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="field-input resize-none" placeholder="اختياري…"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 rounded-full btn-primary-nagah font-bold text-sm">حفظ الدرجة</button>
                <button type="button" onclick="closeAddModal()"
                        class="flex-1 py-2.5 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">إلغاء</button>
            </div>
        </form>
    </div>
</div>
<script>
function openAddModal()  { const m=document.getElementById('gradeModal'); m.classList.remove('hidden'); m.classList.add('flex'); if(typeof lucide!=='undefined')lucide.createIcons(); }
function closeAddModal() { const m=document.getElementById('gradeModal'); m.classList.add('hidden'); m.classList.remove('flex'); }
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
