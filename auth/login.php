<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';
require_once '../includes/nagah/auth_shell.php';

$error = '';
$success = '';

if (isLoggedIn()) {
    redirect(isProfessor() ? '/admin/dashboard.php' : '/student/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $error = 'توكن الأمان غير صحيح. الرجاء تحديث الصفحة والمحاولة مجدداً';
    } else {
        $email = getSafePost('email', '', 'email');
        $password = getSafePost('password', '', 'string');

        if (empty($email) || empty($password)) {
            $error = 'الرجاء إدخال البريد الإلكتروني وكلمة المرور';
        } else {
            try {
                $pdo = getDB();
                $stmt = $pdo->prepare('SELECT id, username, full_name, user_type, password FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && verifyPassword($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    redirect($user['user_type'] === 'professor' ? '/admin/dashboard.php' : '/student/dashboard.php');
                    exit;
                }
                $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
            } catch (PDOException $e) {
                $error = 'حدث خطأ عند الاتصال بقاعدة البيانات';
            }
        }
    }
}

nagahAuthHead('تسجيل الدخول | أكاديمية ماستر');
?>

<section class="auth-bg flex items-center justify-center p-4 py-16">
    <span class="blob" style="width:420px;height:420px;background:#60A5FA;top:-80px;right:-100px;"></span>
    <span class="blob" style="width:360px;height:360px;background:#2563EB;bottom:-100px;left:-80px;opacity:.4;"></span>
    <span class="blob" style="width:260px;height:260px;background:#F59E0B;top:30%;left:20%;opacity:.28;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>

    <div class="glass max-w-md w-full rounded-[24px] p-6 sm:p-8 relative z-10 reveal" style="animation-delay:.1s">
        <div class="text-center">
            <span class="w-12 h-12 rounded-2xl flex items-center justify-center text-white mx-auto mb-4" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="log-in" style="width:24px;height:24px;"></i>
            </span>
            <h1 class="display font-semibold text-2xl text-slate-900">مرحباً بعودتك</h1>
            <p class="text-sm text-slate-500 mt-2">سجّل دخولك لمتابعة دروسك وكورساتك</p>
        </div>

        <?php if ($error): ?>
            <div class="<?php echo nagahFeedbackClass('error'); ?> mt-6" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="<?php echo nagahFeedbackClass('success'); ?> mt-6" role="status"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-6 space-y-4" id="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div>
                <label for="email" class="block text-sm font-semibold mb-1.5 text-slate-800">البريد الإلكتروني</label>
                <input id="email" name="email" type="email" class="field-input" required autocomplete="email" placeholder="example@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold mb-1.5 text-slate-800">كلمة المرور</label>
                <input id="password" name="password" type="password" class="field-input" required autocomplete="current-password" placeholder="••••••••">
            </div>
            <div class="text-left">
                <a href="forgot_password.php" class="text-sm font-medium text-blue-600 hover:text-blue-700">نسيت كلمة المرور؟</a>
            </div>
            <button type="submit" id="login-submit" class="btn-primary-nagah w-full py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all mt-2 flex items-center justify-center gap-2">
                <span id="login-label">تسجيل الدخول</span>
                <i data-lucide="loader-2" id="login-spinner" class="spin hidden" style="width:18px;height:18px;"></i>
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            ليس لديك حساب؟
            <a href="register.php" class="font-bold text-blue-600 hover:text-blue-700">أنشئ حساباً الآن</a>
        </p>
    </div>
</section>

<script>
document.getElementById('login-form')?.addEventListener('submit', function() {
    const btn = document.getElementById('login-submit');
    const spinner = document.getElementById('login-spinner');
    btn.disabled = true;
    btn.style.opacity = '0.7';
    spinner?.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>

<?php nagahAuthFoot(); ?>
