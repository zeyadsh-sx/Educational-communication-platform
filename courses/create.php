<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$message     = '';
$messageKind = '';
$courseName  = '';
$courseCode  = '';
$description = '';
$base        = nagahBaseUrl();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'توكن الأمان غير صحيح. الرجاء تحديث الصفحة.';
        $messageKind = 'error';
    } else {
        $courseName  = trim($_POST['course_name'] ?? '');
        $courseCode  = trim($_POST['course_code'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($courseName) || empty($courseCode)) {
            $message = 'الرجاء ملء جميع الحقول المطلوبة';
            $messageKind = 'error';
        } elseif (courseCodeExists($courseCode)) {
            $message = 'كود الكورس مستخدم بالفعل، جرّب كوداً آخر';
            $messageKind = 'error';
        } else {
            $result = createCourse($courseName, $courseCode, $_SESSION['user_id'], $description);
            if ($result['success']) {
                $message = 'تم إنشاء الكورس بنجاح!';
                $messageKind = 'success';
                $courseName = $courseCode = $description = '';
            } else {
                $message = $result['message'];
                $messageKind = 'error';
            }
        }
    }
}

$pageTitle = 'إنشاء كورس جديد | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<section class="relative min-h-screen flex items-center justify-center py-16 overflow-hidden auth-bg">
    <span class="blob" style="width:380px;height:380px;background:#60A5FA;top:-100px;right:-80px;"></span>
    <span class="blob" style="width:320px;height:320px;background:#2563EB;bottom:-80px;left:-60px;opacity:.4;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>

    <div class="relative z-10 w-full max-w-xl mx-auto px-5">
        <a href="<?php echo $base; ?>/professor/dashboard.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-600 text-sm font-medium mb-6 transition">
            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i> العودة للوحة التحكم
        </a>

        <div class="glass rounded-[28px] p-8 reveal">
            <div class="text-center mb-7">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-lg" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <i data-lucide="book-plus" style="width:22px;height:22px;"></i>
                </span>
                <h1 class="display font-semibold text-2xl text-slate-900">إنشاء كورس جديد</h1>
                <p class="text-sm text-slate-500 mt-1.5">أضف كورسك وابدأ في تدريس طلابك</p>
            </div>

            <?php if ($message): ?>
                <div class="rounded-xl px-4 py-3 text-sm font-medium mb-6 <?php echo $messageKind === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                    <?php if ($messageKind === 'success'): ?>
                        <a href="list.php" class="block mt-2 font-bold underline">عرض كورساتي ←</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5" id="create-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">اسم الكورس <span class="text-red-500">*</span></label>
                    <input type="text" name="course_name" class="field-input" required placeholder="مثال: رياضيات الثانوية العامة" value="<?php echo htmlspecialchars($courseName); ?>">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">كود الكورس <span class="text-red-500">*</span></label>
                    <input type="text" name="course_code" class="field-input" required placeholder="مثال: MATH101" value="<?php echo htmlspecialchars($courseCode); ?>">
                    <p class="text-xs text-slate-400 mt-1">كود فريد لتمييز الكورس</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">وصف الكورس</label>
                    <textarea name="description" rows="4" class="field-input resize-none" placeholder="اكتب وصفاً مختصراً عن محتوى الكورس…"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" id="create-btn" class="btn-primary-nagah flex-1 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="plus-circle" style="width:17px;height:17px;"></i>
                        <span id="btn-label">إنشاء الكورس</span>
                        <i data-lucide="loader-2" id="btn-spin" class="spin hidden" style="width:17px;height:17px;"></i>
                    </button>
                    <a href="list.php" class="px-6 py-3 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition text-center">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.getElementById('create-form')?.addEventListener('submit', function() {
    const btn = document.getElementById('create-btn');
    const spin = document.getElementById('btn-spin');
    btn.disabled = true; btn.style.opacity = '.7';
    spin?.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
