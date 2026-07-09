<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

$lang = $_SESSION['lang'] ?? 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $error = 'توكن الأمان غير صحيح';
    } else {
        $email = getSafePost('email', '', 'email');
        if (empty($email)) {
            $error = 'الرجاء إدخال البريد الإلكتروني';
        } else {
            $success = 'إذا كان البريد مسجلاً لدينا، ستتلقى رابط إعادة تعيين كلمة المرور.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نسيت كلمة المرور | أكاديمية ماستر</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-card glass">
        <div class="auth-header">
            <div class="brand-icon"><i class="fas fa-key"></i></div>
            <h1 style="font-size: 1.35rem;">نسيت كلمة المرور؟</h1>
            <p style="color: var(--text-secondary);">أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين</p>
        </div>

        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label class="form-label"><?php echo __('email_label'); ?></label>
                <input type="email" name="email" class="form-control" required placeholder="<?php echo __('email_placeholder'); ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-block">إرسال رابط الاستعادة</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem;">
            <a href="login.php"><i class="fas fa-arrow-right"></i> العودة لتسجيل الدخول</a>
        </p>
    </div>
</body>
</html>
