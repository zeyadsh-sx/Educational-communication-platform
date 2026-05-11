<?php
// Start session and include required files
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/header.php';

// Check if professor is logged in
if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$userId = getCurrentUserId();
$professorCourses = getCourses($userId);
$recentQuestions = getRecentQuestions($userId, 'professor');
$upcomingAppointments = getUpcomingAppointmentsList($userId, 'professor');
$analytics = getProfessorAnalytics($userId);
$pageTitle = 'Professor Dashboard | EduFlow';

// Calculate statistics
$totalStudents = 0;
foreach ($professorCourses as $course) {
    $totalStudents += getCourseStudentCount($course['id']);
}
$pendingQuestions = getPendingQuestionsCount($userId, 'professor');
$upcomingAppointmentsCount = getUpcomingAppointmentsCount($userId, 'professor');
?>

<div class="container animate-fade">
    <div style="margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">مرحباً، دكتور <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👨‍🏫</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">إليك ملخص شامل لنشاط كورساتك وتفاعلك مع الطلاب.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--primary); background: rgba(99, 102, 241, 0.1);"><i class="fas fa-book"></i></div>
            <div class="stat-value"><?php echo count($professorCourses); ?></div>
            <div class="stat-label">الكورسات الحالية</div>
        </div>
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--success); background: rgba(16, 185, 129, 0.1);"><i class="fas fa-users"></i></div>
            <div class="stat-value">
                <?php 
                $totalStudents = 0;
                foreach ($professorCourses as $course) {
                    $totalStudents += getCourseStudentCount($course['id']);
                }
                echo $totalStudents;
                ?>
            </div>
            <div class="stat-label">إجمالي الطلاب</div>
        </div>
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--danger); background: rgba(239, 68, 68, 0.1);"><i class="fas fa-question-circle"></i></div>
            <div class="stat-value"><?php echo getPendingQuestionsCount($userId, 'professor'); ?></div>
            <div class="stat-label">أسئلة معلقة</div>
        </div>
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--warning); background: rgba(245, 158, 11, 0.1);"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-value"><?php echo getUpcomingAppointmentsCount($userId, 'professor'); ?></div>
            <div class="stat-label">المواعيد القادمة</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        
        <!-- Courses Management -->
        <div class="card glass">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; margin: 0;"><i class="fas fa-list-check" style="margin-left: 10px; color: var(--primary);"></i> إدارة الكورسات</h2>
                <a href="<?php echo getBaseUrl(); ?>/courses/create.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> كورس جديد</a>
            </div>
            
            <?php if (empty($professorCourses)): ?>
                <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                    <p>لم تقم بإنشاء أي كورس بعد.</p>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($professorCourses as $course): ?>
                        <div class="card" style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span class="badge badge-primary"><?php echo htmlspecialchars($course['course_code']); ?></span>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-users"></i> <?php echo getCourseStudentCount($course['id']); ?></span>
                            </div>
                            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem;"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $course['id']; ?>" class="btn btn-outline btn-sm" style="flex: 1;">عرض</a>
                                <a href="<?php echo $basePath; ?>/courses/manage.php?id=<?php echo $course['id']; ?>" class="btn btn-secondary btn-sm"><i class="fas fa-cog"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity Side Panel -->
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            <!-- Quick Questions -->
            <div class="card glass">
                <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;"><i class="fas fa-comments" style="margin-left: 10px; color: var(--danger);"></i> أسئلة حديثة</h2>
                <?php if (empty($recentQuestions)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; text-align: center;">لا توجد أسئلة حالياً.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($recentQuestions as $q): ?>
                            <div style="padding: 0.75rem; border-radius: var(--radius-sm); background: rgba(255,255,255,0.03); border-right: 3px solid var(--danger);">
                                <div style="font-size: 0.85rem; font-weight: 600;"><?php echo htmlspecialchars($q['student_name']); ?></div>
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0.25rem 0;"><?php echo htmlspecialchars(mb_substr($q['question_text'], 0, 50)); ?>...</p>
                                <a href="<?php echo $basePath; ?>/questions/answer.php?id=<?php echo $q['id']; ?>" style="font-size: 0.75rem; color: var(--primary); text-decoration: none; font-weight: 700;">رد الآن ←</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Analytics Chart -->
            <div class="card glass">
                <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;"><i class="fas fa-chart-pie" style="margin-left: 10px; color: var(--success);"></i> إحصائيات سريعة</h2>
                <canvas id="questionsChart" style="max-height: 200px;"></canvas>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const analytics = <?php echo json_encode($analytics); ?>;
    const ctx = document.getElementById('questionsChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['معلقة', 'مجابة'],
            datasets: [{
                data: [analytics.questions.pending, analytics.questions.answered],
                backgroundColor: ['#ef4444', '#10b981'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: getComputedStyle(document.body).getPropertyValue('--text-main') } }
            },
            cutout: '70%'
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
