<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$base    = nagahBaseUrl();
$userId  = getSafeUserId();
$success = '';
$error   = '';
$deleteSuccess = '';
$deleteError   = '';

// Add professor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $error = 'توكن الأمان غير صحيح';
    } else {
        $name     = getSafePost('name', '', 'string');
        $email    = getSafePost('email', '', 'email');
        $password = getSafePost('password', '', 'string');

        if (empty($name) || strlen($name) < 3) {
            $error = 'الاسم يجب أن يكون على الأقل 3 أحرف';
        } elseif (empty($email)) {
            $error = 'البريد الإلكتروني مطلوب';
        } elseif (empty($password) || strlen($password) < 8) {
            $error = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        } else {
            try {
                $pdo = getDB();
                $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $chk->execute([$email]);
                if ($chk->fetch()) {
                    $error = 'البريد الإلكتروني مسجل بالفعل';
                } else {
                    $username = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]) ?: 'prof';
                    $uchk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $uchk->execute([$username]);
                    if ($uchk->fetch()) $username .= '_' . rand(10, 99);

                    $pdo->prepare("INSERT INTO users (username, full_name, email, password, user_type, created_at) VALUES (?,?,?,?,'professor',NOW())")
                        ->execute([$username, $name, $email, hashPassword($password)]);
                    $success = 'تم إضافة المعلم بنجاح';
                }
            } catch (PDOException $e) {
                logError('Error adding professor', ['error' => $e->getMessage()]);
                $error = 'حدث خطأ في قاعدة البيانات';
            }
        }
    }
}

// Delete professor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $deleteError = 'توكن الأمان غير صحيح';
    } else {
        $deleteId = getSafePost('delete_id', null, 'int');
        if ($deleteId && $deleteId > 0) {
            try {
                $pdo = getDB();
                $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'professor'")->execute([$deleteId]);
                $deleteSuccess = 'تم حذف المعلم بنجاح';
            } catch (PDOException $e) {
                logError('Error deleting professor', ['error' => $e->getMessage()]);
                $deleteError = 'حدث خطأ عند الحذف';
            }
        }
    }
}

// Fetch professors
$professors = [];
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, full_name, email, created_at FROM users WHERE user_type = 'professor' ORDER BY created_at DESC");
    $stmt->execute();
    $professors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError('Error fetching professors', ['error' => $e->getMessage()]);
}

$pageTitle = 'إدارة المعلمين | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<div class="flex min-h-[calc(100vh-64px)]">

    <!-- ===== SIDEBAR ===== -->
    <aside class="hidden lg:flex flex-col w-60 shrink-0 border-l border-slate-100 bg-white/80 backdrop-blur sticky top-16 self-start overflow-y-auto" style="height:calc(100vh - 64px)">
        <div class="p-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-white font-bold text-sm shrink-0"
                      style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <?php echo mb_substr($_SESSION['full_name'] ?? 'A', 0, 2); ?>
                </span>
                <div class="min-w-0">
                    <p class="font-bold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></p>
                    <p class="text-xs text-blue-600 font-medium">معلم</p>
                </div>
            </div>
        </div>
        <nav class="p-3 space-y-0.5 flex-1">
            <?php
            $navLinks = [
                ['layout-dashboard', 'لوحة التحكم',    $base.'/admin/dashboard.php',        false],
                ['book-open',        'الكورسات',        $base.'/courses/list.php',            false],
                ['plus-circle',      'كورس جديد',       $base.'/courses/create.php',          false],
                ['megaphone',        'الإعلانات',       $base.'/announcements/view.php',      false],
                ['calendar',         'المواعيد',        $base.'/appointments/view.php',       false],
                ['users',            'إدارة المعلمين',  $base.'/admin/manage_professors.php', true],
                ['bell',             'الإشعارات',       $base.'/notifications/view.php',      false],
                ['user',             'الملف الشخصي',    $base.'/auth/profile.php',            false],
                ['log-out',          'تسجيل الخروج',    $base.'/auth/logout.php',             false],
            ];
            foreach ($navLinks as [$icon, $label, $url, $active]):
            ?>
            <a href="<?php echo $url; ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
               <?php echo $active
                    ? 'bg-blue-50 text-blue-700 font-bold'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'; ?>">
                <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
                <?php echo $label; ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- ===== MAIN ===== -->
    <main class="flex-1 min-w-0 py-8 px-5 sm:px-8">
        <div class="max-w-4xl">

            <!-- Page header -->
            <div class="mb-8">
                <a href="<?php echo $base; ?>/admin/dashboard.php"
                   class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 text-sm font-medium mb-3 transition">
                    <i data-lucide="arrow-right" style="width:15px;height:15px;"></i> لوحة التحكم
                </a>
                <h1 class="display font-semibold text-2xl sm:text-3xl text-slate-900 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-white"
                          style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                        <i data-lucide="users" style="width:18px;height:18px;"></i>
                    </span>
                    إدارة المعلمين
                </h1>
                <p class="text-slate-500 mt-1 text-sm">أضف وأدر حسابات المعلمين على المنصة</p>
            </div>

            <!-- Add professor form -->
            <div class="glass rounded-3xl p-6 sm:p-8 mb-8">
                <h2 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="user-plus" style="width:18px;height:18px;color:#2563EB"></i>
                    إضافة معلم جديد
                </h2>

                <?php if ($error): ?>
                <div class="rounded-xl px-4 py-3 text-sm font-medium bg-red-100 text-red-700 mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="rounded-xl px-4 py-3 text-sm font-medium bg-green-100 text-green-700 mb-6 flex items-center gap-2">
                    <i data-lucide="check-circle" style="width:16px;height:16px;"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="grid sm:grid-cols-2 gap-5" id="add-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">
                            الاسم الكامل <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" class="field-input" required minlength="3"
                               placeholder="مثال: أ. محمد حسن">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">
                            البريد الإلكتروني <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" class="field-input" required
                               placeholder="example@email.com">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">
                            كلمة المرور <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="prof-password" class="field-input" required minlength="8"
                                   placeholder="8 أحرف على الأقل">
                            <button type="button" onclick="togglePwd()" class="absolute top-1/2 -translate-y-1/2 left-3 text-slate-400 hover:text-slate-600 transition">
                                <i data-lucide="eye" id="eye-icon" style="width:17px;height:17px;"></i>
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">يجب أن تكون كلمة المرور 8 أحرف على الأقل</p>
                    </div>

                    <div class="sm:col-span-2">
                        <button type="submit" name="add"
                                class="btn-primary-nagah inline-flex items-center gap-2 px-7 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
                            <i data-lucide="user-plus" style="width:16px;height:16px;"></i>
                            إضافة المعلم
                        </button>
                    </div>
                </form>
            </div>

            <!-- Professors list -->
            <div class="glass rounded-3xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="list" style="width:17px;height:17px;color:#2563EB"></i>
                        قائمة المعلمين
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                            <?php echo count($professors); ?>
                        </span>
                    </h2>
                </div>

                <?php if ($deleteSuccess): ?>
                <div class="mx-6 mt-4 rounded-xl px-4 py-3 text-sm font-medium bg-green-100 text-green-700 flex items-center gap-2">
                    <i data-lucide="check-circle" style="width:15px;height:15px;"></i>
                    <?php echo htmlspecialchars($deleteSuccess); ?>
                </div>
                <?php endif; ?>
                <?php if ($deleteError): ?>
                <div class="mx-6 mt-4 rounded-xl px-4 py-3 text-sm font-medium bg-red-100 text-red-700">
                    <?php echo htmlspecialchars($deleteError); ?>
                </div>
                <?php endif; ?>

                <?php if (empty($professors)): ?>
                <div class="text-center py-16 text-slate-400">
                    <i data-lucide="users" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-30"></i>
                    <p class="text-sm">لا يوجد معلمون مسجلون بعد</p>
                    <p class="text-xs text-slate-300 mt-1">استخدم النموذج أعلاه لإضافة معلم</p>
                </div>
                <?php else: ?>

                <!-- Desktop table -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="background:rgba(37,99,235,.04)">
                                <th class="px-5 py-3.5 text-right font-semibold text-slate-600">#</th>
                                <th class="px-5 py-3.5 text-right font-semibold text-slate-600">المعلم</th>
                                <th class="px-5 py-3.5 text-right font-semibold text-slate-600">البريد الإلكتروني</th>
                                <th class="px-5 py-3.5 text-right font-semibold text-slate-600">تاريخ الانضمام</th>
                                <th class="px-5 py-3.5 text-center font-semibold text-slate-600">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($professors as $i => $prof): ?>
                            <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 text-slate-400 text-xs"><?php echo $i + 1; ?></td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                                              style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                                            <?php echo mb_substr($prof['full_name'], 0, 1); ?>
                                        </span>
                                        <span class="font-semibold text-slate-800">
                                            <?php echo htmlspecialchars($prof['full_name']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <a href="mailto:<?php echo htmlspecialchars($prof['email']); ?>"
                                       class="text-blue-600 hover:underline text-sm">
                                        <?php echo htmlspecialchars($prof['email']); ?>
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 text-slate-500 text-xs">
                                    <?php echo date('d/m/Y', strtotime($prof['created_at'])); ?>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <form method="POST" class="inline"
                                          onsubmit="return confirm('هل تريد حذف هذا المعلم نهائياً؟')">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="delete_id"  value="<?php echo $prof['id']; ?>">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 text-xs font-bold transition">
                                            <i data-lucide="trash-2" style="width:13px;height:13px;"></i> حذف
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile cards -->
                <div class="sm:hidden p-4 space-y-3">
                    <?php foreach ($professors as $prof): ?>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shrink-0"
                                      style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                                    <?php echo mb_substr($prof['full_name'], 0, 1); ?>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 truncate"><?php echo htmlspecialchars($prof['full_name']); ?></p>
                                    <p class="text-xs text-slate-400 truncate"><?php echo htmlspecialchars($prof['email']); ?></p>
                                </div>
                            </div>
                            <form method="POST" onsubmit="return confirm('هل تريد حذف هذا المعلم؟')">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="delete_id"  value="<?php echo $prof['id']; ?>">
                                <button type="submit"
                                        class="p-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition">
                                    <i data-lucide="trash-2" style="width:15px;height:15px;"></i>
                                </button>
                            </form>
                        </div>
                        <p class="text-xs text-slate-400 flex items-center gap-1">
                            <i data-lucide="calendar" style="width:11px;height:11px;"></i>
                            انضم في <?php echo date('d/m/Y', strtotime($prof['created_at'])); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<script>
function togglePwd() {
    const inp  = document.getElementById('prof-password');
    const icon = document.getElementById('eye-icon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        inp.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
</script>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
