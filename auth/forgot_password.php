<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';
require_once '../includes/nagah/auth_shell.php';

$error   = '';
$success = '';

if (isLoggedIn()) {
    redirect('/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $error = 'توكن الأمان غير صحيح. الرجاء تحديث الصفحة والمحاولة مجدداً';
    } else {
        $email = getSafePost('email', '', 'email');
        if (empty($email)) {
            $error = 'الرجاء إدخال البريد الإلكتروني';
        } else {
            // Check if email exists (don't reveal whether it does or not for security)
            try {
                $pdo  = getDB();
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                // Always show success message regardless — prevents email enumeration
            } catch (Exception $e) {}

            $success = 'إذا كان البريد الإلكتروني مسجلاً لدينا، ستتلقى رابط إعادة تعيين كلمة المرور قريباً.';
        }
    }
}

nagahAuthHead('نسيت كلمة المرور | أكاديمية ماستر');
?>

<section class="auth-bg flex items-center justify-center p-4 py-16">
    <span class="blob" style="width:380px;height:380px;background:#60A5FA;top:-80px;right:-100px;"></span>
    <span class="blob" style="width:320px;height:320px;background:#2563EB;bottom:-100px;left:-80px;opacity:.4;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>

    <div class="glass max-w-md w-full rounded-[24px] p-6 sm:p-8 relative z-10 reveal" style="animation-delay:.1s">
        <div class="text-center">
            <span class="w-12 h-12 rounded-2xl flex items-center justify-center text-white mx-auto mb-4"
                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="key-round" style="width:24px;height:24px;"></i>
            </span>
            <h1 class="display font-semibold text-2xl text-slate-900">نسيت كلمة المرور؟</h1>
            <p class="text-sm text-slate-500 mt-2">أدخل بريدك الإلكتروني وسنرسل لك رابط الاستعادة</p>
        </div>

        <?php if ($error): ?>
        <div class="<?php echo nagahFeedbackClass('error'); ?> mt-6" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="<?php echo nagahFeedbackClass('success'); ?> mt-6" role="status">
            <div class="flex items-start gap-2">
                <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;margin-top:1px;"></i>
                <div>
                    <?php echo htmlspecialchars($success); ?>
                    <a href="login.php" class="block mt-2 font-bold underline">العودة لتسجيل الدخول</a>
                </div>
            </div>
        </div>
        <?php else: ?>

        <form method="POST" class="mt-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div>
                <label for="email" class="block text-sm font-semibold mb-1.5 text-slate-800">البريد الإلكتروني</label>
                <input id="email" name="email" type="email" class="field-input" required
                       autocomplete="email" placeholder="example@email.com"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <button type="submit"
                    class="btn-primary-nagah w-full py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all mt-2 flex items-center justify-center gap-2">
                <i data-lucide="send" style="width:17px;height:17px;"></i>
                إرسال رابط الاستعادة
            </button>
        </form>

        <?php endif; ?>

        <p class="text-center text-sm text-slate-500 mt-6">
            <a href="login.php" class="font-bold text-blue-600 hover:text-blue-700 flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                العودة لتسجيل الدخول
            </a>
        </p>
    </div>
</section>

<?php nagahAuthFoot(); ?>
