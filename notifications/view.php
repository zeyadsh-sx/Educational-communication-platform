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
$pdo = getDB();

// Get all notifications for the user
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

// Mark all notifications as read
$updateStmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
$updateStmt->execute([$userId]);

$pageTitle = 'الإشعارات | EduFlow';
?>

<div class="container animate-fade">
  <div style="max-width: 800px; margin: 2rem auto;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <h1 style="font-size: 2rem; margin: 0;">الإشعارات</h1>
      <a href="<?php echo $basePath; ?>/student/dashboard.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">
        <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
      </a>
    </div>

    <?php if (empty($notifications)): ?>
      <div class="card glass" style="text-align: center; padding: 3rem;">
        <i class="fas fa-bell-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
        <h3 style="color: var(--text-muted); margin: 0 0 1rem 0;">لا توجد إشعارات</h3>
        <p style="color: var(--text-muted); margin: 0;">
          ستظهر هنا إشعاراتك عندما تكون متوفرة.
        </p>
      </div>
    <?php else: ?>
      <div class="notifications-list">
        <?php foreach ($notifications as $notification): ?>
          <div class="card glass notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" style="margin-bottom: 1rem; padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.95rem; line-height: 1.5;">
                  <?php echo htmlspecialchars($notification['message']); ?>
                </p>
                <div style="margin-top: 0.75rem; font-size: 0.8rem; color: var(--text-muted);">
                  <i class="fas fa-clock"></i>
                  <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                </div>
              </div>
              <?php if (!$notification['is_read']): ?>
                <div class="notification-dot" style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%; margin-left: 1rem;"></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="margin-top: 2rem; text-align: center;">
        <button onclick="markAllAsRead()" class="btn btn-outline" style="margin-right: 1rem;">
          <i class="fas fa-check-double"></i> تحديد الكل كمقروء
        </button>
        <button onclick="clearAllNotifications()" class="btn btn-danger">
          <i class="fas fa-trash"></i> حذف الكل
        </button>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  .notification-item.unread {
    border-right: 4px solid var(--primary);
    background: rgba(99, 102, 241, 0.05);
  }

  .notification-item {
    transition: var(--transition);
  }

  .notification-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--card-shadow);
  }
</style>

<script>
  function markAllAsRead() {
    if (confirm('هل تريد تحديد جميع الإشعارات كمقروءة؟')) {
      // Since we already marked them as read on page load, just reload
      location.reload();
    }
  }

  function clearAllNotifications() {
    if (confirm('هل تريد حذف جميع الإشعارات؟ هذا الإجراء لا يمكن التراجع عنه.')) {
      fetch('<?php echo $basePath; ?>/api/notifications/clear.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('حدث خطأ أثناء حذف الإشعارات');
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