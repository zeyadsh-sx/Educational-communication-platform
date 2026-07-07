<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn() || !isProfessor()) {
  redirect('/auth/login.php');
  exit;
}

$userId = getCurrentUserId();
$professorCourses = getProfessorCourses($userId);
$pendingQuestions = getPendingQuestionsCount($userId, 'professor');
$upcomingAppointments = getUpcomingAppointmentsCount($userId, 'professor');
$analytics = getProfessorAnalytics($userId);
$professorPoints = 0;

// Fetch professor points
$pdo = getDB();
$stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
$stmt->execute([$userId]);
$professorPoints = $stmt->fetchColumn();

$pageTitle = 'لوحة تحكم الدكتور | EduFlow';
?>

<div class="container animate-fade">
  <div style="margin-bottom: 3rem; text-align: center;">
    <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo getEmoji('welcome'); ?> مرحباً د. <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
    <p style="color: var(--text-muted); font-size: 1.1rem;">إدارة كورساتك وتفاعل مع طلابك في مكان واحد.</p>
  </div>

  <!-- Stats Grid -->
  <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
    <div class="card glass stat-card">
      <div class="stat-icon" style="color: var(--primary); background: rgba(99, 102, 241, 0.1);"><i class="fas fa-book"></i></div>
      <div class="stat-value"><?php echo count($professorCourses); ?></div>
      <div class="stat-label"><?php echo getEmoji('courses'); ?> كورساتي</div>
    </div>
    <div class="card glass stat-card">
      <div class="stat-icon" style="color: var(--warning); background: rgba(245, 158, 11, 0.1);"><i class="fas fa-question-circle"></i></div>
      <div class="stat-value"><?php echo $pendingQuestions; ?></div>
      <div class="stat-label"><?php echo getEmoji('questions'); ?> أسئلة معلقة</div>
    </div>
    <div class="card glass stat-card">
      <div class="stat-icon" style="color: var(--success); background: rgba(16, 185, 129, 0.1);"><i class="fas fa-calendar-check"></i></div>
      <div class="stat-value"><?php echo $upcomingAppointments; ?></div>
      <div class="stat-label"><?php echo getEmoji('appointments'); ?> المواعيد القادمة</div>
    </div>
    <div class="card glass stat-card">
      <div class="stat-icon" style="color: var(--info); background: rgba(59, 130, 246, 0.1);"><i class="fas fa-star"></i></div>
      <div class="stat-value"><?php echo $professorPoints; ?></div>
      <div class="stat-label"><?php echo getEmoji('points'); ?> إجمالي النقاط</div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">

    <!-- Courses Management -->
    <div class="card glass">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; margin: 0;"><i class="fas fa-chalkboard-teacher" style="margin-left: 10px; color: var(--primary);"></i> كورساتي</h2>
        <a href="<?php echo getBaseUrl(); ?>/courses/create.php" class="btn btn-primary btn-sm">إنشاء كورس جديد</a>
      </div>

      <?php if (empty($professorCourses)): ?>
        <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
          <i class="fas fa-plus-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
          <p>لم تنشئ أي كورس بعد.</p>
          <a href="<?php echo getBaseUrl(); ?>/courses/create.php" class="btn btn-primary">إنشاء كورس الأول</a>
        </div>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
          <?php foreach ($professorCourses as $course): ?>
            <div style="padding: 1rem; border-radius: var(--radius-md); background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div>
                  <h4 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($course['course_name']); ?></h4>
                  <span class="badge badge-primary"><?php echo htmlspecialchars($course['course_code']); ?></span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                  <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $course['id']; ?>" class="btn btn-outline btn-sm">عرض</a>
                  <a href="<?php echo $basePath; ?>/courses/manage.php?id=<?php echo $course['id']; ?>" class="btn btn-outline btn-sm">إدارة</a>
                </div>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--text-muted);">
                <span><i class="fas fa-users"></i> <?php echo $course['student_count'] ?? 0; ?> طالب</span>
                <span><i class="fas fa-file"></i> <?php echo $course['material_count'] ?? 0; ?> ملف</span>
                <span><i class="fas fa-question"></i> <?php echo $course['question_count'] ?? 0; ?> سؤال</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <div class="card glass">
      <h2 style="font-size: 1.5rem; margin-bottom: 2rem;"><i class="fas fa-clock" style="margin-left: 10px; color: var(--accent);"></i> الأنشطة الأخيرة</h2>

      <div style="display: flex; flex-direction: column; gap: 1rem;">
        <?php
        $recentQuestions = getRecentQuestions($userId, 'professor', 5);
        if (!empty($recentQuestions)): ?>
          <?php foreach ($recentQuestions as $question): ?>
            <div style="padding: 1rem; border-radius: var(--radius-md); background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                <div>
                  <strong><?php echo htmlspecialchars($question['student_name'] ?? 'طالب'); ?></strong>
                  <span style="color: var(--text-muted); font-size: 0.9rem;"> في <?php echo htmlspecialchars($question['course_name']); ?></span>
                </div>
                <span class="badge <?php echo $question['status'] === 'pending' ? 'badge-warning' : 'badge-success'; ?>">
                  <?php echo $question['status'] === 'pending' ? 'معلق' : 'مجاب'; ?>
                </span>
              </div>
              <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted);">
                <?php echo htmlspecialchars(substr($question['question_text'], 0, 100)); ?><?php echo strlen($question['question_text']) > 100 ? '...' : ''; ?>
              </p>
              <div style="margin-top: 0.5rem;">
                <a href="<?php echo $basePath; ?>/questions/answer.php?id=<?php echo $question['id']; ?>" class="btn btn-primary btn-xs">
                  <?php echo $question['status'] === 'pending' ? 'الإجابة' : 'عرض'; ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="text-align: center; padding: 2rem 0; color: var(--text-muted);">
            <p>لا توجد أسئلة حديثة.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Appointments Management -->
  <div class="card glass" style="margin-top: 2rem;">
    <h2 style="font-size: 1.5rem; margin-bottom: 2rem;"><i class="fas fa-calendar-alt" style="margin-left: 10px; color: var(--warning);"></i> إدارة المواعيد</h2>

    <?php
    $recentAppointments = getUpcomingAppointmentsList($userId, 'professor', 3);
    if (empty($recentAppointments)): ?>
      <div style="text-align: center; padding: 2rem 0; color: var(--text-muted);">
        <i class="fas fa-calendar-times" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.3;"></i>
        <p>لا توجد مواعيد محجوزة حالياً.</p>
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
        <?php foreach ($recentAppointments as $appointment): ?>
          <div style="padding: 1rem; border-radius: var(--radius-md); background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
              <div>
                <strong><?php echo htmlspecialchars($appointment['student_name'] ?? 'طالب'); ?></strong>
              </div>
              <span class="badge <?php echo $appointment['status'] === 'confirmed' ? 'badge-success' : ($appointment['status'] === 'pending' ? 'badge-warning' : 'badge-danger'); ?>">
                <?php
                $statusText = [
                  'pending' => 'معلق',
                  'confirmed' => 'مؤكد',
                  'cancelled' => 'ملغي',
                  'completed' => 'مكتمل'
                ];
                echo $statusText[$appointment['status']] ?? $appointment['status'];
                ?>
              </span>
            </div>
            <div style="font-size: 0.9rem; color: var(--text-muted);">
              <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($appointment['date_time'])); ?>
              <i class="fas fa-clock" style="margin-right: 10px;"></i> <?php echo date('H:i', strtotime($appointment['date_time'])); ?>
            </div>
            <?php if (!empty($appointment['notes'])): ?>
              <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">
                <strong>ملاحظات:</strong> <?php echo htmlspecialchars(substr($appointment['notes'], 0, 50)); ?><?php echo strlen($appointment['notes']) > 50 ? '...' : ''; ?>
              </div>
            <?php endif; ?>
            <?php if ($appointment['status'] === 'pending'): ?>
              <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                <button class="btn btn-success btn-xs" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'confirmed')">تأكيد</button>
                <button class="btn btn-danger btn-xs" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'cancelled')">إلغاء</button>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top: 1.5rem; text-align: center;">
        <a href="<?php echo getBaseUrl(); ?>/appointments/view.php" class="btn btn-outline">عرض جميع المواعيد</a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Quick Actions -->
  <div class="card glass" style="margin-top: 2rem;">
    <h2 style="font-size: 1.5rem; margin-bottom: 2rem;"><i class="fas fa-bolt" style="margin-left: 10px; color: var(--success);"></i> إجراءات سريعة</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
      <a href="<?php echo getBaseUrl(); ?>/courses/create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> إنشاء كورس جديد
      </a>
      <a href="<?php echo getBaseUrl(); ?>/materials/upload.php" class="btn btn-secondary">
        <i class="fas fa-upload"></i> رفع ملف جديد
      </a>
      <a href="<?php echo getBaseUrl(); ?>/announcements/create.php" class="btn btn-accent">
        <i class="fas fa-bullhorn"></i> إنشاء إعلان
      </a>
      <a href="<?php echo getBaseUrl(); ?>/appointments/view.php" class="btn btn-info">
        <i class="fas fa-calendar"></i> إدارة المواعيد
      </a>
    </div>
  </div>
</div>

<style>
  .stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
  }

  .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }

  .stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--text);
  }

  .stat-label {
    font-size: 0.9rem;
    color: var(--text-muted);
  }

  .btn-xs {
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
  }
</style>

<script>
  function updateAppointmentStatus(appointmentId, status) {
    if (confirm('هل أنت متأكد من هذا الإجراء؟')) {
      fetch('<?php echo $basePath; ?>/api/appointments/update.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            appointment_id: appointmentId,
            status: status
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('حدث خطأ: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('حدث خطأ في الاتصال');
        });
    }
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>