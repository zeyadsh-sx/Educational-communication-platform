<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn()) {
  redirect('/auth/login.php');
  exit;
}

$userId = getCurrentUserId();
$userType = getCurrentUserType();

$pdo = getDB();
if ($userType === 'professor') {
  $stmt = $pdo->prepare("
        SELECT a.*, u.full_name as student_name, u.email as student_email
        FROM appointments a
        LEFT JOIN users u ON a.student_id = u.id
        WHERE a.professor_id = ?
        ORDER BY a.appointment_date DESC, a.created_at DESC
    ");
} else {
  $stmt = $pdo->prepare("
        SELECT a.*, u.full_name as professor_name, u.email as professor_email
        FROM appointments a
        LEFT JOIN users u ON a.professor_id = u.id
        WHERE a.student_id = ?
        ORDER BY a.appointment_date DESC, a.created_at DESC
    ");
}
$stmt->execute([$userId]);
$appointments = $stmt->fetchAll();

$pageTitle = 'مواعيدي | EduFlow';
?>

<div class="container animate-fade">
  <div style="max-width: 1000px; margin: 2rem auto;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <h1 style="font-size: 2rem; margin: 0;">مواعيدي</h1>
      <?php if ($userType === 'student'): ?>
        <a href="<?php echo $basePath; ?>/appointments/book.php" class="btn btn-primary">
          <i class="fas fa-calendar-plus"></i> حجز موعد جديد
        </a>
      <?php endif; ?>
    </div>

    <?php if (empty($appointments)): ?>
      <div class="card glass" style="text-align: center; padding: 3rem;">
        <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
        <h3 style="color: var(--text-muted); margin: 0 0 1rem 0;">لا توجد مواعيد</h3>
        <p style="color: var(--text-muted); margin: 0;">
          <?php echo $userType === 'student' ? 'لم تقم بحجز أي مواعيد بعد.' : 'لا توجد مواعيد محجوزة مع الطلاب.'; ?>
        </p>
        <?php if ($userType === 'student'): ?>
          <a href="<?php echo $basePath; ?>/appointments/book.php" class="btn btn-primary" style="margin-top: 1rem;">
            <i class="fas fa-calendar-plus"></i> حجز موعد الأن
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="appointments-grid">
        <?php foreach ($appointments as $appointment): ?>
          <div class="card glass appointment-card">
            <div class="appointment-header">
              <div class="appointment-date">
                <i class="fas fa-calendar"></i>
                <?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?>
              </div>
              <div class="appointment-time">
                <i class="fas fa-clock"></i>
                <?php echo date('H:i', strtotime($appointment['appointment_date'])); ?>
              </div>
              <div class="appointment-status status-<?php echo $appointment['status']; ?>">
                <?php
                $statusText = [
                  'pending' => 'في الانتظار',
                  'confirmed' => 'مؤكد',
                  'cancelled' => 'ملغي',
                  'completed' => 'مكتمل'
                ];
                echo $statusText[$appointment['status']] ?? $appointment['status'];
                ?>
              </div>
            </div>

            <div class="appointment-body">
              <?php if ($userType === 'professor'): ?>
                <div class="appointment-student">
                  <strong>الطالب:</strong> <?php echo htmlspecialchars($appointment['student_name'] ?? 'غير محدد'); ?>
                </div>
              <?php else: ?>
                <div class="appointment-professor">
                  <strong>الدكتور:</strong> <?php echo htmlspecialchars($appointment['professor_name'] ?? 'غير محدد'); ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($appointment['notes'])): ?>
                <div class="appointment-notes">
                  <strong>ملاحظات:</strong>
                  <div style="margin-top: 0.5rem; padding: 0.5rem; background: rgba(0,0,0,0.05); border-radius: 4px;">
                    <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="appointment-actions">
              <?php if ($userType === 'professor' && $appointment['status'] === 'pending'): ?>
                <button class="btn btn-success btn-sm" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'confirmed')">
                  <i class="fas fa-check"></i> تأكيد
                </button>
                <button class="btn btn-danger btn-sm" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'cancelled')">
                  <i class="fas fa-times"></i> إلغاء
                </button>
              <?php elseif ($userType === 'student' && $appointment['status'] === 'pending'): ?>
                <button class="btn btn-danger btn-sm" onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'cancelled')">
                  <i class="fas fa-times"></i> إلغاء الحجز
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  .appointments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
  }

  .appointment-card {
    position: relative;
    overflow: hidden;
  }

  .appointment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }

  .appointment-date,
  .appointment-time {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--primary);
  }

  .appointment-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
  }

  .status-pending {
    background: rgba(251, 191, 36, 0.2);
    color: #d97706;
  }

  .status-confirmed {
    background: rgba(16, 185, 129, 0.2);
    color: #059669;
  }

  .status-cancelled {
    background: rgba(239, 68, 68, 0.2);
    color: #dc2626;
  }

  .status-completed {
    background: rgba(59, 130, 246, 0.2);
    color: #2563eb;
  }

  .appointment-body {
    margin-bottom: 1rem;
  }

  .appointment-student,
  .appointment-professor,
  .appointment-course {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
  }

  .appointment-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
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