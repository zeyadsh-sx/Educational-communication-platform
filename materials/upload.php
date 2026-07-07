<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/header.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$courseId = getSafeGet('course_id', 0, 'int');
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $message = 'توكن الأمان غير صحيح';
        $messageType = 'error';
    } else {
        $title = getSafePost('title', '', 'string');
        $description = getSafePost('description', '', 'string');
        $courseId = getSafePost('course_id', 0, 'int');
        
        if (empty($title) || strlen($title) < 3) {
            $message = 'عنوان المادة يجب أن يكون على الأقل 3 أحرف';
            $messageType = 'error';
        } elseif ($courseId <= 0) {
            $message = 'معرف الكورس غير صحيح';
            $messageType = 'error';
        } else {
            // Validate file upload
            $uploadResult = validateFileUpload('file', 
                ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'xls', 'xlsx'],
                10485760  // 10MB
            );
            
            if (!$uploadResult['valid']) {
                $message = $uploadResult['error'];
                $messageType = 'error';
            } else {
                $file = $uploadResult['file'];
                
                // Validate MIME type
                $mimeAllowed = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/zip',
                    'text/plain'
                ];
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($mimeType, $mimeAllowed)) {
                    $message = 'نوع الملف غير مسموح (MIME type)';
                    $messageType = 'error';
                } else {
                    try {
                        $pdo = getDB();
                        $userId = getSafeUserId();
                        
                        // Verify user is professor of this course
                        $checkStmt = $pdo->prepare("
                            SELECT id FROM courses 
                            WHERE id = ? AND professor_id = ?
                        ");
                        $checkStmt->execute([$courseId, $userId]);
                        
                        if (!$checkStmt->fetch()) {
                            $message = 'أنت لا تملك صلاحية رفع ملفات لهذا الكورس';
                            $messageType = 'error';
                        } else {
                            // Generate safe filename
                            $newFileName = generateSafeFilename($file['name']);
                            $uploadDir = __DIR__ . '/../uploads/materials';
                            
                            // Create directory if it doesn't exist
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            
                            $uploadPath = $uploadDir . '/' . $newFileName;
                            
                            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                                // Save to database
                                $stmt = $pdo->prepare("
                                    INSERT INTO materials (title, description, file_name, file_path, file_type, course_id, professor_id, uploaded_by, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                                ");
                                
                                $stmt->execute([
                                    $title,
                                    $description,
                                    $file['name'],
                                    'uploads/materials/' . $newFileName,
                                    $uploadResult['extension'],
                                    $courseId,
                                    $userId,
                                    $userId
                                ]);
                                
                                $message = 'تم رفع المادة بنجاح!';
                                $messageType = 'success';
                                
                                // Reset form
                                $title = '';
                                $description = '';
                            } else {
                                $message = 'خطأ في رفع الملف';
                                $messageType = 'error';
                            }
                        }
                    } catch (PDOException $e) {
                        logError('Material upload error', ['error' => $e->getMessage()]);
                        $message = 'حدث خطأ في قاعدة البيانات';
                        $messageType = 'error';
                    }
                }
            }
        }
    }
}

$pageTitle = 'رفع مادة دراسية | EduFlow';
?>

<div class="container" style="margin: 2rem auto;">
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <h1>📤 رفع مادة دراسية</h1>
        </div>
        
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="form-group" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="title" class="form-label required">عنوان المادة</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           class="form-control"
                           value="<?php echo htmlspecialchars($title ?? ''); ?>"
                           required
                           minlength="3"
                           placeholder="أدخل عنوان المادة">
                </div>
                
                <div class="form-group">
                    <label for="course_id" class="form-label required">معرف الكورس</label>
                    <input type="number" 
                           id="course_id" 
                           name="course_id" 
                           class="form-control"
                           value="<?php echo htmlspecialchars($courseId); ?>"
                           required
                           min="1">
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">الوصف</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-control"
                              rows="4"
                              placeholder="وصف المادة الدراسية..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="file" class="form-label required">الملف</label>
                    <input type="file" 
                           id="file" 
                           name="file" 
                           class="form-control"
                           required
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,.xls,.xlsx">
                    <p class="form-help">الملفات المسموحة: PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP, XLS, XLSX<br>الحد الأقصى للحجم: 10 MB</p>
                </div>
                
                <div style="display: flex; gap: 0.75rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> رفع المادة
                    </button>
                    <a href="<?php echo $basePath ?? ''; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
