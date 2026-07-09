<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
    exit;
}

$userId      = getCurrentUserId();
$base        = nagahBaseUrl();
$pdo         = getDB();
$message     = '';
$messageKind = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'توكن الأمان غير صحيح.';
        $messageKind = 'error';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');

        if (empty($full_name)) {
            $message = 'الاسم بالكامل مطلوب.';
            $messageKind = 'error';
        } else {
            try {
                // Build update dynamically based on available columns
                $checkCols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND TABLE_SCHEMA=DATABASE()")->fetchAll(PDO::FETCH_COLUMN);
                $sets = ['full_name = ?'];
                $vals = [$full_name];
                if (in_array('phone', $checkCols) && $phone !== '') {
                    $sets[] = 'phone = ?';
                    $vals[] = $phone;
                }
                $vals[] = $userId;
                $pdo->prepare("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?")->execute($vals);
                $_SESSION['full_name'] = $full_name;
                $message = 'تم تحديث الملف الشخصي بنجاح.';
                $messageKind = 'success';
            } catch (Exception $e) {
                $message = 'حدث خطأ أثناء التحديث.';
                $messageKind = 'error';
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Stats
$coursesCount      = 0;
$notifCount        = 0;
$enrollmentsCount  = 0;
try {
    if ($user['user_type'] === 'professor') {
        $coursesCount = (int) $pdo->prepare("SELECT COUNT(*) FROM courses WHERE professor_id = ?")->execute([$userId]) ? $pdo->query("SELECT COUNT(*) FROM courses WHERE professor_id = $userId")->fetchColumn() : 0;
    } else {
        $enrollmentsCount = (int) $pdo->query("SELECT COUNT(*) FROM course_enrollments WHERE student_id = $userId")->fetchColumn();
    }
    $notifCount = (int) $pdo->query("SELECT COUNT(*) FROM notifications WHERE user_id = $userId AND is_read = FALSE")->fetchColumn();
} catch (Exception $e) {}

$initials     = mb_substr($user['full_name'] ?? 'U', 0, 2);
$dashboardUrl = $user['user_type'] === 'professor' ? $base . '/admin/dashboard.php' : $base . '/student/dashboard.php';
$pageTitle    = 'الملف الشخصي | أكاديمية ماستر';

require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<!-- Banner -->
<section class="relative overflow-hidden py-14 sm:py-20" style="background:linear-gradient(135deg,#1e3a8a,#2563EB,#3b82f6)">
    <div class="absolute inset-0 grid-dots opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-5 flex flex-col sm:flex-row items-center sm:items-end gap-6">
        <!-- Avatar -->
        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl flex items-center justify-center text-3xl font-bold text-white shadow-2xl shrink-0" style="background:linear-gradient(135deg,rgba(255,255,255,.3),rgba(255,255,255,.1));backdrop-filter:blur(10px);border:3px solid rgba(255,255,255,.4)">
            <?php echo htmlspecialchars($initials); ?>
        </div>
        <div class="text-center sm:text-right">
            <h1 class="display font-semibold text-2xl sm:text-3xl text-white"><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p class="text-white/70 mt-1 flex items-center justify-center sm:justify-start gap-2">
                <i data-lucide="mail" style="width:14px;height:14px;"></i>
                <?php echo htmlspecialchars($user['email']); ?>
            </p>
            <span class="mt-2 inline-flex items-center gap-1.5 bg-white/15 backdrop-blur text-white text-xs font-bold px-3 py-1 rounded-full">
                <i data-lucide="<?php echo $user['user_type'] === 'professor' ? 'user-cog' : 'graduation-cap'; ?>" style="width:12px;height:12px;"></i>
                <?php echo $user['user_type'] === 'professor' ? 'معلم' : 'طالب'; ?>
            </span>
        </div>
    </div>
</section>

<main class="max-w-5xl mx-auto px-5 py-10 pb-20">
    <div class="grid lg:grid-cols-3 gap-8">

        <!-- Left: Edit form -->
        <div class="lg:col-span-2">
            <div class="glass rounded-3xl p-6 sm:p-8">
                <h2 class="font-bold text-slate-800 text-lg mb-6 flex items-center gap-2">
                    <i data-lucide="pencil" style="width:18px;height:18px;color:#2563EB"></i> تعديل المعلومات
                </h2>

                <?php if ($message): ?>
                <div class="rounded-xl px-4 py-3 text-sm font-medium mb-6 <?php echo $messageKind === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">الاسم بالكامل <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" class="field-input" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">البريد الإلكتروني</label>
                        <input type="email" class="field-input opacity-60 cursor-not-allowed" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <p class="text-xs text-slate-400 mt-1">البريد الإلكتروني غير قابل للتعديل</p>
                    </div>

                    <?php if (!empty($user['phone'])): ?>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">رقم الهاتف</label>
                        <input type="tel" name="phone" class="field-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="01xxxxxxxxx">
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($user['grade'])): ?>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">الصف الدراسي</label>
                        <input type="text" class="field-input opacity-60 cursor-not-allowed" value="<?php echo htmlspecialchars($user['grade']); ?>" disabled>
                    </div>
                    <?php endif; ?>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary-nagah inline-flex items-center gap-2 px-7 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
                            <i data-lucide="save" style="width:16px;height:16px;"></i> حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Stats & quick links -->
        <div class="space-y-5">
            <!-- Quick stats -->
            <div class="glass rounded-3xl p-6">
                <h3 class="font-bold text-slate-700 mb-4 text-sm uppercase tracking-wide">إحصائياتك</h3>
                <div class="space-y-3">
                    <?php if ($user['user_type'] === 'professor'): ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <span class="text-sm text-slate-600 flex items-center gap-2">
                            <i data-lucide="book" style="width:15px;height:15px;color:#2563EB"></i> كورساتي
                        </span>
                        <span class="font-bold text-slate-800"><?php echo $coursesCount; ?></span>
                    </div>
                    <?php else: ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <span class="text-sm text-slate-600 flex items-center gap-2">
                            <i data-lucide="book-open" style="width:15px;height:15px;color:#2563EB"></i> كورساتي
                        </span>
                        <span class="font-bold text-slate-800"><?php echo $enrollmentsCount; ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <span class="text-sm text-slate-600 flex items-center gap-2">
                            <i data-lucide="bell" style="width:15px;height:15px;color:#F59E0B"></i> إشعارات جديدة
                        </span>
                        <span class="font-bold text-slate-800"><?php echo $notifCount; ?></span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <span class="text-sm text-slate-600 flex items-center gap-2">
                            <i data-lucide="calendar" style="width:15px;height:15px;color:#16a34a"></i> تاريخ الانضمام
                        </span>
                        <span class="font-bold text-slate-800 text-xs"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick links -->
            <div class="glass rounded-3xl p-6">
                <h3 class="font-bold text-slate-700 mb-4 text-sm uppercase tracking-wide">روابط سريعة</h3>
                <div class="space-y-2">
                    <?php $links = [
                        [$dashboardUrl, 'layout-dashboard', 'لوحة التحكم', '#2563EB'],
                        [$base.'/courses/list.php', 'book-open', 'الكورسات', '#16a34a'],
                        [$base.'/notifications/view.php', 'bell', 'الإشعارات', '#F59E0B'],
                        [$base.'/appointments/view.php', 'calendar', 'المواعيد', '#7c3aed'],
                        [$base.'/auth/logout.php', 'log-out', 'تسجيل الخروج', '#dc2626'],
                    ];
                    foreach ($links as [$url, $icon, $label, $color]): ?>
                    <a href="<?php echo $url; ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition text-sm font-medium text-slate-700 hover:text-blue-600 group">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center group-hover:scale-110 transition" style="background:<?php echo $color; ?>18">
                            <i data-lucide="<?php echo $icon; ?>" style="width:15px;height:15px;color:<?php echo $color; ?>"></i>
                        </span>
                        <?php echo $label; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
