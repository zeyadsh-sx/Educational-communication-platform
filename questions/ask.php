<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
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
        $course_id = $_POST['course_id'] ?? 0;
        $question_text = trim($_POST['question_text'] ?? '');
        
        if (empty($question_text) || empty($course_id)) {
            $message = 'يرجى ملء جميع الحقول المطلوبة';
            $messageType = 'error';
        } else {
            $pdo = getDB();
            
            // Get professor_id from course
            $courseStmt = $pdo->prepare("SELECT professor_id FROM courses WHERE id = ?");
            $courseStmt->execute([$course_id]);
            $course = $courseStmt->fetch();
            
            if ($course) {
                $stmt = $pdo->prepare("INSERT INTO questions (question_text, student_id, professor_id, course_id, status) VALUES (?, ?, ?, ?, 'pending')");
                $result = $stmt->execute([
                    $question_text,
                    getCurrentUserId(),
                    $course['professor_id'],
                    $course_id
                ]);
                
                if ($result) {
                    // Award points to student
                    awardPoints(getCurrentUserId(), 'ask_question');
                    
                    $message = 'تم إرسال السؤال بنجاح! لقد حصلت على 10 نقاط.';
                    $messageType = 'success';
                } else {
                    $message = 'حدث خطأ أثناء إرسال السؤال';
                    $messageType = 'error';
                }
            } else {
                $message = 'الكورس غير موجود';
                $messageType = 'error';
            }
        }
    }
}

$pageTitle = 'طرح سؤال | EduFlow';
?>

<div class="container animate-fade">
    <div style="max-width: 800px; margin: 2rem auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; margin: 0;">طرح سؤال جديد</h1>
            <a href="/courses/view.php?id=<?php echo $courseId; ?>" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                <i class="fas fa-arrow-right"></i> العودة للكورس
            </a>
        </div>

        <?php if ($message): ?>
            <div class="card" style="background: <?php echo $messageType === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border: 1px solid <?php echo $messageType === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; color: <?php echo $messageType === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; margin-bottom: 2rem;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card glass">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label class="form-label">الكورس</label>
                    <select name="course_id" class="form-control" required>
                        <option value="">اختر الكورس...</option>
                        <?php 
                        $studentCourses = getStudentCourses(getCurrentUserId());
                        foreach ($studentCourses as $sc): ?>
                            <option value="<?php echo $sc['id']; ?>" <?php echo $courseId == $sc['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sc['course_name']); ?> (<?php echo htmlspecialchars($sc['course_code']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="question_text">سؤالك</label>
                    <textarea id="question_text" 
                              name="question_text" 
                              class="form-control" 
                              rows="6"
                              required
                              placeholder="ما الذي يدور في ذهنك؟"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-paper-plane"></i> إرسال السؤال
                    </button>
                    <a href="<?php echo getBaseUrl(); ?>/student/dashboard.php" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>

        <div style="margin-top: 3rem;" class="card glass">
            <h3 style="font-size: 1.1rem; margin-bottom: 1rem;"><i class="fas fa-info-circle" style="color: var(--primary); margin-left: 10px;"></i> ملاحظات هامة</h3>
            <ul style="list-style: none; padding: 0; font-size: 0.9rem; color: var(--text-muted);">
                <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="margin-left: 10px;"></i> تأكد من وضوح السؤال لضمان الحصول على إجابة دقيقة.</li>
                <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="margin-left: 10px;"></i> سيتم إشعارك فور قيام الدكتور بالرد على سؤالك.</li>
                <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="margin-left: 10px;"></i> ستحصل على 10 نقاط مقابل كل سؤال تطرحه!</li>
            </ul>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
