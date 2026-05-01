<?php
session_start();
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

$pdo = getDB();

// Fetch Materials
$materialsStmt = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY upload_date DESC");
$materialsStmt->execute([$courseId]);
$materials = $materialsStmt->fetchAll();

// Fetch Announcements
$announcementsStmt = $pdo->prepare("SELECT a.*, u.full_name as author FROM announcements a JOIN users u ON a.created_by = u.id WHERE course_id = ? ORDER BY created_at DESC");
$announcementsStmt->execute([$courseId]);
$announcements = $announcementsStmt->fetchAll();

// Fetch Questions
$questionsStmt = $pdo->prepare("SELECT q.*, u.full_name as student_name FROM questions q JOIN users u ON q.student_id = u.id WHERE course_id = ? ORDER BY created_at DESC");
$questionsStmt->execute([$courseId]);
$questions = $questionsStmt->fetchAll();

$students = [];
if ($userType === 'professor' && $course['professor_id'] == $userId) {
    $students = getCourseStudents($courseId, 'active');
}

$studentCount = getCourseStudentCount($courseId);
$pageTitle = htmlspecialchars($course['course_name']) . ' | EduFlow';
?>

<div class="container animate-fade">
    <div style="max-width: 1000px; margin: 0 auto;">
        
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
            <div>
                <a href="<?php echo $userType === 'professor' ? '/admin/dashboard.php' : '/student/dashboard.php'; ?>" style="color: var(--primary); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
                </a>
                <div style="margin-bottom: 0.5rem;">
                    <span class="badge badge-primary"><?php echo htmlspecialchars($course['course_code']); ?></span>
                </div>
                <h1 style="font-size: 2.5rem; margin: 0;"><?php echo htmlspecialchars($course['course_name']); ?></h1>
            </div>
            
            <?php if ($userType === 'professor' && $course['professor_id'] == $userId): ?>
                <div>
                    <a href="manage.php?id=<?php echo $courseId; ?>" class="btn btn-secondary">
                        <i class="fas fa-cog"></i> إدارة الكورس
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Info Card -->
        <div class="card glass" style="margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">أستاذ المادة</label>
                    <div style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-user-tie" style="color: var(--primary);"></i> <?php echo htmlspecialchars($course['professor_name'] ?? 'غير محدد'); ?>
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">الطلاب المسجلين</label>
                    <div style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-users" style="color: var(--success);"></i> <?php echo $studentCount; ?> طالب
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">تاريخ الإنشاء</label>
                    <div style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-calendar" style="color: var(--info);"></i> <?php echo date('Y-m-d', strtotime($course['created_at'])); ?>
                    </div>
                </div>
            </div>
            
            <?php if ($course['description']): ?>
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--glass-border);">
                    <label style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">وصف الكورس</label>
                    <p style="margin: 0; line-height: 1.7;"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Content Tabs -->
        <div class="card glass" style="padding: 0; overflow: hidden;">
            <div style="display: flex; border-bottom: 1px solid var(--glass-border); background: rgba(0,0,0,0.02);">
                <button class="tab-btn active" data-tab="materials">
                    <i class="fas fa-file-alt"></i> المواد الدراسية
                </button>
                <button class="tab-btn" data-tab="announcements">
                    <i class="fas fa-bullhorn"></i> الإعلانات
                </button>
                <button class="tab-btn" data-tab="questions">
                    <i class="fas fa-question-circle"></i> الأسئلة والمناقشات
                </button>
                <?php if ($userType === 'professor'): ?>
                    <button class="tab-btn" data-tab="students">
                        <i class="fas fa-users"></i> الطلاب
                    </button>
                <?php endif; ?>
            </div>
            
            <div style="padding: 2rem;">
                
                <!-- Materials Tab -->
                <div class="tab-content active" id="materials">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3 style="margin: 0;">المواد الدراسية (<?php echo count($materials); ?>)</h3>
                        <?php if ($userType === 'professor'): ?>
                            <a href="/materials/upload.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload"></i> رفع ملف
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($materials)): ?>
                        <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                            <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>لا توجد مواد دراسية حتى الآن.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; gap: 1rem;">
                            <?php foreach ($materials as $mat): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-md);">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                            <?php
                                            $ext = strtolower(pathinfo($mat['file_name'], PATHINFO_EXTENSION));
                                            if (in_array($ext, ['pdf'])) echo '<i class="fas fa-file-pdf"></i>';
                                            elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) echo '<i class="fas fa-file-image"></i>';
                                            elseif (in_array($ext, ['mp4', 'avi'])) echo '<i class="fas fa-file-video"></i>';
                                            else echo '<i class="fas fa-file"></i>';
                                            ?>
                                        </div>
                                        <div>
                                            <h4 style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($mat['title'] ?: $mat['file_name']); ?></h4>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?php echo date('Y-m-d', strtotime($mat['upload_date'])); ?>
                                                <?php if ($mat['description']) echo ' &bull; ' . htmlspecialchars($mat['description']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <?php if (in_array(strtolower(pathinfo($mat['file_name'], PATHINFO_EXTENSION)), ['pdf', 'jpg', 'jpeg', 'png'])): ?>
                                            <button onclick="previewFile('<?php echo $mat['id']; ?>', '<?php echo htmlspecialchars($mat['file_name']); ?>')" class="btn btn-secondary btn-sm" title="معاينة">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="/materials/download.php?id=<?php echo $mat['id']; ?>" class="btn btn-primary btn-sm" title="تحميل">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Announcements Tab -->
                <div class="tab-content" id="announcements">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3 style="margin: 0;">الإعلانات (<?php echo count($announcements); ?>)</h3>
                        <?php if ($userType === 'professor'): ?>
                            <a href="/announcements/create.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> إعلان جديد
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($announcements)): ?>
                        <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                            <i class="fas fa-bullhorn" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>لا توجد إعلانات حتى الآن.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; gap: 1.5rem;">
                            <?php foreach ($announcements as $ann): ?>
                                <div style="padding: 1.5rem; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-md); border-right: 4px solid <?php echo $ann['priority'] == 'high' ? 'var(--danger)' : 'var(--info)'; ?>;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                        <h4 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($ann['title']); ?></h4>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($ann['created_at'])); ?></span>
                                    </div>
                                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($ann['content'])); ?></p>
                                    <div style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-muted);">
                                        بواسطة: <?php echo htmlspecialchars($ann['author']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Questions Tab -->
                <div class="tab-content" id="questions">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3 style="margin: 0;">الأسئلة (<?php echo count($questions); ?>)</h3>
                        <?php if ($userType === 'student'): ?>
                            <a href="/questions/ask.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-question"></i> اطرح سؤالاً
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($questions)): ?>
                        <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                            <i class="fas fa-question-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>لم يتم طرح أي أسئلة بعد.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; gap: 1.5rem;">
                            <?php foreach ($questions as $q): ?>
                                <div style="padding: 1.5rem; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-md);">
                                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                            <?php echo mb_substr($q['student_name'], 0, 1); ?>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="display: flex; justify-content: space-between;">
                                                <h5 style="margin: 0; font-size: 0.9rem;"><?php echo htmlspecialchars($q['student_name']); ?></h5>
                                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('Y-m-d H:i', strtotime($q['created_at'])); ?></span>
                                            </div>
                                            <p style="margin: 0.5rem 0 0; font-size: 1rem;"><?php echo nl2br(htmlspecialchars($q['question_text'])); ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php if ($q['answer_text']): ?>
                                        <div style="margin-top: 1rem; margin-right: 3rem; padding: 1rem; background: rgba(16, 185, 129, 0.05); border-radius: var(--radius-sm); border-right: 2px solid var(--success);">
                                            <div style="font-size: 0.85rem; color: var(--success); font-weight: 600; margin-bottom: 0.5rem;">رد الدكتور:</div>
                                            <p style="margin: 0; font-size: 0.95rem;"><?php echo nl2br(htmlspecialchars($q['answer_text'])); ?></p>
                                        </div>
                                    <?php elseif ($userType === 'professor'): ?>
                                        <div style="margin-top: 1rem; margin-right: 3rem;">
                                            <a href="/questions/answer.php?id=<?php echo $q['id']; ?>" class="btn btn-outline btn-sm">رد على السؤال</a>
                                        </div>
                                    <?php else: ?>
                                        <div style="margin-top: 1rem; margin-right: 3rem; font-size: 0.85rem; color: var(--warning);">
                                            <i class="fas fa-clock"></i> في انتظار الرد...
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Students Tab -->
                <?php if ($userType === 'professor'): ?>
                    <div class="tab-content" id="students">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3 style="margin: 0;">الطلاب المسجلين (<?php echo count($students); ?>)</h3>
                        </div>
                        
                        <?php if (empty($students)): ?>
                            <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
                                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>لا يوجد طلاب مسجلين حتى الآن.</p>
                            </div>
                        <?php else: ?>
                            <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                                <?php foreach ($students as $student): ?>
                                    <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-md);">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--secondary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                            <?php echo mb_substr($student['full_name'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($student['email']); ?></div>
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
</div>

<!-- Preview Modal -->
<div id="previewModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center; padding: 2rem;">
    <div class="card glass" style="width: 100%; max-width: 900px; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 id="previewTitle" style="margin: 0; color: var(--text-main);">معاينة الملف</h3>
            <button onclick="closePreview()" style="background: none; border: none; color: var(--danger); font-size: 1.5rem; cursor: pointer;"><i class="fas fa-times"></i></button>
        </div>
        <div id="previewContainer" style="flex: 1; min-height: 500px; background: white; border-radius: var(--radius-sm); overflow: hidden;">
            <!-- Content injected here -->
        </div>
    </div>
</div>

<style>
.tab-btn {
    flex: 1;
    padding: 1rem;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--text-muted);
    font-weight: 600;
    font-family: inherit;
    font-size: 1rem;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.tab-btn:hover {
    color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
}

.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    background: rgba(99, 102, 241, 0.1);
}

.tab-content {
    display: none;
    animation: fadeIn 0.4s ease;
}

.tab-content.active {
    display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-btn');
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

function previewFile(id, filename) {
    const modal = document.getElementById('previewModal');
    const container = document.getElementById('previewContainer');
    document.getElementById('previewTitle').textContent = filename;
    
    const ext = filename.split('.').pop().toLowerCase();
    
    // Create iframe for PDF, image for images
    if (ext === 'pdf') {
        container.innerHTML = `<iframe src="/materials/download.php?id=${id}" style="width: 100%; height: 100%; border: none;"></iframe>`;
    } else {
        container.innerHTML = `<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #eee;"><img src="/materials/download.php?id=${id}" style="max-width: 100%; max-height: 100%; object-fit: contain;"></div>`;
    }
    
    modal.style.display = 'flex';
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
    document.getElementById('previewContainer').innerHTML = ''; // Clear to stop loading
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
