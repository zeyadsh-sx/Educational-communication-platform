<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/notification_functions.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
  redirect('/auth/login.php');
  exit;
}

if (!isProfessor()) {
  redirect('/student/dashboard.php');
  exit;
}

$questionId = $_GET['id'] ?? 0;
$message = '';
$messageType = '';

if (!$questionId) {
  redirect('/professor/dashboard.php');
  exit;
}

$pdo = getDB();
$questionStmt = $pdo->prepare("
    SELECT q.*, c.course_name, u.full_name as student_name, u.email as student_email
    FROM questions q
    JOIN courses c ON q.course_id = c.id
    LEFT JOIN users u ON q.student_id = u.id
    WHERE q.id = ? AND q.professor_id = ?
");
$questionStmt->execute([$questionId, getCurrentUserId()]);
$question = $questionStmt->fetch();

if (!$question) {
  redirect('/professor/dashboard.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $message = 'Invalid security token. Please try again.';
    $messageType = 'error';
  } else {
    $answer_text = trim($_POST['answer_text'] ?? '');

    if (empty($answer_text)) {
      $message = 'يرجى كتابة الإجابة';
      $messageType = 'error';
    } else {
      $updateStmt = $pdo->prepare("UPDATE questions SET answer_text = ?, status = 'answered', answered_at = NOW() WHERE id = ?");
      $result = $updateStmt->execute([$answer_text, $questionId]);

      if ($result) {
        // Send notification to student
        sendNotification($question['student_id'], "تم الرد على سؤالك في كورس {$question['course_name']}");

        // Award points to professor
        awardPoints(getCurrentUserId(), 'answer_question');

        $message = 'تم إرسال الإجابة بنجاح! لقد حصلت على 20 نقطة.';
        $messageType = 'success';

        $questionStmt->execute([$questionId, getCurrentUserId()]);
        $question = $questionStmt->fetch();
      } else {
        $message = 'حدث خطأ أثناء إرسال الإجابة';
        $messageType = 'error';
      }
    }
  }
}

$pageTitle = 'الإجابة على سؤال | EduFlow';
?>

<div class="container animate-fade">
  <div style="max-width: 800px; margin: 2rem auto;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <h1 style="font-size: 2rem; margin: 0;">الإجابة على سؤال</h1>
      <a href="<?php echo $basePath; ?>/professor/dashboard.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">
        <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
      </a>
    </div>

    <?php if ($message): ?>
      <div class="card" style="background: <?php echo $messageType === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border: 1px solid <?php echo $messageType === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; color: <?php echo $messageType === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; margin-bottom: 2rem;">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <div class="card glass">
      <div style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(59, 130, 246, 0.1); border-radius: 8px;">
        <h3 style="margin: 0 0 1rem 0; color: var(--primary);">السؤال</h3>
        <div style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 1rem;">
          <?php echo nl2br(htmlspecialchars($question['question_text'])); ?>
        </div>
        <div style="font-size: 0.9rem; color: var(--text-muted);">
          <strong>الكورس:</strong> <?php echo htmlspecialchars($question['course_name']); ?><br>
          <strong>الطالب:</strong> <?php echo htmlspecialchars($question['student_name'] ?? 'غير محدد'); ?><br>
          <strong>تاريخ السؤال:</strong> <?php echo formatDate($question['created_at']); ?>
        </div>
      </div>

      <?php if ($question['status'] === 'pending'): ?>
        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

          <div class="form-group">
            <label class="form-label" for="answer_text">إجابتك</label>
            <textarea id="answer_text"
              name="answer_text"
              class="form-control"
              rows="8"
              required
              placeholder="اكتب إجابتك هنا..."><?php echo htmlspecialchars($question['answer_text'] ?? ''); ?></textarea>
          </div>

          <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
              <i class="fas fa-reply"></i> إرسال الإجابة
            </button>
            <a href="<?php echo $basePath; ?>/professor/dashboard.php" class="btn btn-secondary">إلغاء</a>
          </div>
        </form>
      <?php else: ?>
        <div style="padding: 1.5rem; background: rgba(16, 185, 129, 0.1); border-radius: 8px;">
          <h3 style="margin: 0 0 1rem 0; color: var(--success);">الإجابة</h3>
          <div style="font-size: 1.1rem; line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($question['answer'])); ?>
          </div>
          <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 1rem;">
            <strong>تاريخ الإجابة:</strong> <?php echo formatDate($question['answered_at']); ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>