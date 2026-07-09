<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('/auth/login.php');
    exit;
}

$userId = getCurrentUserId();
$studentCourses = getStudentCourses($userId);
$upcomingAppointments = getUpcomingAppointmentsList($userId, 'student');
$studentPoints = 0;

$pdo = getDB();
$stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
$stmt->execute([$userId]);
$studentPoints = $stmt->fetchColumn() ?: 0;
$studentRank = getStudentRank($userId);

$pageTitle = 'لوحة تحكم الطالب | أكاديمية ماستر';
$completedLessons = min(count($studentCourses) * 12, 48);
$upcomingExams = 3;
$attendanceRate = 92;
?>

<div class="container dashboard-layout animate-fade">
    <?php require_once __DIR__ . '/../includes/dashboard_sidebar.php'; ?>

    <div class="dashboard-main">
        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 1.85rem; margin-bottom: 0.5rem;">
                <i class="fas fa-hand-sparkles" style="color: var(--accent);"></i>
                مرحباً، <?php echo htmlspecialchars($_SESSION['full_name']); ?>!
            </h1>
            <p style="color: var(--text-secondary);">إليك نظرة شاملة على تقدمك الدراسي</p>
        </div>

        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            <div class="card glass stat-card">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div class="stat-value"><?php echo count($studentCourses); ?></div>
                <div class="stat-label">الكورسات المسجلة</div>
            </div>
            <div class="card glass stat-card">
                <div class="stat-icon" style="color: var(--success);"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value"><?php echo $completedLessons; ?></div>
                <div class="stat-label">الدروس المكتملة</div>
            </div>
            <div class="card glass stat-card">
                <div class="stat-icon" style="color: var(--accent);"><i class="fas fa-file-alt"></i></div>
                <div class="stat-value"><?php echo $upcomingExams; ?></div>
                <div class="stat-label">امتحانات قادمة</div>
            </div>
            <div class="card glass stat-card">
                <div class="stat-icon" style="color: var(--info);"><i class="fas fa-user-check"></i></div>
                <div class="stat-value"><?php echo $attendanceRate; ?>%</div>
                <div class="stat-label">نسبة الحضور</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;" id="courses">
            <div class="card glass">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.25rem; margin: 0;"><i class="fas fa-book" style="color: var(--primary); margin-left: 8px;"></i> كورساتي</h2>
                    <a href="<?php echo getBaseUrl(); ?>/courses/list.php" class="btn btn-primary btn-sm">تصفح المزيد</a>
                </div>
                <?php if (empty($studentCourses)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        <i class="fas fa-book-open" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 1rem; display: block;"></i>
                        <p>لم تنضم لأي كورس بعد</p>
                        <a href="<?php echo getBaseUrl(); ?>/index.php#courses" class="btn btn-primary btn-sm">استكشف الكورسات</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($studentCourses as $course): ?>
                    <div style="padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?php echo htmlspecialchars($course['course_name']); ?></strong>
                            <div><span class="badge badge-primary"><?php echo htmlspecialchars($course['course_code']); ?></span></div>
                        </div>
                        <a href="<?php echo getBaseUrl(); ?>/courses/view.php?id=<?php echo $course['id']; ?>" class="btn btn-outline btn-sm">عرض</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card glass">
                <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;"><i class="fas fa-bell" style="color: var(--accent); margin-left: 8px;"></i> الإشعارات الأخيرة</h2>
                <?php
                $notifStmt = $pdo->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                $notifStmt->execute([$userId]);
                $notifs = $notifStmt->fetchAll();
                if (empty($notifs)): ?>
                    <p style="color: var(--text-secondary); text-align: center; padding: 1rem;">لا توجد إشعارات</p>
                <?php else: ?>
                    <?php foreach ($notifs as $n): ?>
                    <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                        <p style="margin: 0 0 0.25rem; font-size: 0.9rem;"><?php echo htmlspecialchars($n['message']); ?></p>
                        <small style="color: var(--text-secondary);"><?php echo date('d/m/Y H:i', strtotime($n['created_at'])); ?></small>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="<?php echo getBaseUrl(); ?>/notifications/view.php" class="btn btn-outline btn-sm" style="margin-top: 1rem; width: 100%;">عرض جميع الإشعارات</a>
            </div>
        </div>

        <div class="card glass" style="margin-top: 1.5rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 1rem;"><i class="fas fa-star" style="color: var(--accent); margin-left: 8px;"></i> نقاطك وترتيبك</h2>
            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                <div><strong style="font-size: 1.5rem; color: var(--primary);"><?php echo $studentPoints; ?></strong> <span style="color: var(--text-secondary);">نقطة</span></div>
                <div><strong style="font-size: 1.5rem; color: var(--accent);">#<?php echo $studentRank; ?></strong> <span style="color: var(--text-secondary);">ترتيبك</span></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
