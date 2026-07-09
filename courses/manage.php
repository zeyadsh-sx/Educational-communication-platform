<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$courseId    = (int)($_GET['id'] ?? 0);
$base        = nagahBaseUrl();
$message     = '';
$messageKind = '';

$course = getCourseById($courseId);
if (!$course) {
    $pageTitle = 'غير موجود';
    require __DIR__ . '/../includes/nagah/head.php';
    require __DIR__ . '/../includes/nagah/nav.php';
    echo '<div class="max-w-xl mx-auto px-5 py-20 text-center"><div class="glass rounded-3xl p-10"><p class="text-slate-500">الكورس غير موجود.</p></div></div>';
    require __DIR__ . '/../includes/nagah/footer.php';
    exit;
}

if ($course['professor_id'] != $_SESSION['user_id']) {
    redirect('/courses/list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $studentId = (int)($_POST['student_id'] ?? 0);

    switch ($action) {
        case 'update_status':
            $status = in_array($_POST['status'] ?? '', ['active','suspended','completed']) ? $_POST['status'] : 'active';
            $result = updateStudentStatus($courseId, $studentId, $status);
            $message = $result['message']; $messageKind = $result['success'] ? 'success' : 'error';
            break;
        case 'remove_student':
            $result = unenrollStudent($courseId, $studentId);
            $message = $result['message']; $messageKind = $result['success'] ? 'success' : 'error';
            break;
        case 'update_course':
            $courseName  = trim($_POST['course_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if (empty($courseName)) { $message = 'الرجاء إدخال اسم الكورس'; $messageKind = 'error'; break; }
            $result = updateCourse($courseId, $courseName, $description);
            $message = $result['message']; $messageKind = $result['success'] ? 'success' : 'error';
            if ($result['success']) $course = getCourseById($courseId);
            break;
        case 'delete_course':
            $result = deleteCourse($courseId);
            if ($result['success']) { header('Location: list.php'); exit; }
            $message = $result['message']; $messageKind = 'error';
            break;
    }
}

$students     = getCourseStudents($courseId, null);
$studentCount = getCourseStudentCount($courseId);
$pageTitle    = 'إدارة: ' . htmlspecialchars($course['course_name']) . ' | أكاديمية ماستر';

require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<!-- Banner -->
<section class="relative overflow-hidden py-12 sm:py-14" style="background:linear-gradient(135deg,#1e3a8a,#2563EB,#3b82f6)">
    <div class="absolute inset-0 grid-dots opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-5">
        <a href="view.php?id=<?php echo $courseId; ?>" class="inline-flex items-center gap-2 text-white/80 hover:text-white text-sm font-medium mb-4 transition">
            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i> العودة للكورس
        </a>
        <h1 class="display font-semibold text-3xl text-white">إدارة الكورس</h1>
        <p class="text-white/70 mt-1"><?php echo htmlspecialchars($course['course_name']); ?></p>
    </div>
</section>

<main class="max-w-5xl mx-auto px-5 py-10 pb-20 space-y-8">

    <?php if ($message): ?>
    <div class="rounded-2xl px-5 py-3.5 text-sm font-medium <?php echo $messageKind === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php
        $suspended = 0;
        foreach ($students as $s) if ($s['status'] === 'suspended') $suspended++;
        $statsCards = [
            ['icon'=>'users',       'color'=>'#2563EB', 'bg'=>'rgba(37,99,235,.1)',  'val'=>$studentCount,        'label'=>'طلاب نشطون'],
            ['icon'=>'user-x',      'color'=>'#dc2626', 'bg'=>'rgba(220,38,38,.1)',  'val'=>$suspended,           'label'=>'موقوفون'],
            ['icon'=>'list-checks', 'color'=>'#16a34a', 'bg'=>'rgba(22,163,74,.1)',  'val'=>count($students),     'label'=>'إجمالي الطلاب'],
            ['icon'=>'calendar',    'color'=>'#d97706', 'bg'=>'rgba(217,119,6,.1)',  'val'=>date('Y-m-d', strtotime($course['created_at'])), 'label'=>'تاريخ الإنشاء'],
        ];
        foreach ($statsCards as $sc):
        ?>
        <div class="glass rounded-2xl p-5 text-center">
            <span class="w-10 h-10 rounded-xl mx-auto flex items-center justify-center mb-2" style="background:<?php echo $sc['bg']; ?>">
                <i data-lucide="<?php echo $sc['icon']; ?>" style="width:18px;height:18px;color:<?php echo $sc['color']; ?>"></i>
            </span>
            <p class="display font-semibold text-xl text-slate-800"><?php echo $sc['val']; ?></p>
            <p class="text-xs text-slate-500 mt-1"><?php echo $sc['label']; ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Edit Course Info -->
    <div class="glass rounded-3xl p-6 sm:p-8">
        <h2 class="font-bold text-slate-800 mb-6 flex items-center gap-2 text-lg">
            <i data-lucide="pencil" style="width:18px;height:18px;color:#2563EB"></i> معلومات الكورس
        </h2>
        <form method="POST" class="grid sm:grid-cols-2 gap-5">
            <input type="hidden" name="action" value="update_course">
            <div>
                <label class="block text-sm font-semibold mb-1.5 text-slate-700">كود الكورس</label>
                <input type="text" value="<?php echo htmlspecialchars($course['course_code']); ?>" class="field-input opacity-60 cursor-not-allowed" disabled>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5 text-slate-700">اسم الكورس <span class="text-red-500">*</span></label>
                <input type="text" name="course_name" class="field-input" required value="<?php echo htmlspecialchars($course['course_name']); ?>">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold mb-1.5 text-slate-700">الوصف</label>
                <textarea name="description" rows="3" class="field-input resize-none"><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="btn-primary-nagah inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
                    <i data-lucide="save" style="width:15px;height:15px;"></i> حفظ التغييرات
                </button>
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-800 flex items-center gap-2 text-lg">
                <i data-lucide="users" style="width:18px;height:18px;color:#2563EB"></i>
                الطلاب المسجلون (<?php echo count($students); ?>)
            </h2>
        </div>
        <?php if (empty($students)): ?>
            <div class="text-center py-14 text-slate-400">
                <i data-lucide="users" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-30"></i>
                <p class="text-sm">لا يوجد طلاب مسجلون حتى الآن</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-right px-5 py-3 font-semibold text-slate-600">الطالب</th>
                            <th class="text-right px-5 py-3 font-semibold text-slate-600 hidden sm:table-cell">البريد</th>
                            <th class="text-right px-5 py-3 font-semibold text-slate-600 hidden md:table-cell">تاريخ التسجيل</th>
                            <th class="text-right px-5 py-3 font-semibold text-slate-600">الحالة</th>
                            <th class="text-center px-5 py-3 font-semibold text-slate-600">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s):
                            $statusColors = ['active'=>'bg-green-100 text-green-700','suspended'=>'bg-amber-100 text-amber-700','completed'=>'bg-blue-100 text-blue-700'];
                            $statusLabels = ['active'=>'نشط','suspended'=>'موقوف','completed'=>'مكتمل'];
                        ?>
                        <tr class="border-b border-slate-50 hover:bg-slate-50/60">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                                        <?php echo mb_substr($s['full_name'], 0, 1); ?>
                                    </span>
                                    <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($s['full_name']); ?></span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-500 hidden sm:table-cell"><?php echo htmlspecialchars($s['email']); ?></td>
                            <td class="px-5 py-3 text-slate-500 hidden md:table-cell"><?php echo date('Y-m-d', strtotime($s['enrolled_at'])); ?></td>
                            <td class="px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo $statusColors[$s['status']] ?? 'bg-slate-100 text-slate-600'; ?>">
                                    <?php echo $statusLabels[$s['status']] ?? $s['status']; ?>
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="field-input text-xs py-1 px-2" style="border-radius:.75rem;width:auto;">
                                            <option value="active"    <?php echo $s['status']==='active'    ? 'selected' : ''; ?>>نشط</option>
                                            <option value="suspended" <?php echo $s['status']==='suspended' ? 'selected' : ''; ?>>موقوف</option>
                                            <option value="completed" <?php echo $s['status']==='completed' ? 'selected' : ''; ?>>مكتمل</option>
                                        </select>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('هل تريد حذف هذا الطالب من الكورس؟')">
                                        <input type="hidden" name="action" value="remove_student">
                                        <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                        <button type="submit" class="p-1.5 rounded-xl bg-red-50 text-red-500 hover:bg-red-100 transition" title="حذف">
                                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Danger Zone -->
    <div class="rounded-3xl border-2 border-red-200 p-6 bg-red-50/30">
        <h2 class="font-bold text-red-700 flex items-center gap-2 mb-3">
            <i data-lucide="triangle-alert" style="width:18px;height:18px;"></i> منطقة الخطر
        </h2>
        <p class="text-sm text-red-600 mb-5">حذف الكورس سيزيل جميع الطلاب والملفات المرتبطة به. هذا الإجراء لا يمكن التراجع عنه.</p>
        <form method="POST" onsubmit="return confirm('هل أنت متأكد تماماً من حذف هذا الكورس؟ لا يمكن التراجع!')">
            <input type="hidden" name="action" value="delete_course">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow transition">
                <i data-lucide="trash-2" style="width:15px;height:15px;"></i> حذف الكورس نهائياً
            </button>
        </form>
    </div>

</main>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
