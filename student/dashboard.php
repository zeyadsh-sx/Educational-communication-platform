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
$analytics = getStudentAnalytics($userId);
$studentPoints = 0;

// Fetch student points
$pdo = getDB();
$stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
$stmt->execute([$userId]);
$studentPoints = $stmt->fetchColumn();
$studentRank = getStudentRank($userId);
$achievements = getStudentAchievements($userId);

$pageTitle = 'لوحة تحكم الطالب | EduFlow';
?>

<div class="container animate-fade">
    <div style="margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">مرحباً، <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👋</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">إليك نظرة شاملة على تقدمك الأكاديمي وتفاعلك في المنصة.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--primary); background: rgba(99, 102, 241, 0.1);"><i class="fas fa-star"></i></div>
            <div class="stat-value"><?php echo $studentPoints; ?></div>
            <div class="stat-label">إجمالي النقاط</div>
        </div>
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--accent); background: rgba(245, 158, 11, 0.1);"><i class="fas fa-trophy"></i></div>
            <div class="stat-value">#<?php echo $studentRank; ?></div>
            <div class="stat-label">ترتيبك الحالي</div>
        </div>
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--success); background: rgba(16, 185, 129, 0.1);"><i class="fas fa-book"></i></div>
            <div class="stat-value"><?php echo count($studentCourses); ?></div>
            <div class="stat-label">الكورسات المسجلة</div>
        </div>
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--info); background: rgba(59, 130, 246, 0.1);"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-value"><?php echo getUpcomingAppointmentsCount($userId, 'student'); ?></div>
            <div class="stat-label">المواعيد القادمة</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
        
        <!-- Courses List -->
        <div class="card glass">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; margin: 0;"><i class="fas fa-book" style="margin-left: 10px; color: var(--primary);"></i> كورساتي</h2>
                <a href="<?php echo getBaseUrl(); ?>/courses/list.php" class="btn btn-primary btn-sm">تصفح المزيد</a>
            </div>
            
            <?php if (empty($studentCourses)): ?>
                <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                    <i class="fas fa-book-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>لم تنضم لأي كورس بعد.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($studentCourses as $course): ?>
                        <div style="padding: 1rem; border-radius: var(--radius-md); background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($course['course_name']); ?></h4>
                                <span class="badge badge-primary"><?php echo htmlspecialchars($course['course_code']); ?></span>
                            </div>
                            <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $course['id']; ?>" class="btn btn-outline btn-sm">عرض</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Achievements & Progress -->
        <div class="card glass">
            <h2 style="font-size: 1.5rem; margin-bottom: 2rem;"><i class="fas fa-award" style="margin-left: 10px; color: var(--accent);"></i> أوسمتي وإنجازاتي</h2>
            
            <?php if (empty($achievements)): ?>
                <div style="text-align: center; padding: 2rem 0; color: var(--text-muted);">
                    <p>ابدأ التفاعل في المنصة لتحصل على أوسمة تميز!</p>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 1rem; text-align: center;">
                    <?php foreach ($achievements as $ach): ?>
                        <div style="padding: 1rem; border-radius: var(--radius-md); background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2);">
                            <i class="fas <?php echo $ach['achievement_icon']; ?>" style="font-size: 2rem; color: var(--accent); margin-bottom: 0.5rem;"></i>
                            <div style="font-size: 0.75rem; font-weight: 700;"><?php echo htmlspecialchars($ach['achievement_name']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 2.5rem; padding-top: 2rem; border-top: 1px solid var(--glass-border);">
                <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem;">كيف أحصل على المزيد من النقاط؟</h3>
                <ul style="list-style: none; padding: 0; font-size: 0.9rem; color: var(--text-muted);">
                    <li style="margin-bottom: 0.75rem;"><i class="fas fa-check-circle" style="color: var(--success); margin-left: 10px;"></i> طرح سؤال جديد (+10 نقاط)</li>
                    <li style="margin-bottom: 0.75rem;"><i class="fas fa-check-circle" style="color: var(--success); margin-left: 10px;"></i> تحميل مادة دراسية (+5 نقاط)</li>
                    <li style="margin-bottom: 0.75rem;"><i class="fas fa-check-circle" style="color: var(--success); margin-left: 10px;"></i> حجز موعد مكتبي (+15 نقطة)</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
