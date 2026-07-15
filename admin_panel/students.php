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

// POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $uid    = (int)($_POST['uid'] ?? 0);

    if ($action === 'toggle_active' && $uid) {
        $cur = (int)$pdo->query("SELECT is_active FROM users WHERE id=$uid")->fetchColumn();
        $pdo->prepare("UPDATE users SET is_active=? WHERE id=?")->execute([$cur ? 0 : 1, $uid]);
        $message = $cur ? 'تم إيقاف الحساب' : 'تم تفعيل الحساب';
        $msgKind = 'success';
    } elseif ($action === 'delete' && $uid && $uid !== $userId) {
        $pdo->prepare("DELETE FROM users WHERE id=? AND user_type='student'")->execute([$uid]);
        $message = 'تم حذف الطالب بنجاح';
        $msgKind = 'success';
    }
}

$search = trim($_GET['q'] ?? '');
$params = [];
$where  = "WHERE user_type='student'";
if ($search) {
    $where   .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.student_id=u.id AND ce.status='active') AS enrolled_count FROM users u $where ORDER BY u.created_at DESC LIMIT 200");
$stmt->execute($params);
$students = $stmt->fetchAll();

$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='student'")->fetchColumn();
$activeStudents= (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='student' AND is_active=1")->fetchColumn();
$newThisMonth  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='student' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

$_activeSidebar = 'students';
$pageTitle = 'إدارة الطلاب | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_admin.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-6xl">

    <div class="mb-7">
        <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
            <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="graduation-cap" style="width:17px;height:17px;"></i>
            </span>
            إدارة الطلاب
        </h1>
        <p class="text-slate-500 mt-1 text-sm">عرض وإدارة جميع حسابات الطلاب</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-7">
        <?php foreach ([
            [$totalStudents,  'إجمالي الطلاب', 'users',          '#2563EB','rgba(37,99,235,.1)'],
            [$activeStudents, 'طلاب نشطون',    'user-check',     '#16a34a','rgba(22,163,74,.1)'],
            [$newThisMonth,   'انضموا هذا الشهر','user-plus',    '#7c3aed','rgba(124,58,237,.1)'],
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

    <!-- Feedback -->
    <?php if ($message): ?>
    <div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-6 flex items-center gap-3
        <?php echo $msgKind === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
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
                   placeholder="ابحث بالاسم أو البريد أو الهاتف…"
                   class="field-input pr-9 text-sm" style="border-radius:999px">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-full text-sm font-bold btn-primary-nagah">بحث</button>
        <?php if ($search): ?>
        <a href="?" class="px-4 py-2.5 rounded-full text-sm font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">مسح</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:rgba(37,99,235,.04)">
                        <th class="px-5 py-4 text-right font-semibold text-slate-600">الطالب</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden sm:table-cell">البريد</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">الهاتف</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-600">الكورسات</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">الحالة</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($students)): ?>
                <tr><td colspan="6" class="text-center py-12 text-slate-400 text-sm">لا توجد نتائج</td></tr>
                <?php endif; ?>
                <?php foreach ($students as $s): ?>
                <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition <?php echo !$s['is_active'] ? 'opacity-60' : ''; ?>">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                                <?php echo mb_substr($s['full_name'], 0, 1); ?>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($s['full_name']); ?></p>
                                <p class="text-xs text-slate-400"><?php echo date('d/m/Y', strtotime($s['created_at'])); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-slate-500 hidden sm:table-cell"><?php echo htmlspecialchars($s['email']); ?></td>
                    <td class="px-5 py-3.5 text-xs text-slate-500 hidden md:table-cell">
                        <?php echo $s['phone'] ? htmlspecialchars($s['phone']) : '—'; ?>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                            <?php echo $s['enrolled_count']; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo $s['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo $s['is_active'] ? 'نشط' : 'موقوف'; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="uid"    value="<?php echo $s['id']; ?>">
                                <button type="submit" title="<?php echo $s['is_active'] ? 'إيقاف' : 'تفعيل'; ?>"
                                        class="p-1.5 rounded-xl transition <?php echo $s['is_active'] ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-green-50 text-green-600 hover:bg-green-100'; ?>">
                                    <i data-lucide="<?php echo $s['is_active'] ? 'pause' : 'play'; ?>" style="width:13px;height:13px;"></i>
                                </button>
                            </form>
                            <form method="POST" class="inline" onsubmit="return confirm('حذف هذا الطالب نهائياً؟')">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="uid"    value="<?php echo $s['id']; ?>">
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
