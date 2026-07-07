<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('/auth/login.php');
    exit;
}

$courseId = getSafeGet('course_id', 0, 'int');
$message = '';
$messageType = '';
$pdo = getDB();
$userId = getSafeUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $message = 'توكن الأمان غير صحيح';
        $messageType = 'error';
    } else {
        $professorId = getSafePost('professor_id', 0, 'int');
        $appointmentDate = getSafePost('appointment_date', '', 'string');
        $notes = getSafePost('notes', '', 'string');
        
        // Validate inputs
        if (!$professorId || $professorId <= 0) {
            $message = 'اختر دكتور صحيح';
            $messageType = 'error';
        } elseif (empty($appointmentDate)) {
            $message = 'التاريخ والوقت مطلوب';
            $messageType = 'error';
        } else {
            // Validate datetime format (ISO 8601: Y-m-d H:i or datetime-local format)
            try {
                $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $appointmentDate);
                if (!$dateTime) {
                    $dateTime = DateTime::createFromFormat('Y-m-d H:i', $appointmentDate);
                }
                
                if (!$dateTime) {
                    throw new Exception('صيغة التاريخ غير صحيحة');
                }
                
                // Check if date is in the future
                $now = new DateTime();
                if ($dateTime <= $now) {
                    throw new Exception('يجب أن يكون الموعد في المستقبل');
                }
                
                // Convert to standard format
                $appointmentDateTime = $dateTime->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $message = $e->getMessage();
                $messageType = 'error';
            }
            
            if ($messageType !== 'error') {
                try {
                    // Verify professor exists
                    $profCheckStmt = $pdo->prepare("
                        SELECT id FROM users 
                        WHERE id = ? AND user_type = 'professor'
                    ");
                    $profCheckStmt->execute([$professorId]);
                    
                    if (!$profCheckStmt->fetch()) {
                        $message = 'الدكتور غير موجود';
                        $messageType = 'error';
                    } else {
                        // Insert appointment
                        $stmt = $pdo->prepare("
                            INSERT INTO appointments (student_id, professor_id, appointment_date, date_time, notes, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
                        ");
                        
                        $stmt->execute([
                            $userId,
                            $professorId,
                            $appointmentDate,
                            $appointmentDateTime,
                            $notes
                        ]);
                        
                        $message = 'تم حجز الموعد بنجاح! حصلت على 15 نقطة';
                        $messageType = 'success';
                        
                        // Award points
                        awardPoints($userId, 'book_appointment');
                        
                        // Send notification
                        $studentStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
                        $studentStmt->execute([$userId]);
                        $studentName = $studentStmt->fetchColumn();
                        
                        if (file_exists(__DIR__ . '/../includes/notification_functions.php')) {
                            require_once __DIR__ . '/../includes/notification_functions.php';
                            sendNotification($professorId, "طالب جديد ({$studentName}) حجز موعد معك في {$appointmentDateTime}");
                        }
                    }
                } catch (PDOException $e) {
                    logError('Appointment booking error', ['error' => $e->getMessage()]);
                    $message = 'حدث خطأ عند حجز الموعد';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Get professors
$professors = [];
try {
    $profStmt = $pdo->prepare("
        SELECT id, full_name, email 
        FROM users 
        WHERE user_type = 'professor' 
        ORDER BY full_name
    ");
    $profStmt->execute();
    $professors = $profStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError('Error fetching professors', ['error' => $e->getMessage()]);
}

$pageTitle = 'حجز موعد | EduFlow';
?>

<div class="container" style="margin: 2rem auto;">
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <h1>📅 حجز موعد مع الدكتور</h1>
        </div>
        
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="form-group">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="professor_id" class="form-label required">اختر الدكتور</label>
                    <select id="professor_id" name="professor_id" class="form-control" required>
                        <option value="">-- اختر دكتور --</option>
                        <?php foreach ($professors as $prof): ?>
                            <option value="<?php echo $prof['id']; ?>" 
                                <?php echo (getSafePost('professor_id', 0, 'int') == $prof['id']) ? 'selected' : ''; ?>>
                                👨‍🏫 <?php echo htmlspecialchars($prof['full_name']); ?> 
                                (<?php echo htmlspecialchars($prof['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="appointment_date" class="form-label required">التاريخ والوقت</label>
                    <input type="datetime-local"
                        id="appointment_date"
                        name="appointment_date"
                        class="form-control"
                        required>
                    <p class="form-help">اختر موعداً في المستقبل</p>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">ملاحظات (اختياري)</label>
                    <textarea id="notes"
                        name="notes"
                        class="form-control"
                        rows="4"
                        placeholder="أي ملاحظات أو موضوعات تريد مناقشتها..."></textarea>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> حجز الموعد
                    </button>
                    <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>