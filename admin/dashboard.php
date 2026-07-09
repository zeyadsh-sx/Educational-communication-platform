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
$pageTitle = 'لوحة تحكم المعلم | أكاديمية ماستر';

$pdo = getDB();
$totalStudentsAll = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'student'")->fetchColumn();
$totalCoursesAll = (int) $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$activeStudents = (int) $pdo->query("SELECT COUNT(DISTINCT student_id) FROM course_enrollments WHERE status = 'active'")->fetchColumn();

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
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo getEmoji('welcome'); ?> مرحباً، دكتور <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">إليك ملخص شامل لنشاط كورساتك وتفاعلك مع الطلاب.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--primary); background: rgba(99, 102, 241, 0.1);"><i class="fas fa-book"></i></div>
            <div class="stat-value"><?php echo count($professorCourses); ?></div>
            <div class="stat-label"><?php echo getEmoji('courses'); ?> الكورسات الحالية</div>
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
            <div class="stat-label"><?php echo getEmoji('users'); ?> إجمالي الطلاب</div>
        </div>
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--danger); background: rgba(239, 68, 68, 0.1);"><i class="fas fa-question-circle"></i></div>
            <div class="stat-value"><?php echo getPendingQuestionsCount($userId, 'professor'); ?></div>
            <div class="stat-label"><?php echo getEmoji('questions'); ?> أسئلة معلقة</div>
        </div>
        <div class="card glass stat-card">
            <div class="stat-icon" style="color: var(--warning); background: rgba(245, 158, 11, 0.1);"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-value"><?php echo getUpcomingAppointmentsCount($userId, 'professor'); ?></div>
            <div class="stat-label"><?php echo getEmoji('appointments'); ?> المواعيد القادمة</div>
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

    <!-- Analytics Charts -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card glass" style="padding: 1.5rem;">
            <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fas fa-chart-line" style="color: var(--primary);"></i> نمو الطلاب</h3>
            <canvas id="growthChart" height="180"></canvas>
        </div>
        <div class="card glass" style="padding: 1.5rem;">
            <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fas fa-book" style="color: var(--success);"></i> التسجيل في الكورسات</h3>
            <canvas id="enrollmentChart" height="180"></canvas>
        </div>
        <div class="card glass" style="padding: 1.5rem;">
            <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fas fa-money-bill-wave" style="color: var(--accent);"></i> الإيرادات</h3>
            <canvas id="revenueChart" height="180"></canvas>
        </div>
        <div class="card glass" style="padding: 1.5rem;">
            <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fas fa-user-check" style="color: var(--info);"></i> الطلاب النشطون</h3>
            <div style="text-align: center; padding: 1rem 0;">
                <div style="font-size: 3rem; font-weight: 800; color: var(--primary);"><?php echo $activeStudents; ?></div>
                <p style="color: var(--text-secondary); margin: 0;">من <?php echo $totalStudentsAll; ?> طالب مسجل</p>
            </div>
            <canvas id="activeChart" height="120"></canvas>
        </div>
    </div>

    <!-- Appointments Management -->
    <div class="card glass" style="margin-top: 2rem;">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;"><i class="fas fa-calendar-alt" style="margin-left: 10px; color: var(--warning);"></i> المواعيد القادمة</h2>
        <?php if (empty($upcomingAppointments)): ?>
            <div style="text-align: center; padding: 2.5rem 0; color: var(--text-muted);">
                <i class="fas fa-calendar-times" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.35;"></i>
                <p>لا توجد مواعيد جديدة في الوقت الحالي.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                <?php foreach ($upcomingAppointments as $appointment): ?>
                    <div class="card" style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                            <div>
                                <div style="font-size: 1rem; font-weight: 700;"><?php echo htmlspecialchars($appointment['other_party'] ?? 'طالب'); ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($appointment['status'] === 'pending' ? 'معلق' : ($appointment['status'] === 'confirmed' ? 'مؤكد' : 'ملغي')); ?></div>
                            </div>
                            <span class="badge <?php echo $appointment['status'] === 'confirmed' ? 'badge-success' : ($appointment['status'] === 'pending' ? 'badge-warning' : 'badge-danger'); ?>">
                                <?php echo $appointment['status'] === 'confirmed' ? 'مؤكد' : ($appointment['status'] === 'pending' ? 'معلق' : 'ملغي'); ?>
                            </span>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($appointment['date_time'])); ?>
                            <i class="fas fa-clock" style="margin-right: 10px;"></i> <?php echo date('H:i', strtotime($appointment['date_time'])); ?>
                        </div>
                        <?php if (!empty($appointment['notes'])): ?>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">
                                <strong>ملاحظات:</strong> <?php echo htmlspecialchars(mb_substr($appointment['notes'], 0, 60)); ?><?php echo strlen($appointment['notes']) > 60 ? '...' : ''; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: 1.25rem; text-align: center;">
                <a href="<?php echo $basePath; ?>/appointments/view.php" class="btn btn-outline">عرض جميع المواعيد</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const analytics = <?php echo json_encode($analytics); ?>;
        const textColor = getComputedStyle(document.body).getPropertyValue('--text-primary') || '#333';
        const chartDefaults = {
            responsive: true,
            plugins: { legend: { labels: { color: textColor, font: { family: 'Cairo' } } } },
            scales: { y: { ticks: { color: textColor } }, x: { ticks: { color: textColor } } }
        };

        new Chart(document.getElementById('questionsChart'), {
            type: 'doughnut',
            data: {
                labels: ['معلقة', 'مجابة'],
                datasets: [{ data: [analytics.questions.pending, analytics.questions.answered], backgroundColor: ['#EF4444', '#10B981'], borderWidth: 0 }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: textColor } } }, cutout: '70%' }
        });

        new Chart(document.getElementById('growthChart'), {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{ label: 'طلاب جدد', data: [45, 62, 78, 95, 110, <?php echo $totalStudentsAll; ?>], borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,0.1)', fill: true, tension: 0.4 }]
            },
            options: chartDefaults
        });

        new Chart(document.getElementById('enrollmentChart'), {
            type: 'bar',
            data: {
                labels: ['رياضيات', 'فيزياء', 'كيمياء', 'عربي', 'إنجليزي'],
                datasets: [{ label: 'مسجلون', data: [120, 95, 88, 76, 82], backgroundColor: ['#2563EB', '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6'] }]
            },
            options: chartDefaults
        });

        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{ label: 'جنيه', data: [15000, 22000, 28000, 35000, 42000, 48000], backgroundColor: '#F59E0B' }]
            },
            options: chartDefaults
        });

        new Chart(document.getElementById('activeChart'), {
            type: 'doughnut',
            data: {
                labels: ['نشط', 'غير نشط'],
                datasets: [{ data: [<?php echo $activeStudents; ?>, <?php echo max(0, $totalStudentsAll - $activeStudents); ?>], backgroundColor: ['#2563EB', '#E5E7EB'], borderWidth: 0 }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: textColor } } }, cutout: '65%' }
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>