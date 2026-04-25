<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: /auth/login.php');
    exit;
}

$courseId = $_GET['id'] ?? 0;
$message = '';
$messageType = '';

$course = getCourseById($courseId);

if (!$course) {
    echo '<div class="container"><div class="alert alert-error">Course not found</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$isEnrolled = isStudentEnrolled($courseId, $_SESSION['user_id']);

if ($isEnrolled) {
    $message = 'You are already enrolled in this course';
    $messageType = 'info';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isEnrolled) {
    $result = enrollStudent($courseId, $_SESSION['user_id']);
    
    if ($result['success']) {
        $message = 'You have successfully joined the course!';
        $messageType = 'success';
        $isEnrolled = true;
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}
?>

<div class="container">
    <div class="course-join">
        <div class="join-card">
            <a href="list.php" class="back-link">
                <i class="fas fa-arrow-right"></i> Back to Courses
            </a>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="course-info">
                <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                <h1><?php echo htmlspecialchars($course['course_name']); ?></h1>
                
                <div class="professor-info">
                    <i class="fas fa-user-tie"></i>
                    <span>Professor: <?php echo htmlspecialchars($course['professor_name'] ?? 'Not specified'); ?></span>
                </div>
                
                <?php if ($course['description']): ?>
                    <div class="description">
                        <h3>Course Description:</h3>
                        <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="course-stats">
                    <div class="stat">
                        <i class="fas fa-users"></i>
                        <span><?php echo getCourseStudentCount($courseId); ?> Students</span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-calendar"></i>
                        <span>Creation Date: <?php echo date('Y-m-d', strtotime($course['created_at'])); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($isEnrolled): ?>
                <div class="enrolled-actions">
                    <a href="view.php?id=<?php echo $courseId; ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Enter Course
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" action="" class="join-form">
                    <p class="join-message">
                        Do you want to join this course?
                    </p>
                    <button type="submit" class="btn btn-success btn-large">
                        <i class="fas fa-user-plus"></i> Join Course
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.course-join {
    padding: 20px;
    max-width: 600px;
    margin: 0 auto;
}

.join-card {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.back-link {
    display: inline-flex;
    align-items: center;
    color: #3498db;
    text-decoration: none;
    margin-bottom: 20px;
    font-weight: 600;
}

.back-link i {
    margin-left: 8px;
}

.back-link:hover {
    color: #2980b9;
}

.course-info {
    margin-bottom: 30px;
}

.course-code {
    display: inline-block;
    background: #ecf0f1;
    color: #2c3e50;
    padding: 5px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
}

.course-info h1 {
    color: #2c3e50;
    margin: 10px 0 20px;
    font-size: 24px;
}

.professor-info {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #7f8c8d;
    margin-bottom: 20px;
}

.professor-info i {
    color: #3498db;
}

.description {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.description h3 {
    color: #2c3e50;
    font-size: 16px;
    margin-bottom: 10px;
}

.description p {
    color: #7f8c8d;
    line-height: 1.6;
    margin: 0;
}

.course-stats {
    display: flex;
    gap: 20px;
}

.stat {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #95a5a6;
    font-size: 14px;
}

.stat i {
    color: #3498db;
}

.join-form {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #ecf0f1;
}

.join-message {
    color: #7f8c8d;
    margin-bottom: 20px;
    font-size: 16px;
}

.btn-large {
    padding: 15px 40px;
    font-size: 16px;
}

.enrolled-actions {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #ecf0f1;
}

.alert {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-primary { background: #3498db; color: white; }
.btn-primary:hover { background: #2980b9; }

.btn-success { background: #27ae60; color: white; }
.btn-success:hover { background: #219a52; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>