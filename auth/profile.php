<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$userId = getCurrentUserId();
$pdo = getDB();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token.';
        $messageType = 'error';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        
        if (empty($full_name)) {
            $message = 'الاسم بالكامل مطلوب.';
            $messageType = 'error';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
                $stmt->execute([$full_name, $userId]);
                $_SESSION['full_name'] = $full_name;
                
                $message = 'تم تحديث الملف الشخصي بنجاح.';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'حدث خطأ أثناء التحديث.';
                $messageType = 'error';
            }
        }
    }
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$pageTitle = 'الملف الشخصي | EduFlow';
?>

<div class="container animate-fade">
    <div style="max-width: 600px; margin: 2rem auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; margin: 0;">الملف الشخصي</h1>
        </div>

        <?php if ($message): ?>
            <div class="card" style="background: <?php echo $messageType === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border: 1px solid <?php echo $messageType === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; color: <?php echo $messageType === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; margin-bottom: 2rem;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card glass">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold; margin: 0 auto 1rem;">
                    <?php echo mb_substr($user['full_name'], 0, 1); ?>
                </div>
                <div style="font-weight: 600; font-size: 1.25rem;"><?php echo htmlspecialchars($user['username']); ?></div>
                <div style="color: var(--text-muted); font-size: 0.9rem;"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label" for="full_name">الاسم بالكامل</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">البريد الإلكتروني (غير قابل للتعديل)</label>
                    <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="opacity: 0.7; cursor: not-allowed;">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
