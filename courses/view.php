<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

$courseId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];
$userType = $_SESSION['user_type'];

$course = getCourseById($courseId);

if (!$course) {
    echo '<div class="container"><div class="alert alert-error">الكورس غير موجود</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$hasAccess = false;
$isEnrolled = false;

if ($userType === 'professor' && $course['professor_id'] == $userId) {
    $hasAccess = true;
} elseif ($userType === 'student') {
    $isEnrolled = isStudentEnrolled($courseId, $userId);
    $hasAccess = $isEnrolled;
}

if (!$hasAccess) {
    echo '<div class="container"><div class="alert alert-error">ليس لديك صلاحية الوصول لهذا الكورس</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$students = [];
if ($userType === 'professor' && $course['professor_id'] == $userId) {
    $students = getCourseStudents($courseId, 'active');
}

$studentCount = getCourseStudentCount($courseId);
?>

<div class="container">
    <div class="course-view">
        <div class="page-header">
            <div>
                <a href="list.php" class="back-link">
                    <i class="fas fa-arrow-right"></i> Back to Courses
                </a>
                <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
            </div>
            
            <?php if ($userType === 'professor' && $course['professor_id'] == $userId): ?>
                <div class="header-actions">
                    <a href="manage.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">
                        <i class="fas fa-cog"></i> Manage Course
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="info-section">
            <div class="info-card">
                <h2><i class="fas fa-info-circle"></i> Course Information</h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <label>Professor</label>
                        <span><?php echo htmlspecialchars($course['professor_name'] ?? 'Not specified'); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Number of Students</label>
                        <span><?php echo $studentCount; ?> students</span>
                    </div>
                    <div class="info-item">
                        <label>Creation Date</label>
                        <span><?php echo date('Y-m-d', strtotime($course['created_at'])); ?></span>
                    </div>
                </div>
                
                <?php if ($course['description']): ?>
                    <div class="description">
                        <label>Course Description</label>
                        <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="content-section">
            <div class="tabs">
                <button class="tab active" data-tab="materials">
                    <i class="fas fa-file-alt"></i> Educational Materials
                </button>
                <button class="tab" data-tab="announcements">
                    <i class="fas fa-bullhorn"></i> Announcements
                </button>
                <button class="tab" data-tab="questions">
                    <i class="fas fa-question-circle"></i> Questions
                </button>
                <?php if ($userType === 'professor' && $course['professor_id'] == $userId): ?>
                    <button class="tab" data-tab="students">
                        <i class="fas fa-users"></i> Students (<?php echo count($students); ?>)
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="tab-content active" id="materials">
                <div class="content-header">
                    <h3>Educational materials</h3>
                    <?php if ($userType === 'professor'): ?>
                        <a href="/materials/upload.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload"></i> Upload Material
                        </a>
                    <?php endif; ?>
                </div>
                <div class="empty-content">
                    <i class="fas fa-folder-open"></i>
                    <p>No educational materials available yet.</p>
                </div>
            </div>
            
            <div class="tab-content" id="announcements">
                <div class="content-header">
                    <h3>Announcements</h3>
                    <?php if ($userType === 'professor'): ?>
                        <a href="/announcements/create.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Announcement
                        </a>
                    <?php endif; ?>
                </div>
                <div class="empty-content">
                    <i class="fas fa-bullhorn"></i>
                    <p>No announcements available yet.</p>
                </div>
            </div>
            
            <div class="tab-content" id="questions">
                <div class="content-header">
                    <h3>Questions</h3>
                    <?php if ($userType === 'student'): ?>
                        <a href="/questions/ask.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-question"></i> Ask Question
                        </a>
                    <?php endif; ?>
                </div>
                <div class="empty-content">
                    <i class="fas fa-question-circle"></i>
                    <p>No questions available yet.</p>
                </div>
            </div>
            
            <?php if ($userType === 'professor' && $course['professor_id'] == $userId): ?>
                <div class="tab-content" id="students">
                    <div class="content-header">
                        <h3>Students</h3>
                    </div>
                    
                    <?php if (empty($students)): ?>
                        <div class="empty-content">
                            <i class="fas fa-users"></i>
                            <p>No students enrolled yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="students-list">
                            <?php foreach ($students as $student): ?>
                                <div class="student-item">
                                    <div class="student-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="student-info">
                                        <span class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></span>
                                        <span class="student-email"><?php echo htmlspecialchars($student['email']); ?></span>
                                    </div>
                                    <div class="student-actions">
                                        <a href="/questions/answer.php?student_id=<?php echo $student['id']; ?>&course_id=<?php echo $courseId; ?>" 
                                           class="btn-icon" title="Questions">
                                            <i class="fas fa-question"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            tabContents.forEach(content => content.classList.remove('active'));
            
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>

<style>
.course-view {
    padding: 20px;
    max-width: 1000px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    color: #3498db;
    text-decoration: none;
    margin-bottom: 10px;
    font-weight: 600;
}

.back-link i { margin-left: 8px; }

.course-code {
    display: inline-block;
    background: #ecf0f1;
    color: #2c3e50;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 5px;
}

h1 {
    color: #2c3e50;
    margin: 5px 0 0;
    font-size: 24px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.info-section {
    margin-bottom: 30px;
}

.info-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.info-card h2 {
    color: #2c3e50;
    font-size: 18px;
    margin: 0 0 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-card h2 i { color: #3498db; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.info-item label {
    display: block;
    color: #7f8c8d;
    font-size: 13px;
    margin-bottom: 5px;
}

.info-item span {
    color: #2c3e50;
    font-weight: 600;
}

.description {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
}

.description label {
    display: block;
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 10px;
}

.description p {
    color: #7f8c8d;
    line-height: 1.6;
    margin: 0;
}

.content-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #ecf0f1;
}

.tab {
    flex: 1;
    padding: 15px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: #7f8c8d;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.tab:hover {
    color: #3498db;
    background: #ecf0f1;
}

.tab.active {
    color: #3498db;
    background: white;
    border-bottom: 2px solid #3498db;
}

.tab-content {
    display: none;
    padding: 25px;
}

.tab-content.active {
    display: block;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.content-header h3 {
    color: #2c3e50;
    margin: 0;
}

.empty-content {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.empty-content i {
    font-size: 40px;
    margin-bottom: 15px;
    color: #bdc3c7;
}

.students-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.student-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.student-avatar {
    width: 40px;
    height: 40px;
    background: #3498db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.student-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.student-name {
    color: #2c3e50;
    font-weight: 600;
}

.student-email {
    color: #7f8c8d;
    font-size: 13px;
}

.btn-icon {
    padding: 8px 12px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
}

.btn-icon:hover {
    background: #2980b9;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-sm {
    padding: 8px 15px;
    font-size: 13px;
}

.btn-primary { background: #3498db; color: white; }
.btn-primary:hover { background: #2980b9; }

.btn-secondary { background: #95a5a6; color: white; }
.btn-secondary:hover { background: #7f8c8d; }

.alert {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs {
        flex-wrap: wrap;
    }
    
    .tab {
        flex: none;
        width: 50%;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>