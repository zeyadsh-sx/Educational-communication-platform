<?php

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'professor') {
    redirect('/auth/login.php');
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

if ($course['professor_id'] != $_SESSION['user_id']) {
    echo '<div class="container"><div class="alert alert-error">You do not have permission to manage this course</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $studentId = $_POST['student_id'] ?? 0;
    
    switch ($action) {
        case 'update_status':
            $status = $_POST['status'] ?? 'active';
            $result = updateStudentStatus($courseId, $studentId, $status);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'remove_student':
            $result = unenrollStudent($courseId, $studentId);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'update_course':
            $courseName = trim($_POST['course_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($courseName)) {
                $message = 'Please enter the course name';
                $messageType = 'error';
            } else {
                $result = updateCourse($courseId, $courseName, $description);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                
                if ($result['success']) {
                    $course = getCourseById($courseId);  
                }
            }
            break;
            
        case 'delete_course':
            $result = deleteCourse($courseId);
            if ($result['success']) {
                header('Location: list.php?message=deleted');
                exit;
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
            break;
    }
}

$students = getCourseStudents($courseId, null);
$studentCount = getCourseStudentCount($courseId);
?>

<div class="container">
    <div class="course-manage">
        <div class="page-header">
            <div>
                <a href="list.php" class="back-link">
                    <i class="fas fa-arrow-right"></i> Back to Courses
                </a>
                <h1>Manage Course</h1>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fas fa-info-circle"></i> Course Information</h2>
            
            <form method="POST" action="" class="course-form">
                <input type="hidden" name="action" value="update_course">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Course Code</label>
                        <input type="text" value="<?php echo htmlspecialchars($course['course_code']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>Course Name</label>
                        <input type="text" 
                               name="course_name" 
                               value="<?php echo htmlspecialchars($course['course_name']); ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Course Description</label>
                    <textarea name="description" rows="3"><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
        

        <div class="stats-row">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-value"><?php echo $studentCount; ?></div>
                <div class="stat-label">Active Students</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-clock"></i>
                <div class="stat-value"><?php echo count($students) - $studentCount; ?></div>
                <div class="stat-label">Suspended Students</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> Registered Students (<?php echo count($students); ?>)</h2>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="empty-state">
                    <p>There are no students registered in this course yet.</p>
                </div>
            <?php else: ?>
                <div class="students-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Enrollment Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($student['enrolled_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $student['status']; ?>">
                                            <?php 
                                                echo $student['status'] === 'active' ? 'Active' : 
                                                    ($student['status'] === 'suspended' ? 'Suspended' : 'Completed'); 
                                            ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <form method="POST" action="" class="inline-form">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="active" <?php echo $student['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="suspended" <?php echo $student['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                                <option value="completed" <?php echo $student['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            </select>
                                        </form>
                                        
                                        <form method="POST" action="" class="inline-form" 
                                              onsubmit="return confirm('Are you sure you want to remove this student?');">
                                            <input type="hidden" name="action" value="remove_student">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" class="btn-icon btn-danger" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card danger-zone">
            <h2><i class="fas fa-exclamation-triangle"></i> Danger Zone</h2>
            <p>Deleting the course will remove all students and associated files.</p>
            
            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this course? This action cannot be undone!');">
                <input type="hidden" name="action" value="delete_course">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Course
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.course-manage {
    padding: 20px;
    max-width: 1000px;
    margin: 0 auto;
}

.page-header {
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

h1 {
    color: #2c3e50;
    margin: 0;
}

.card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card h2 {
    color: #2c3e50;
    font-size: 18px;
    margin: 0 0 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card h2 i { color: #3498db; }

.form-row {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #34495e;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #3498db;
}

.form-group input:disabled {
    background: #f8f9fa;
    color: #7f8c8d;
}

.form-actions {
    margin-top: 20px;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stat-card i {
    font-size: 30px;
    color: #3498db;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #2c3e50;
}

.stat-label {
    color: #7f8c8d;
    font-size: 14px;
}

.students-table {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: right;
    border-bottom: 1px solid #ecf0f1;
}

th {
    background: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
}

.badge {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-active { background: #27ae60; color: white; }
.badge-suspended { background: #f39c12; color: white; }
.badge-completed { background: #3498db; color: white; }

.actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.inline-form {
    display: inline;
}

.inline-form select {
    padding: 5px 10px;
    font-size: 13px;
}

.btn-icon {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-danger { background: #e74c3c; color: white; }
.btn-danger:hover { background: #c0392b; }

.danger-zone {
    border: 2px solid #e74c3c;
}

.danger-zone h2 i { color: #e74c3c; }

.danger-zone p {
    color: #7f8c8d;
    margin-bottom: 15px;
}

.alert {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

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

.btn-primary { background: #3498db; color: white; }
.btn-primary:hover { background: #2980b9; }

.empty-state {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
