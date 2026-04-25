<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn() || !isStudent()) {
    header('Location: /auth/login.php');
    exit;
}

$userId = getCurrentUserId();
$studentCourses = getStudentCourses($userId);
$upcomingAppointments = getUpcomingAppointmentsList($userId, 'student');
$analytics = getStudentAnalytics($userId);
$pageTitle = 'لوحة تحكم الطالب';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>مرحباً، <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
        <p>إليك ملخص نشاطك الأكاديمي</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #3498db;">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo count($studentCourses); ?></h3>
                <p>الكورسات المسجلة</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #27ae60;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo getStudentMaterialsCount($userId); ?></h3>
                <p>المواد الدراسية</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #e74c3c;">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo getPendingQuestionsCount($userId, 'student'); ?></h3>
                <p>الأسئلة المعلقة</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f39c12;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo getUpcomingAppointmentsCount($userId, 'student'); ?></h3>
                <p>المواعيد القادمة</p>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-book"></i> كورساتي</h2>
                <a href="/courses/list.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> انضمام لكورس جديد
                </a>
            </div>
            
            <?php if (empty($studentCourses)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>لم تنضم لأي كورس بعد</p>
                    <a href="/courses/list.php" class="btn btn-primary">تصفح الكورسات المتاحة</a>
                </div>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($studentCourses as $course): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                                <span class="badge badge-enrolled">مسجل</span>
                            </div>
                            
                            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            
                            <?php if ($course['description']): ?>
                                <p class="course-description">
                                    <?php echo htmlspecialchars(mb_substr($course['description'], 0, 100)); ?>
                                    <?php if (mb_strlen($course['description']) > 100): ?>...<?php endif; ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="course-meta">
                                <span><i class="fas fa-user"></i> 
                                    <?php echo htmlspecialchars($course['professor_name'] ?? 'غير محدد'); ?>
                                </span>
                                <span><i class="fas fa-calendar"></i> 
                                    <?php echo date('Y-m-d', strtotime($course['enrolled_at'])); ?>
                                </span>
                            </div>
                            
                            <div class="course-actions">
                                <a href="/courses/view.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> عرض الكورس
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-bell"></i> الإشعارات الأخيرة</h2>
            </div>
            
            <?php if (empty($recentNotifications)): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <p>لا توجد إشعارات جديدة</p>
                </div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($recentNotifications as $notification): ?>
                        <li class="list-group-item" style="padding: 15px; border-bottom: 1px solid #eee;">
                            <?php echo htmlspecialchars($notification['message']); ?>
                            <br>
                            <small class="text-muted"><?php echo formatDate($notification['created_at']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-calendar-alt"></i> مواعيدي القادمة</h2>
                <a href="/appointments/view.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-plus"></i> حجز موعد جديد
                </a>
            </div>
            
            <?php if (empty($upcomingAppointments)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>لا توجد مواعيد قادمة</p>
                </div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($upcomingAppointments as $appointment): ?>
                        <li class="list-group-item" style="padding: 15px; border-bottom: 1px solid #eee;">
                            <strong>موعد مع دكتور: <?php echo htmlspecialchars($appointment['other_party']); ?></strong>
                            <br>
                            التاريخ: <?php echo formatDate($appointment['date_time']); ?>
                            (<?php echo $appointment['duration']; ?> دقيقة)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Analytics Section -->
        <div class="dashboard-section" style="margin-top: 30px;">
            <div class="section-header">
                <h2><i class="fas fa-chart-pie"></i> التحليلات الأكاديمية</h2>
            </div>
            <div class="analytics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
                <div class="chart-container" style="background: #f8f9fa; padding: 20px; border-radius: 10px; border: 1px solid #e0e0e0;">
                    <h3 style="text-align: center; margin-bottom: 20px; color: #2c3e50;">حالة المواعيد</h3>
                    <canvas id="appointmentsChart"></canvas>
                </div>
                <div class="chart-container" style="background: #f8f9fa; padding: 20px; border-radius: 10px; border: 1px solid #e0e0e0;">
                    <h3 style="text-align: center; margin-bottom: 20px; color: #2c3e50;">المواد الدراسية في كل كورس</h3>
                    <canvas id="materialsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
}

.dashboard-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.dashboard-header p {
    font-size: 1.2rem;
    opacity: 0.9;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.stat-icon i {
    font-size: 1.5rem;
}

.stat-info h3 {
    font-size: 2rem;
    margin-bottom: 5px;
    color: #2c3e50;
}

.stat-info p {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.dashboard-content {
    display: grid;
    gap: 30px;
}

.dashboard-section {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.section-header h2 {
    color: #2c3e50;
    margin: 0;
}

.section-header h2 i {
    margin-left: 10px;
    color: #3498db;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.course-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    transition: all 0.3s;
    border: 1px solid #e0e0e0;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.course-code {
    background: #3498db;
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.course-card h3 {
    color: #2c3e50;
    margin: 10px 0;
    font-size: 1.2rem;
}

.course-description {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-bottom: 15px;
    line-height: 1.5;
}

.course-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 0.85rem;
    color: #95a5a6;
}

.course-meta i {
    margin-left: 5px;
}

.course-actions {
    display: flex;
    gap: 10px;
}

@media (max-width: 768px) {
    .dashboard-header h1 {
        font-size: 1.8rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .section-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const analytics = <?php echo json_encode($analytics); ?>;
    
    // Materials per course chart
    const ctxMaterials = document.getElementById('materialsChart').getContext('2d');
    if (analytics.courses.length > 0) {
        new Chart(ctxMaterials, {
            type: 'bar',
            data: {
                labels: analytics.courses,
                datasets: [{
                    label: 'عدد المواد الدراسية',
                    data: analytics.materials_count,
                    backgroundColor: 'rgba(39, 174, 96, 0.7)',
                    borderColor: 'rgba(39, 174, 96, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // Appointments Status chart
    const ctxAppts = document.getElementById('appointmentsChart').getContext('2d');
    const totalAppts = analytics.appointments.pending + analytics.appointments.completed + analytics.appointments.cancelled;
    
    if (totalAppts > 0) {
        new Chart(ctxAppts, {
            type: 'doughnut',
            data: {
                labels: ['مواعيد معلقة', 'مواعيد مكتملة', 'مواعيد ملغاة'],
                datasets: [{
                    data: [analytics.appointments.pending, analytics.appointments.completed, analytics.appointments.cancelled],
                    backgroundColor: [
                        'rgba(243, 156, 18, 0.8)',
                        'rgba(46, 204, 113, 0.8)',
                        'rgba(231, 76, 60, 0.8)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
