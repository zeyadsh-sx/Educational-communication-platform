<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
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
        $professor_id = $_POST['professor_id'] ?? 0;
        $appointment_date = $_POST['appointment_date'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        
        if (empty($professor_id) || empty($appointment_date)) {
            $message = 'Please fill in all required fields';
            $messageType = 'error';
        } else {
        $database = new Database();
        $conn = $database->connect();
        
        $stmt = $conn->prepare("INSERT INTO appointments (student_id, professor_id, appointment_date, date_time, notes, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $result = $stmt->execute([
            getCurrentUserId(),
            $professor_id,
            $appointment_date,
            $appointment_date,
            $notes
        ]);
        
            if ($result) {
                $message = 'Appointment booked successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error booking appointment';
                $messageType = 'error';
            }
        }
    }
}

$pageTitle = 'حجز موعد';
?>

<div class="container">
    <div class="book-appointment">
        <div class="book-header">
            <h1>حجز موعد</h1>
            <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="back-link">
                <i class="fas fa-arrow-right"></i> العودة للكورس
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="appointment-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="professor_id">الدكتور <span class="required">*</span></label>
                <input type="number" 
                       id="professor_id" 
                       name="professor_id" 
                       required
                       placeholder="معرف الدكتور">
            </div>
            
            <div class="form-group">
                <label for="appointment_date">التاريخ والوقت <span class="required">*</span></label>
                <input type="datetime-local" 
                       id="appointment_date" 
                       name="appointment_date" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="notes">ملاحظات</label>
                <textarea id="notes" 
                          name="notes" 
                          rows="4"
                          placeholder="أي ملاحظات إضافية..."><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i> حجز الموعد
                </button>
                <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.book-appointment {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.book-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.book-header h1 {
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

.appointment-form {
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
