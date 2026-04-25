<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

$courseId = $_GET['course_id'] ?? 0;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? 0;
    $question_text = trim($_POST['question_text'] ?? '');
    
    if (empty($question_text) || empty($course_id)) {
        $message = 'Please fill in all required fields';
        $messageType = 'error';
    } else {
        $database = new Database();
        $conn = $database->connect();
        
        $stmt = $conn->prepare("INSERT INTO questions (question_text, student_id, professor_id, course_id, status) VALUES (?, ?, ?, ?, 'pending')");
        
        // Get professor_id from course
        $courseStmt = $conn->prepare("SELECT professor_id FROM courses WHERE id = ?");
        $courseStmt->execute([$course_id]);
        $course = $courseStmt->fetch();
        
        if ($course) {
            $result = $stmt->execute([
                $question_text,
                getCurrentUserId(),
                $course['professor_id'],
                $course_id
            ]);
            
            if ($result) {
                $message = 'Question sent successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error sending question';
                $messageType = 'error';
            }
        } else {
            $message = 'Course not found';
            $messageType = 'error';
        }
    }
}

$pageTitle = 'طرح سؤال';
?>

<div class="container">
    <div class="ask-question">
        <div class="ask-header">
            <h1>طرح سؤال</h1>
            <a href="/courses/view.php?id=<?php echo $courseId; ?>" class="back-link">
                <i class="fas fa-arrow-right"></i> العودة للكورس
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="question-form">
            <div class="form-group">
                <label for="course_id">الكورس <span class="required">*</span></label>
                <input type="number" 
                       id="course_id" 
                       name="course_id" 
                       value="<?php echo htmlspecialchars($courseId); ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="question_text">السؤال <span class="required">*</span></label>
                <textarea id="question_text" 
                          name="question_text" 
                          rows="6"
                          required
                          placeholder="اكتب سؤالك هنا..."><?php echo htmlspecialchars($question_text ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> إرسال السؤال
                </button>
                <a href="/courses/view.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.ask-question {
    max-width: 700px;
    margin: 0 auto;
    padding: 20px;
}

.ask-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.ask-header h1 {
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

.question-form {
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
.form-group textarea {
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