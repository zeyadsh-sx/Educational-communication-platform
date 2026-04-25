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
                <h3>0</h3>
                <p>المواد الدراسية</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #e74c3c;">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="stat-info">
                <h3>0</h3>
                <p>الأسئلة المعلقة</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f39c12;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h3>0</h3>
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
            
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <p>لا توجد إشعارات جديدة</p>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-calendar-alt"></i> مواعيدي القادمة</h2>
                <a href="/appointments/view.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-plus"></i> حجز موعد جديد
                </a>
            </div>
            
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>لا توجد مواعيد قادمة</p>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
