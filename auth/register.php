<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';
require_once '../includes/nagah/auth_shell.php';

$error = '';
$success = '';

if (isLoggedIn()) {
    redirect('/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'توكن الأمان غير صحيح. الرجاء تحديث الصفحة والمحاولة مجدداً';
    } else {
        $email = getSafePost('email', '', 'email');
        $password = getSafePost('password', '', 'string');
        $confirm_password = getSafePost('confirm_password', '', 'string');
        $full_name = getSafePost('full_name', '', 'string');
        $phone = getSafePost('phone', '', 'string');
        $parent_phone = getSafePost('parent_phone', '', 'string');
        $grade = getSafePost('grade', '', 'string');
        $education_system = getSafePost('education_system', '', 'string');

        if (empty($email) || empty($password) || empty($full_name) || empty($phone) || empty($parent_phone) || empty($grade) || empty($education_system)) {
            $error = 'الرجاء ملء جميع الحقول المطلوبة';
        } elseif ($password !== $confirm_password) {
            $error = 'كلمتا المرور غير متطابقتين';
        } elseif (strlen($password) < 6) {
            $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'الرجاء إدخال بريد إلكتروني صحيح';
        } else {
            try {
                $pdo = getDB();
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                $stmt->execute([$email]);

                if ($stmt->fetch()) {
                    $error = 'البريد الإلكتروني مسجل بالفعل';
                } else {
                    $username = generateUsernameFromEmail($pdo, $email);
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $gradeStored = $grade . ' | ' . $education_system;

                    $cols = ['username', 'email', 'password', 'full_name', 'user_type'];
                    $vals = [$username, $email, $hashed_password, $full_name, 'student'];
                    $placeholders = ['?', '?', '?', '?', '?'];

                    $checkCols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'users' AND TABLE_SCHEMA = DATABASE()")->fetchAll(PDO::FETCH_COLUMN);
                    if (in_array('phone', $checkCols)) {
                        $cols[] = 'phone'; $vals[] = $phone; $placeholders[] = '?';
                    }
                    if (in_array('parent_phone', $checkCols)) {
                        $cols[] = 'parent_phone'; $vals[] = $parent_phone; $placeholders[] = '?';
                    }
                    if (in_array('grade', $checkCols)) {
                        $cols[] = 'grade'; $vals[] = $gradeStored; $placeholders[] = '?';
                    }

                    $sql = 'INSERT INTO users (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
                    $pdo->prepare($sql)->execute($vals);

                    $success = 'شكراً لك! تم استلام تسجيلك بنجاح. يمكنك تسجيل الدخول الآن.';
                }
            } catch (PDOException $e) {
                $error = 'حدث خطأ أثناء الاتصال بقاعدة البيانات';
            }
        }
    }
}

$registerImage = 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=900&h=800&fit=crop';
nagahAuthHead('إنشاء حساب | أكاديمية ماستر');
?>

<section id="register" class="relative w-full py-16 sm:py-24 overflow-hidden auth-bg">
    <span class="blob" style="width:360px;height:360px;background:#60A5FA;top:-80px;left:-60px;opacity:.35;"></span>
    <span class="blob" style="width:300px;height:300px;background:#F59E0B;bottom:-60px;right:-40px;opacity:.25;"></span>
    <div class="relative z-10 max-w-6xl mx-auto px-5">
        <div class="glass rounded-[32px] overflow-hidden grid lg:grid-cols-2 items-stretch">
            <div class="relative hidden lg:block min-h-[520px]">
                <img src="<?php echo $registerImage; ?>" alt="طلاب يدرسون" loading="lazy" class="w-full h-full object-cover">
            </div>
            <div class="p-8 sm:p-10 lg:p-12">
                <span class="tag-pill inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">انضم إلينا</span>
                <h1 class="display font-semibold mt-4 text-2xl sm:text-3xl leading-tight text-slate-900">أنشئ حسابك في أكاديمية ماستر</h1>
                <p class="mt-3 text-slate-500">املأ البيانات وسيتواصل معك فريقنا قريباً</p>

                <?php if ($error): ?>
                    <div class="<?php echo nagahFeedbackClass('error'); ?> mt-6" role="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="<?php echo nagahFeedbackClass('success'); ?> mt-6" role="status">
                        <?php echo htmlspecialchars($success); ?>
                        <a href="login.php" class="block mt-2 font-bold underline">تسجيل الدخول</a>
                    </div>
                <?php endif; ?>

                <?php if (!$success): ?>
                <form method="POST" id="register-form" class="mt-7 grid sm:grid-cols-2 gap-4">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="sm:col-span-2">
                        <label for="full_name" class="block text-sm font-semibold mb-1.5">الاسم الكامل</label>
                        <input id="full_name" name="full_name" type="text" class="field-input" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold mb-1.5">البريد الإلكتروني</label>
                        <input id="email" name="email" type="email" class="field-input" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-semibold mb-1.5">رقم الهاتف</label>
                        <input id="phone" name="phone" type="tel" class="field-input" required placeholder="01xxxxxxxxx" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="parent_phone" class="block text-sm font-semibold mb-1.5">هاتف ولي الأمر</label>
                        <input id="parent_phone" name="parent_phone" type="tel" class="field-input" required placeholder="01xxxxxxxxx" value="<?php echo htmlspecialchars($_POST['parent_phone'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="grade" class="block text-sm font-semibold mb-1.5">الصف الدراسي</label>
                        <select id="grade" name="grade" class="field-input" required>
                            <option value="" disabled <?php echo empty($_POST['grade']) ? 'selected' : ''; ?>>— اختر —</option>
                            <option value="الصف الأول الثانوي" <?php echo ($_POST['grade'] ?? '') === 'الصف الأول الثانوي' ? 'selected' : ''; ?>>الصف الأول الثانوي</option>
                            <option value="الصف الثاني الثانوي" <?php echo ($_POST['grade'] ?? '') === 'الصف الثاني الثانوي' ? 'selected' : ''; ?>>الصف الثاني الثانوي</option>
                            <option value="الصف الثالث الثانوي" <?php echo ($_POST['grade'] ?? '') === 'الصف الثالث الثانوي' ? 'selected' : ''; ?>>الصف الثالث الثانوي</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="education_system" class="block text-sm font-semibold mb-1.5">النظام التعليمي</label>
                        <select id="education_system" name="education_system" class="field-input" required>
                            <option value="" disabled <?php echo empty($_POST['education_system']) ? 'selected' : ''; ?>>— اختر —</option>
                            <option value="الثانوية العامة" <?php echo ($_POST['education_system'] ?? '') === 'الثانوية العامة' ? 'selected' : ''; ?>>الثانوية العامة</option>
                            <option value="البكالوريا المصرية" <?php echo ($_POST['education_system'] ?? '') === 'البكالوريا المصرية' ? 'selected' : ''; ?>>البكالوريا المصرية</option>
                            <option value="دولي (IGCSE/American)" <?php echo ($_POST['education_system'] ?? '') === 'دولي (IGCSE/American)' ? 'selected' : ''; ?>>دولي (IGCSE/American)</option>
                        </select>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-semibold mb-1.5">كلمة المرور</label>
                        <input id="password" name="password" type="password" class="field-input" required minlength="6" placeholder="6 أحرف على الأقل">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold mb-1.5">تأكيد كلمة المرور</label>
                        <input id="confirm_password" name="confirm_password" type="password" class="field-input" required minlength="6">
                    </div>
                    <div class="sm:col-span-2 mt-1">
                        <button type="submit" id="register-submit" class="btn-primary-nagah w-full py-3.5 rounded-full font-bold shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                            <span id="submit-label">إرسال التسجيل</span>
                            <i data-lucide="loader-2" id="submit-spinner" class="spin hidden" style="width:18px;height:18px;"></i>
                        </button>
                    </div>
                </form>
                <div id="register-feedback" class="mt-4 hidden rounded-xl px-4 py-3 text-sm font-medium" role="status"></div>
                <?php endif; ?>

                <p class="text-center text-sm text-slate-500 mt-6">
                    لديك حساب؟ <a href="login.php" class="font-bold text-blue-600">تسجيل الدخول</a>
                </p>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('register-form')?.addEventListener('submit', function() {
    const btn = document.getElementById('register-submit');
    const spinner = document.getElementById('submit-spinner');
    if (btn) { btn.disabled = true; btn.style.opacity = '0.7'; }
    spinner?.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php nagahAuthFoot(); ?>
