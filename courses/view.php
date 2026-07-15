<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
    exit;
}

$courseId = (int)($_GET['id'] ?? 0);
$userId   = $_SESSION['user_id'];
$userType = $_SESSION['user_type'];
$base     = nagahBaseUrl();

$course = getCourseById($courseId);

if (!$course) {
    $pageTitle = 'كورس غير موجود';
    require __DIR__ . '/../includes/nagah/head.php';
    require __DIR__ . '/../includes/nagah/nav.php';
    echo '<div class="max-w-2xl mx-auto px-5 py-20 text-center"><div class="glass rounded-3xl p-10"><i data-lucide="alert-circle" class="mx-auto text-red-400 mb-4" style="width:48px;height:48px;"></i><h2 class="font-bold text-xl text-slate-700">الكورس غير موجود</h2></div></div>';
    require __DIR__ . '/../includes/nagah/footer.php';
    exit;
}

$hasAccess  = false;
$isEnrolled = false;
if ($userType === 'professor' && $course['professor_id'] == $userId) {
    $hasAccess = true;
} elseif ($userType === 'student') {
    $isEnrolled = isStudentEnrolled($courseId, $userId);
    $hasAccess  = $isEnrolled;
}

if (!$hasAccess) {
    $pageTitle = 'غير مصرح';
    require __DIR__ . '/../includes/nagah/head.php';
    require __DIR__ . '/../includes/nagah/nav.php';
    echo '<div class="max-w-2xl mx-auto px-5 py-20 text-center"><div class="glass rounded-3xl p-10"><i data-lucide="lock" class="mx-auto text-amber-400 mb-4" style="width:48px;height:48px;"></i><h2 class="font-bold text-xl text-slate-700">ليس لديك صلاحية الوصول لهذا الكورس</h2><a href="list.php" class="mt-6 inline-block btn-primary-nagah px-6 py-2.5 rounded-full font-bold">العودة للكورسات</a></div></div>';
    require __DIR__ . '/../includes/nagah/footer.php';
    exit;
}

$pdo = getDB();

$materialsStmt = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY upload_date DESC");
$materialsStmt->execute([$courseId]);
$materials = $materialsStmt->fetchAll();

$announcementsStmt = $pdo->prepare("SELECT a.*, u.full_name as author FROM announcements a JOIN users u ON a.created_by = u.id WHERE course_id = ? ORDER BY created_at DESC");
$announcementsStmt->execute([$courseId]);
$announcements = $announcementsStmt->fetchAll();

$questionsStmt = $pdo->prepare("SELECT q.*, u.full_name as student_name FROM questions q JOIN users u ON q.student_id = u.id WHERE course_id = ? ORDER BY created_at DESC");
$questionsStmt->execute([$courseId]);
$questions = $questionsStmt->fetchAll();

$students = [];
if ($userType === 'professor' && $course['professor_id'] == $userId) {
    $students = getCourseStudents($courseId, 'active');
}

$studentCount = getCourseStudentCount($courseId);
$pageTitle = htmlspecialchars($course['course_name']) . ' | أكاديمية ماستر';
$dashboardUrl = match($userType) {
    'admin'     => $base . '/admin_panel/dashboard.php',
    'professor' => $base . '/professor/dashboard.php',
    default     => $base . '/student/dashboard.php',
};

require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';

// Icon helper for file types
function fileIcon(string $fname): string {
    $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
    $map = ['pdf'=>'file-text','doc'=>'file-type-2','docx'=>'file-type-2','ppt'=>'presentation','pptx'=>'presentation','xls'=>'table-2','xlsx'=>'table-2','mp4'=>'video','avi'=>'video','jpg'=>'image','jpeg'=>'image','png'=>'image','zip'=>'archive'];
    return $map[$ext] ?? 'file';
}
?>

<!-- Course Banner -->
<section class="relative w-full overflow-hidden" style="background:linear-gradient(135deg,#1e3a8a,#2563EB,#3b82f6)">
    <div class="absolute inset-0 grid-dots opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-5 py-12 sm:py-16">
        <a href="<?php echo $dashboardUrl; ?>" class="inline-flex items-center gap-2 text-white/80 hover:text-white text-sm font-medium mb-6 transition">
            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i> العودة للوحة التحكم
        </a>
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
            <div>
                <span class="inline-block bg-white/20 backdrop-blur text-white text-xs font-bold px-3 py-1 rounded-full mb-3"><?php echo htmlspecialchars($course['course_code']); ?></span>
                <h1 class="display font-semibold text-3xl sm:text-4xl text-white leading-tight"><?php echo htmlspecialchars($course['course_name']); ?></h1>
                <div class="flex flex-wrap gap-4 mt-4 text-white/80 text-sm">
                    <span class="flex items-center gap-1.5"><i data-lucide="user-round" style="width:15px;height:15px;"></i> <?php echo htmlspecialchars($course['professor_name'] ?? 'غير محدد'); ?></span>
                    <span class="flex items-center gap-1.5"><i data-lucide="users" style="width:15px;height:15px;"></i> <?php echo $studentCount; ?> طالب</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="calendar" style="width:15px;height:15px;"></i> <?php echo date('Y-m-d', strtotime($course['created_at'])); ?></span>
                </div>
            </div>
            <?php if ($userType === 'professor' && $course['professor_id'] == $userId): ?>
                <a href="manage.php?id=<?php echo $courseId; ?>" class="self-start inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 backdrop-blur text-white px-5 py-2.5 rounded-full text-sm font-bold transition shrink-0">
                    <i data-lucide="settings" style="width:16px;height:16px;"></i> إدارة الكورس
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<main class="max-w-7xl mx-auto px-5 py-10 pb-20">

    <?php if ($course['description']): ?>
    <div class="glass rounded-2xl p-6 mb-8">
        <h2 class="font-bold text-slate-800 mb-2 flex items-center gap-2">
            <i data-lucide="info" style="width:16px;height:16px;color:#2563EB"></i> عن الكورس
        </h2>
        <p class="text-slate-600 leading-relaxed text-sm"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="glass rounded-3xl overflow-hidden">
        <!-- Tab nav -->
        <div id="tab-nav" class="flex overflow-x-auto border-b border-slate-100 bg-slate-50/60">
            <?php
            $tabs = [
                ['id'=>'materials',      'icon'=>'folder-open',     'label'=>'المواد الدراسية ('.count($materials).')'],
                ['id'=>'announcements',  'icon'=>'megaphone',       'label'=>'الإعلانات ('.count($announcements).')'],
                ['id'=>'questions',      'icon'=>'message-circle',  'label'=>'الأسئلة ('.count($questions).')'],
            ];
            if ($userType === 'professor') {
                $tabs[] = ['id'=>'students', 'icon'=>'users', 'label'=>'الطلاب ('.count($students).')'];
            }
            foreach ($tabs as $i => $tab):
            ?>
            <button data-tab="<?php echo $tab['id']; ?>"
                    class="tab-btn flex items-center gap-2 px-5 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-all <?php echo $i===0 ? 'tab-active' : 'border-transparent text-slate-500 hover:text-blue-600'; ?>">
                <i data-lucide="<?php echo $tab['icon']; ?>" style="width:15px;height:15px;"></i>
                <?php echo $tab['label']; ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Tab bodies -->
        <div class="p-6 sm:p-8">

            <!-- Materials -->
            <div id="tab-materials" class="tab-body">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-slate-800">المواد الدراسية</h3>
                    <?php if ($userType === 'professor'): ?>
                        <a href="<?php echo $base; ?>/materials/upload.php?course_id=<?php echo $courseId; ?>"
                           class="btn-primary-nagah inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold shadow hover:-translate-y-0.5 transition-all">
                            <i data-lucide="upload" style="width:14px;height:14px;"></i> رفع ملف
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (empty($materials)): ?>
                    <div class="text-center py-14 text-slate-400">
                        <i data-lucide="folder-open" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-40"></i>
                        <p class="text-sm">لا توجد مواد دراسية حتى الآن</p>
                    </div>
                <?php else: ?>
                    <div class="grid gap-3">
                        <?php foreach ($materials as $mat):
                            $ext = strtolower(pathinfo($mat['file_name'], PATHINFO_EXTENSION));
                            $isPreviewable = in_array($ext, ['pdf','jpg','jpeg','png']);
                        ?>
                        <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 hover:bg-blue-50/50 transition group">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-blue-600" style="background:rgba(37,99,235,.1)">
                                    <i data-lucide="<?php echo fileIcon($mat['file_name']); ?>" style="width:18px;height:18px;"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($mat['title'] ?: $mat['file_name']); ?></p>
                                    <p class="text-xs text-slate-400"><?php echo date('Y-m-d', strtotime($mat['upload_date'])); ?><?php if ($mat['description']) echo ' · ' . htmlspecialchars($mat['description']); ?></p>
                                </div>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <?php if ($isPreviewable): ?>
                                    <button onclick="previewFile('<?php echo $mat['id']; ?>','<?php echo htmlspecialchars($mat['file_name']); ?>')"
                                            class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 transition text-xs font-bold" title="معاينة">
                                        <i data-lucide="eye" style="width:15px;height:15px;"></i>
                                    </button>
                                <?php endif; ?>
                                <a href="<?php echo $base; ?>/materials/download.php?id=<?php echo $mat['id']; ?>"
                                   class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 transition" title="تحميل">
                                    <i data-lucide="download" style="width:15px;height:15px;"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Announcements -->
            <div id="tab-announcements" class="tab-body hidden">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-slate-800">الإعلانات</h3>
                    <?php if ($userType === 'professor'): ?>
                        <a href="<?php echo $base; ?>/announcements/create.php?course_id=<?php echo $courseId; ?>"
                           class="btn-primary-nagah inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold shadow hover:-translate-y-0.5 transition-all">
                            <i data-lucide="plus" style="width:14px;height:14px;"></i> إعلان جديد
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (empty($announcements)): ?>
                    <div class="text-center py-14 text-slate-400">
                        <i data-lucide="megaphone" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-40"></i>
                        <p class="text-sm">لا توجد إعلانات حتى الآن</p>
                    </div>
                <?php else: ?>
                    <div class="grid gap-4">
                        <?php foreach ($announcements as $ann): ?>
                        <div class="rounded-2xl p-5 border-r-4 <?php echo $ann['priority'] == 'high' ? 'border-red-500 bg-red-50/50' : 'border-blue-500 bg-blue-50/50'; ?>">
                            <div class="flex items-start justify-between gap-4 mb-2">
                                <h4 class="font-bold text-slate-800"><?php echo htmlspecialchars($ann['title']); ?></h4>
                                <span class="text-xs text-slate-400 shrink-0 flex items-center gap-1">
                                    <i data-lucide="clock" style="width:12px;height:12px;"></i>
                                    <?php echo date('Y-m-d', strtotime($ann['created_at'])); ?>
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($ann['content'])); ?></p>
                            <p class="text-xs text-slate-400 mt-3">بواسطة: <?php echo htmlspecialchars($ann['author']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Questions -->
            <div id="tab-questions" class="tab-body hidden">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-slate-800">الأسئلة والمناقشات</h3>
                    <?php if ($userType === 'student'): ?>
                        <a href="<?php echo $base; ?>/questions/ask.php?course_id=<?php echo $courseId; ?>"
                           class="btn-primary-nagah inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold shadow hover:-translate-y-0.5 transition-all">
                            <i data-lucide="help-circle" style="width:14px;height:14px;"></i> اطرح سؤالاً
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (empty($questions)): ?>
                    <div class="text-center py-14 text-slate-400">
                        <i data-lucide="message-circle" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-40"></i>
                        <p class="text-sm">لم يتم طرح أي أسئلة بعد</p>
                    </div>
                <?php else: ?>
                    <div class="grid gap-5">
                        <?php foreach ($questions as $q): ?>
                        <div class="glass rounded-2xl p-5">
                            <div class="flex gap-3 mb-3">
                                <span class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                                    <?php echo mb_substr($q['student_name'], 0, 1); ?>
                                </span>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-sm"><?php echo htmlspecialchars($q['student_name']); ?></span>
                                        <span class="text-xs text-slate-400"><?php echo date('Y-m-d H:i', strtotime($q['created_at'])); ?></span>
                                    </div>
                                    <p class="text-slate-700 text-sm mt-1"><?php echo nl2br(htmlspecialchars($q['question_text'])); ?></p>
                                </div>
                            </div>
                            <?php if ($q['answer_text']): ?>
                                <div class="mr-12 p-4 rounded-xl bg-green-50 border-r-4 border-green-400">
                                    <p class="text-xs font-bold text-green-700 mb-1">رد المعلم:</p>
                                    <p class="text-sm text-slate-700"><?php echo nl2br(htmlspecialchars($q['answer_text'])); ?></p>
                                </div>
                            <?php elseif ($userType === 'professor'): ?>
                                <div class="mr-12">
                                    <a href="<?php echo $base; ?>/questions/answer.php?id=<?php echo $q['id']; ?>"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold border-2 border-blue-600 text-blue-600 hover:bg-blue-50 transition">
                                        <i data-lucide="reply" style="width:13px;height:13px;"></i> رد على السؤال
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="mr-12 text-xs text-amber-600 flex items-center gap-1">
                                    <i data-lucide="clock" style="width:13px;height:13px;"></i> في انتظار الرد…
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Students (professor only) -->
            <?php if ($userType === 'professor'): ?>
            <div id="tab-students" class="tab-body hidden">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-slate-800">الطلاب المسجلين (<?php echo count($students); ?>)</h3>
                </div>
                <?php if (empty($students)): ?>
                    <div class="text-center py-14 text-slate-400">
                        <i data-lucide="users" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-40"></i>
                        <p class="text-sm">لا يوجد طلاب مسجلون حتى الآن</p>
                    </div>
                <?php else: ?>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($students as $student): ?>
                        <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 hover:bg-blue-50/40 transition">
                            <span class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shrink-0" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                                <?php echo mb_substr($student['full_name'], 0, 1); ?>
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($student['full_name']); ?></p>
                                <p class="text-xs text-slate-400 truncate"><?php echo htmlspecialchars($student['email']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,.7);backdrop-filter:blur(6px)">
    <div class="glass rounded-3xl w-full max-w-4xl flex flex-col" style="max-height:90vh">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <h3 id="previewTitle" class="font-bold text-slate-800">معاينة الملف</h3>
            <button onclick="closePreview()" class="p-2 rounded-xl hover:bg-red-50 text-red-500 transition"><i data-lucide="x" style="width:18px;height:18px;"></i></button>
        </div>
        <div id="previewContainer" class="flex-1 rounded-b-3xl overflow-hidden bg-white" style="min-height:480px;"></div>
    </div>
</div>

<style>
.tab-btn { color:#64748b; border-bottom-color:transparent; }
.tab-active { color:#2563EB !important; border-bottom-color:#2563EB !important; background:rgba(37,99,235,.06); }
</style>
<script>
document.getElementById('tab-nav').addEventListener('click', function(e){
    const btn = e.target.closest('.tab-btn');
    if (!btn) return;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('tab-active'));
    document.querySelectorAll('.tab-body').forEach(b => b.classList.add('hidden'));
    btn.classList.add('tab-active');
    document.getElementById('tab-' + btn.dataset.tab).classList.remove('hidden');
});
function previewFile(id, filename) {
    const modal = document.getElementById('previewModal');
    const container = document.getElementById('previewContainer');
    document.getElementById('previewTitle').textContent = filename;
    const ext = filename.split('.').pop().toLowerCase();
    container.innerHTML = ext === 'pdf'
        ? `<iframe src="/materials/download.php?id=${id}" style="width:100%;height:100%;min-height:480px;border:none;"></iframe>`
        : `<div class="w-full h-full flex items-center justify-center p-4"><img src="/materials/download.php?id=${id}" class="max-w-full max-h-full object-contain"></div>`;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function closePreview() {
    const modal = document.getElementById('previewModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('previewContainer').innerHTML = '';
}
</script>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
