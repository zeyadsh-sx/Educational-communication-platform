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
        $message = $cur ? 'تم إيقاف حساب المعلم' : 'تم تفعيل حساب المعلم';
        $msgKind = 'success';
    } elseif ($action === 'delete' && $uid) {
        $pdo->prepare("DELETE FROM users WHERE id=? AND user_type='professor'")->execute([$uid]);
        $message = 'تم حذف المعلم بنجاح';
        $msgKind = 'success';
    } elseif ($action === 'add_professor') {
        $name  = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email']     ?? '');
        $pass  = trim($_POST['password']  ?? '');
        $phone = trim($_POST['phone']     ?? '');

        if (!$name || !$email || strlen($pass) < 8) {
            $message = 'تأكد من ملء الاسم والبريد وكلمة المرور (8 أحرف+)';
            $msgKind = 'error';
        } else {
            $chk = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $message = 'البريد الإلكتروني مسجل بالفعل'; $msgKind = 'error';
            } else {
                $username = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]) ?: 'prof';
                $uchk = $pdo->prepare("SELECT id FROM users WHERE username=?");
                $uchk->execute([$username]);
                if ($uchk->fetch()) $username .= '_' . rand(10, 99);
                $pdo->prepare("INSERT INTO users (username,full_name,email,phone,password,user_type) VALUES (?,?,?,?,?,?)")
                    ->execute([$username, $name, $email, $phone, hashPassword($pass), 'professor']);
                $message = 'تم إضافة المعلم بنجاح'; $msgKind = 'success';
            }
        }
    }
}

$search = trim($_GET['q'] ?? '');
$params = [];
$where  = "WHERE u.user_type='professor'";
if ($search) {
    $where   .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare("
    SELECT u.*,
           COUNT(DISTINCT c.id)  AS courses_count,
           COUNT(DISTINCT ce.student_id) AS students_count
    FROM users u
    LEFT JOIN courses c  ON c.professor_id = u.id
    LEFT JOIN course_enrollments ce ON ce.course_id = c.id AND ce.status='active'
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT 200
");
$stmt->execute($params);
$professors = $stmt->fetchAll();

$totalProfs  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='professor'")->fetchColumn();
$activeProfs = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='professor' AND is_active=1")->fetchColumn();

$_activeSidebar = 'professors';
$pageTitle = 'إدارة المعلمين | أكاديمية ماستر';
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
                      style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                    <i data-lucide="user-cog" style="width:17px;height:17px;"></i>
                </span>
                إدارة المعلمين
            </h1>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden');document.getElementById('addModal').classList.add('flex')"
                class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all">
            <i data-lucide="user-plus" style="width:15px;height:15px;"></i> معلم جديد
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4 mb-7">
        <?php foreach ([
            [$totalProfs,  'إجمالي المعلمين', 'user-cog',   '#7c3aed','rgba(124,58,237,.1)'],
            [$activeProfs, 'معلمون نشطون',    'user-check', '#16a34a','rgba(22,163,74,.1)'],
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
                   placeholder="ابحث بالاسم أو البريد…"
                   class="field-input pr-9 text-sm" style="border-radius:999px">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-full text-sm font-bold btn-primary-nagah">بحث</button>
    </form>

    <!-- Table -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:rgba(124,58,237,.04)">
                        <th class="px-5 py-4 text-right font-semibold text-slate-600">المعلم</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden sm:table-cell">البريد</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-600">الكورسات</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-600">الطلاب</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">الحالة</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($professors)): ?>
                <tr><td colspan="6" class="text-center py-12 text-slate-400 text-sm">لا توجد نتائج</td></tr>
                <?php endif; ?>
                <?php foreach ($professors as $p): ?>
                <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition <?php echo !$p['is_active'] ? 'opacity-60' : ''; ?>">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                                  style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                                <?php echo mb_substr($p['full_name'], 0, 1); ?>
                            </span>
                            <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($p['full_name']); ?></p>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-slate-500 hidden sm:table-cell"><?php echo htmlspecialchars($p['email']); ?></td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-purple-100 text-purple-700 text-xs font-bold">
                            <?php echo $p['courses_count']; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                            <?php echo $p['students_count']; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo $p['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo $p['is_active'] ? 'نشط' : 'موقوف'; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="uid"    value="<?php echo $p['id']; ?>">
                                <button type="submit" title="<?php echo $p['is_active'] ? 'إيقاف' : 'تفعيل'; ?>"
                                        class="p-1.5 rounded-xl transition <?php echo $p['is_active'] ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-green-50 text-green-600 hover:bg-green-100'; ?>">
                                    <i data-lucide="<?php echo $p['is_active'] ? 'pause' : 'play'; ?>" style="width:13px;height:13px;"></i>
                                </button>
                            </form>
                            <form method="POST" class="inline" onsubmit="return confirm('حذف هذا المعلم نهائياً؟')">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="uid"    value="<?php echo $p['id']; ?>">
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

<!-- Add Professor Modal -->
<div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,.6);backdrop-filter:blur(6px)">
    <div class="glass rounded-3xl w-full max-w-md p-7">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="user-plus" style="width:17px;height:17px;color:#7c3aed"></i> إضافة معلم جديد
            </h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden');document.getElementById('addModal').classList.remove('flex')"
                    class="p-1.5 rounded-xl hover:bg-slate-100"><i data-lucide="x" style="width:16px;height:16px;"></i></button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="add_professor">
            <div>
                <label class="block text-sm font-semibold mb-1.5">الاسم الكامل *</label>
                <input name="full_name" required class="field-input" placeholder="أ. محمد حسن">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">البريد الإلكتروني *</label>
                <input type="email" name="email" required class="field-input" placeholder="example@email.com">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">رقم الهاتف</label>
                <input name="phone" class="field-input" placeholder="01xxxxxxxxx">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">كلمة المرور * (8 أحرف+)</label>
                <input type="password" name="password" required minlength="8" class="field-input" placeholder="••••••••">
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 rounded-full btn-primary-nagah font-bold text-sm">إضافة</button>
                <button type="button"
                        onclick="document.getElementById('addModal').classList.add('hidden');document.getElementById('addModal').classList.remove('flex')"
                        class="flex-1 py-2.5 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">إلغاء</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
