<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/header.php';

// Check authorization
if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$pageTitle = 'إدارة الأساتذة | EduFlow';
$userId = getSafeUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    // Validate CSRF token
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $error = 'توكن الأمان غير صحيح';
    } else {
        $name = getSafePost('name', '', 'string');
        $email = getSafePost('email', '', 'email');
        $password = getSafePost('password', '', 'string');
        
        // Validate input
        if (empty($name) || strlen($name) < 3) {
            $error = 'الاسم يجب أن يكون على الأقل 3 أحرف';
        } elseif (empty($email)) {
            $error = 'البريد الإلكتروني مطلوب';
        } elseif (empty($password) || strlen($password) < 8) {
            $error = 'كلمة المرور يجب أن تكون على الأقل 8 أحرف';
        } else {
            try {
                $pdo = getDB();
                
                // Check if email exists
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $checkStmt->execute([$email]);
                
                if ($checkStmt->fetch()) {
                    $error = 'البريد الإلكتروني موجود بالفعل';
                } else {
                    // Generate unique username from email
                    $username = explode('@', $email)[0];
                    $checkUserStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $checkUserStmt->execute([$username]);
                    if ($checkUserStmt->fetch()) {
                        $username = $username . '_' . rand(10, 99);
                    }

                    $hashedPassword = hashPassword($password);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, full_name, email, password, user_type, created_at) 
                        VALUES (?, ?, ?, ?, 'professor', NOW())
                    ");
                    $stmt->execute([$username, $name, $email, $hashedPassword]);
                    $success = 'تم إضافة الدكتور بنجاح';
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
        $delete_error = 'توكن الأمان غير صحيح';
    } else {
        $deleteId = getSafePost('delete_id', null, 'int');
        
        if ($deleteId && $deleteId > 0) {
            try {
                $pdo = getDB();
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'professor'");
                $stmt->execute([$deleteId]);
                $delete_success = 'تم حذف الدكتور بنجاح';
            } catch (PDOException $e) {
                logError('Error deleting professor', ['error' => $e->getMessage()]);
                $delete_error = 'حدث خطأ في حذف الدكتور';
            }
        }
    }
}

// Get all professors
$professors = [];
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, full_name, email, created_at FROM users WHERE user_type = 'professor' ORDER BY created_at DESC");
    $stmt->execute();
    $professors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError('Error fetching professors', ['error' => $e->getMessage()]);
}
?>

<div class="container" style="margin: 2rem auto;">
    <div class="card">
        <div class="card-header">
            <h1>👨‍🏫 إدارة الأساتذة</h1>
        </div>
        
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 2rem;">
                <h3>➕ إضافة دكتور جديد</h3>
                <form method="POST" class="form-group" style="max-width: 500px;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="name" class="form-label required">الاسم الكامل</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-control" 
                            placeholder="أدخل اسم الدكتور" 
                            required
                            minlength="3"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label required">البريد الإلكتروني</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="example@example.com" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label required">كلمة المرور</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="كلمة مرور قوية" 
                            required
                            minlength="8"
                        >
                        <p class="form-help">يجب أن تكون كلمة المرور على الأقل 8 أحرف</p>
                    </div>
                    
                    <button type="submit" name="add" class="btn btn-primary w-full">
                        <i class="fas fa-plus"></i> إضافة دكتور
                    </button>
                </form>
            </div>
            
            <hr style="margin: 2rem 0; border: 1px solid var(--border-light);">
            
            <div>
                <h3>📋 قائمة الأساتذة</h3>
                
                <?php if (isset($delete_error)): ?>
                    <div class="alert alert-danger mt-4">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($delete_error); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($delete_success)): ?>
                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($delete_success); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($professors)): ?>
                    <p class="text-center text-secondary mt-4">لا يوجد أساتذة مسجلون بعد</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color); background: var(--bg-tertiary);">
                                <th style="padding: 1rem; text-align: right;">الاسم</th>
                                <th style="padding: 1rem; text-align: right;">البريد الإلكتروني</th>
                                <th style="padding: 1rem; text-align: right;">تاريخ التسجيل</th>
                                <th style="padding: 1rem; text-align: center;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($professors as $prof): ?>
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td style="padding: 1rem;">
                                        <strong><?php echo htmlspecialchars($prof['full_name']); ?></strong>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <a href="mailto:<?php echo htmlspecialchars($prof['email']); ?>">
                                            <?php echo htmlspecialchars($prof['email']); ?>
                                        </a>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <?php echo date('d/m/Y H:i', strtotime($prof['created_at'])); ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل تريد حقاً حذف هذا الدكتور؟');">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="delete_id" value="<?php echo $prof['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>