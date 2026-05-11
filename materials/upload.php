<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$courseId = $_GET['course_id'] ?? 0;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $course_id = $_POST['course_id'] ?? 0;
        
        if (empty($title) || empty($course_id)) {
            $message = 'Please fill in all required fields';
            $messageType = 'error';
        } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $message = 'Please select a file to upload';
            $messageType = 'error';
        } else {
            $file = $_FILES['file'];
            $fileName = $file['name'];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip'];
            $disallowedExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'exe', 'sh', 'bat', 'cmd', 'vbs'];
            
            if (!in_array($fileType, $allowedExtensions) || in_array($fileType, $disallowedExtensions)) {
                $message = 'Invalid file type. Only PDF, DOC, PPT, TXT, and ZIP are allowed.';
                $messageType = 'error';
            } else {
                $newFileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileType;
                $uploadPath = __DIR__ . '/../uploads/materials/' . $newFileName;
                
                // Create directory if it doesn't exist
                if (!is_dir(dirname($uploadPath))) {
                    mkdir(dirname($uploadPath), 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $database = new Database();
                    $conn = $database->connect();
                    
                    $stmt = $conn->prepare("INSERT INTO materials (title, description, file_name, file_path, file_type, course_id, professor_id, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $title,
                        $description,
                        $fileName,
                        $newFileName,
                        $fileType,
                        $course_id,
                        getCurrentUserId(),
                        getCurrentUserId()
                    ]);
                    
                    if ($result) {
                        $message = 'Material uploaded successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Error saving material to database';
                        $messageType = 'error';
                        // Clean up uploaded file if database insert fails
                        unlink($uploadPath);
                    }
                } else {
                    $message = 'Error uploading file';
                    $messageType = 'error';
                }
            }
        }
    }
}

$pageTitle = 'رفع مادة دراسية';
?>

<div class="container">
    <div class="upload-material">
        <div class="upload-header">
            <h1>رفع مادة دراسية</h1>
            <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="back-link">
                <i class="fas fa-arrow-right"></i> العودة للكورس
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="upload-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="title">عنوان المادة <span class="required">*</span></label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="<?php echo htmlspecialchars($title ?? ''); ?>"
                       required
                       placeholder="أدخل عنوان المادة">
            </div>
            
            <div class="form-group">
                <label for="course_id">الكورس <span class="required">*</span></label>
                <input type="number" 
                       id="course_id" 
                       name="course_id" 
                       value="<?php echo htmlspecialchars($courseId); ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="description">الوصف</label>
                <textarea id="description" 
                          name="description" 
                          rows="4"
                          placeholder="وصف المادة الدراسية..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="file">الملف <span class="required">*</span></label>
                <input type="file" 
                       id="file" 
                       name="file" 
                       required
                       accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip">
                <small>الملفات المسموحة: PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> رفع المادة
                </button>
                <a href="<?php echo $basePath; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<style>
.upload-material {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.upload-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.upload-header h1 {
    color: #2c3e50;
    margin: 0;
}

.back-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.back-link:hover {
    text-decoration: underline;
}

.upload-form {
    background: white;
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

@media (max-width: 768px) {
    .upload-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
