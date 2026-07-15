<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

requireRole('admin');

$userId  = getCurrentUserId();
$base    = nagahBaseUrl();
$pdo     = getDB();
$message = '';
$msgKind = '';

// POST — delete course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action   = $_POST['action'] ?? '';
    $courseId = (int)($_POST['course_id'] ?? 0);
    if ($action === 'delete' && $courseId) {
        $pdo->prepare("DELETE FROM courses WHERE id=?")->execute([$courseId]);
        $message = 'تم حذف الكورس بنجاح';
        $msgKind = 'success';
    }
}

$search = trim($_GET['q'] ?? '');
$params = [];
$where  = '';
if ($search) {
    $where   = "WHERE c.course_name LIKE ? OR c.course_code LIKE ? OR u.full_name LIKE ?";
    $params  = ["%$search%", "%$search%", "%$search%"];
}

$stmt = $pdo->prepare("
    SELECT c.*,
           u.full_name AS professor_name,
           COUNT(DISTINCT ce.student_id) AS enrolled_count
    FROM courses c
    JOIN users u ON c.professor_id = u.id
    LEFT JOIN course_enrollments ce ON ce.course_id = c.id AND ce.status = 'active'
    $where
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 200
");
$stmt->execute($params);
$courses = $stmt->fetchAll();

$totalCourses     = (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalEnrollments = (int)$pdo->query("SELECT COUNT(*) FROM course_enrollments WHERE status='active'")->fetchColumn();
$totalMaterials   = (int)$pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();

$_activeSidebar = 'courses';
$pageTitle = 'إدارة الكورسات | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>
<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_admin.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-6xl">

    <div class="mb-7 flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
                <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
                      style="background:linear-gradient(135deg,#16a34a,#4ade80)">
                    <i data-lucide="book-open" style="width:17px;height:17px;"></i>
                </span>
                إدارة الكورسات
            </h1>
        </div>
        <a href="<?php echo $base; ?>/courses/create.php"
           class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> كورس جديد
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-7">
        <?php foreach ([
            [$totalCourses,     'إجمالي الكورسات', 'book-open',  '#16a34a','rgba(22,163,74,.1)'],
            [$totalEnrollments, 'تسجيلات نشطة',   'users',      '#2563EB','rgba(37,99,235,.1)'],
            [$totalMaterials,   'مواد دراسية',     'file-text',  '#7c3aed','rgba(124,58,237,.1)'],
        ] as [$val,$lbl,$icon,$col,$bg]): ?>
        <div class="glass rounded-2xl p-4 text-center">
            <span class="w-8 h-8 rounded-lg mx-auto flex items-center justify-center mb-1.5" style="background:<?php echo $bg; ?>">
                <i data-lucide="<?php echo $icon; ?>" style="width:15px;height:15px;color:<?php echo $col; ?>"></i>
            </span>
            <p class="display font-semibold text-2xl" style="color:<?php echo $col; ?>"><?php echo $val; ?></p>
            <p class="text-xs text-slate-500"><?php echo $lbl; ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($message): ?>
    <div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-6 flex items-center gap-3
        <?php echo $msgKind==='success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i data-lucide="<?php echo $msgKind==='success'?'check-circle':'alert-circle'; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Search -->
    <form method="GET" class="flex gap-2 mb-6">
        <div class="relative flex-1 max-w-sm">
            <i data-lucide="search" class="absolute top-1/2 -translate-y-1/2 pointer-events-none text-slate-400"
               style="width:15px;height:15px;right:12px"></i>
            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="ابحث بالاسم أو الكود أو المعلم…"
                   class="field-input pr-9 text-sm" style="border-radius:999px">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-full text-sm font-bold btn-primary-nagah">بحث</button>
    </form>

    <!-- Table -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:rgba(22,163,74,.04)">
                        <th class="px-5 py-4 text-right font-semibold text-slate-600">الكورس</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden sm:table-cell">الكود</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">المعلم</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-600">الطلاب</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">تاريخ الإنشاء</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($courses)): ?>
                <tr><td colspan="6" class="text-center py-12 text-slate-400 text-sm">لا توجد كورسات</td></tr>
                <?php endif; ?>
                <?php foreach ($courses as $c): ?>
                <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($c['course_name']); ?></p>
                        <?php if ($c['description']): ?>
                        <p class="text-xs text-slate-400 truncate max-w-xs"><?php echo htmlspecialchars(mb_substr($c['description'], 0, 60)); ?>…</p>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5 hidden sm:table-cell">
                        <span class="tag-pill text-xs font-bold px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($c['course_code']); ?></span>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-slate-600 hidden md:table-cell"><?php echo htmlspecialchars($c['professor_name']); ?></td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                            <?php echo $c['enrolled_count']; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-slate-400 hidden md:table-cell">
                        <?php echo date('d/m/Y', strtotime($c['created_at'])); ?>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $c['id']; ?>"
                               class="p-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                                <i data-lucide="eye" style="width:13px;height:13px;"></i>
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('حذف هذا الكورس نهائياً مع بياناته؟')">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action"    value="delete">
                                <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                <button type="submit"
                                        class="p-1.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition">
                                    <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>
</div>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
