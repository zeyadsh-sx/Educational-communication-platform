<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$courseId = $_GET['course_id'] ?? 0;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $course_id = $_POST['course_id'] ?? 0;
        $priority = $_POST['priority'] ?? 'medium';
        
        if (empty($title) || empty($content)) {
            $message = 'Please fill in all required fields';
            $messageType = 'error';
        } else {
        $database = new Database();
        $conn = $database->connect();
        
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, professor_id, course_id, priority, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $title,
            $content,
            getCurrentUserId(),
            $course_id,
            $priority,
            getCurrentUserId()
        ]);
        
            if ($result) {
                $message = 'Announcement created successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error creating announcement';
                $messageType = 'error';
            }
        }
    }
}

$pageTitle = 'إنشاء إعلان';
?>

<div class="container">
    <div class="create-announcement">
        <div class="create-header">
            <h1>إنشاء إعلان</h1>
            <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="back-link">
                <i class="fas fa-arrow-right"></i> العودة للكورس
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="announcement-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="title">العنوان <span class="required">*</span></label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="<?php echo htmlspecialchars($title ?? ''); ?>"
                       required
                       placeholder="أدخل عنوان الإعلان">
            </div>
            
            <div class="form-group">
                <label for="course_id">الكورس</label>
                <input type="number" 
                       id="course_id" 
                       name="course_id" 
                       value="<?php echo htmlspecialchars($courseId); ?>"
                       placeholder="معرف الكورس (اختياري)">
            </div>
            
            <div class="form-group">
                <label for="priority">الأولوية</label>
                <select id="priority" name="priority">
                    <option value="low">منخفضة</option>
                    <option value="medium" selected>متوسطة</option>
                    <option value="high">عالية</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="content">المحتوى <span class="required">*</span></label>
                <textarea id="content" 
                          name="content" 
                          rows="6"
                          required
                          placeholder="اكتب محتوى الإعلان..."><?php echo htmlspecialchars($content ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-bullhorn"></i> نشر الإعلان
                </button>
                <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.create-announcement {
    max-width: 700px;
    margin: 0 auto;
    padding: 20px;
}

.create-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.create-header h1 {
    color: #2c3e50;
    margin: 0;
}

.back-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.announcement-form {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #34495e;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.required {
    color: #e74c3c;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
