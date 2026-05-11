<?php

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'professor') {
    redirect('/auth/login.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $courseName = trim($_POST['course_name'] ?? '');
        $courseCode = trim($_POST['course_code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($courseName) || empty($courseCode)) {
        $message = 'Please fill in all required fields ';
        $messageType = 'error';
    } elseif (courseCodeExists($courseCode)) {
        $message = 'Course code already exists';
        $messageType = 'error';
    } else {
        $result = createCourse($courseName, $courseCode, $_SESSION['user_id'], $description);
        
        if ($result['success']) {
            $message = 'Course created successfully!';
            $messageType = 'success';

            $courseName = '';
            $courseCode = '';
            $description = '';
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
    }
}
?>

<div class="container">
    <div class="course-create">
        <h1>Create New Course</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="course-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="course_name">Course Name <span class="required">*</span></label>
                <input type="text" 
                       id="course_name" 
                       name="course_name" 
                       value="<?php echo htmlspecialchars($courseName ?? ''); ?>"
                       required
                       placeholder="Enter course name">
            </div>
            
            <div class="form-group">
                <label for="course_code">Course Code <span class="required">*</span></label>
                <input type="text" 
                       id="course_code" 
                       name="course_code" 
                       value="<?php echo htmlspecialchars($courseCode ?? ''); ?>"
                       required
                       placeholder="Example: CS101">
                <small>Unique code for the course (e.g., CS101, MATH202)</small>
            </div>
            
            <div class="form-group">
                <label for="description">Course Description</label>
                <textarea id="description" 
                          name="description" 
                          rows="5"
                          placeholder="Enter course description..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Course
                </button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.course-create {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.course-create h1 {
    color: #2c3e50;
    margin-bottom: 30px;
    text-align: center;
}

.course-form {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #34495e;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3498db;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #7f8c8d;
    font-size: 12px;
}

.required {
    color: #e74c3c;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
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

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
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
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
