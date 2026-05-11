<?php
// Start session and include required files
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is professor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'professor') {
    redirect('/auth/login.php');
    exit;
}

$message = '';
$messageType = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token';
        $messageType = 'error';
    } else {
        $courseName = trim($_POST['course_name'] ?? '');
        $courseCode = trim($_POST['course_code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        // Validation
        if (empty($courseName) || empty($courseCode)) {
            $message = 'Please fill in all required fields';
            $messageType = 'error';
        } elseif (courseCodeExists($courseCode)) {
            $message = 'Course code already exists';
            $messageType = 'error';
        } else {
            // Create course
            $result = createCourse($courseName, $courseCode, $_SESSION['user_id'], $description);
            
            if ($result['success']) {
                $message = 'Course created successfully!';
                $messageType = 'success';
                // Clear form
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
    <div class="form-container">
        <h1>Create New Course</h1>
        
        <!-- Show message if exists -->
        <?php if ($message): ?>
            <div class="message message-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Course creation form -->
        <form method="POST" class="course-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="field">
                <label for="course_name">Course Name *</label>
                <input type="text" id="course_name" name="course_name" 
                       value="<?php echo htmlspecialchars($courseName ?? ''); ?>"
                       required placeholder="Enter course name">
            </div>
            
            <div class="field">
                <label for="course_code">Course Code *</label>
                <input type="text" id="course_code" name="course_code" 
                       value="<?php echo htmlspecialchars($courseCode ?? ''); ?>"
                       required placeholder="Example: CS101">
                <small>Unique code for course</small>
            </div>
            
            <div class="field">
                <label for="description">Course Description</label>
                <textarea id="description" name="description" rows="4"
                          placeholder="Enter course description..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            
            <div class="actions">
                <button type="submit" class="btn btn-primary">Create Course</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 30px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-container h1 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 30px;
}

.field {
    margin-bottom: 20px;
}

.field label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #34495e;
}

.field input,
.field textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}

.field input:focus,
.field textarea:focus {
    outline: none;
    border-color: #3498db;
}

.field small {
    display: block;
    margin-top: 5px;
    color: #7f8c8d;
    font-size: 12px;
}

.actions {
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
    text-align: center;
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

.message {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.message-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.message-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
