<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/course_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$search = trim($_GET['search'] ?? '');
$userType = $_SESSION['user_type'] ?? '';
$userId = $_SESSION['user_id'];

if ($userType === 'professor') {
    $courses = getCourses($userId, $search);
} elseif ($userType === 'student') {
    $courses = getCourses(null, $search);
} else {
    $courses = getCourses(null, $search);
}
?>

<div class="container">
    <div class="courses-list">
        <div class="page-header">
            <h1>
                <?php echo $userType === 'professor' ? 'My Courses' : 'Available Courses'; ?>
            </h1>
            
            <?php if ($userType === 'professor'): ?>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Course
                </a>
            <?php endif; ?>
        </div>

        <form method="GET" action="" class="search-form">
            <div class="search-box">
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search courses...">
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        
        <?php if (empty($courses)): ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <p>No courses found <?php echo $search ? 'matching your search' : 'available at the moment'; ?></p>
                <?php if ($userType === 'professor' && !$search): ?>
                    <a href="create.php" class="btn btn-primary">Create First Course</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): 
                    $studentCount = getCourseStudentCount($course['id']);
                    $isEnrolled = $userType === 'student' ? isStudentEnrolled($course['id'], $userId) : false;
                ?>
                    <div class="course-card">
                        <div class="course-header">
                            <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                            <?php if ($isEnrolled): ?>
                                <span class="badge enrolled">Enrolled</span>
                            <?php endif; ?>
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
                                <?php echo htmlspecialchars($course['professor_name'] ?? 'Not specified'); ?>
                            </span>
                            <span><i class="fas fa-users"></i> <?php echo $studentCount; ?> Students</span>
                        </div>
                        
                        <div class="course-actions">
                            <a href="view.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">
                                View Details
                            </a>
                            
                            <?php if ($userType === 'student' && !$isEnrolled): ?>
                                <a href="join.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-success">
                                    Join Course
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($userType === 'professor' && $course['professor_id'] == $userId): ?>
                                <a href="manage.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-secondary">
                                    Manage Course
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.courses-list {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    color: #2c3e50;
    margin: 0;
}

.search-form {
    margin-bottom: 30px;
}

.search-box {
    display: flex;
    max-width: 500px;
    margin: 0 auto;
}

.search-box input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px 0 0 6px;
    font-size: 14px;
}

.search-box input:focus {
    outline: none;
    border-color: #3498db;
}

.btn-search {
    padding: 12px 20px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 0 6px 6px 0;
    cursor: pointer;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.course-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.course-code {
    background: #ecf0f1;
    color: #2c3e50;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge.enrolled {
    background: #27ae60;
    color: white;
}

.course-card h3 {
    color: #2c3e50;
    margin: 10px 0;
    font-size: 18px;
}

.course-description {
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 15px;
    line-height: 1.5;
}

.course-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 13px;
    color: #95a5a6;
}

.course-meta i {
    margin-right: 5px;
}

.course-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
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

.btn-success { background: #27ae60; color: white; }
.btn-success:hover { background: #219a52; }

.btn-secondary { background: #95a5a6; color: white; }
.btn-secondary:hover { background: #7f8c8d; }

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.empty-state i {
    font-size: 60px;
    margin-bottom: 20px;
    color: #bdc3c7;
}

.empty-state p {
    font-size: 18px;
    margin-bottom: 20px;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
