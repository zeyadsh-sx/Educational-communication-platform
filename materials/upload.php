<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$courseId    = getSafeGet('course_id', 0, 'int');
$base        = nagahBaseUrl();
$message     = '';
$messageKind = '';
$title_val   = '';
$desc_val    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $message = 'توكن الأمان غير صحيح. الرجاء تحديث الصفحة.';
        $messageKind = 'error';
    } else {
        $title_val = getSafePost('title', '', 'string');
        $desc_val  = getSafePost('description', '', 'string');
        $cid       = getSafePost('course_id', 0, 'int');

        if (empty($title_val) || strlen($title_val) < 3) {
            $message = 'عنوان المادة يجب أن يكون على الأقل 3 أحرف'; $messageKind = 'error';
        } elseif ($cid <= 0) {
            $message = 'معرف الكورس غير صحيح'; $messageKind = 'error';
        } else {
            $uploadResult = validateFileUpload('file',
                ['pdf','doc','docx','ppt','pptx','txt','zip','xls','xlsx'],
                10485760
            );
            if (!$uploadResult['valid']) {
                $message = $uploadResult['error']; $messageKind = 'error';
            } else {
                $file = $uploadResult['file'];
                $mimeAllowed = ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/zip','text/plain'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, $mimeAllowed)) {
                    $message = 'نوع الملف غير مسموح به'; $messageKind = 'error';
                } else {
                    try {
                        $pdo = getDB();
                        $uid = getSafeUserId();
                        $chk = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND professor_id = ?");
                        $chk->execute([$cid, $uid]);
                        if (!$chk->fetch()) {
                            $message = 'ليس لديك صلاحية رفع ملفات لهذا الكورس'; $messageKind = 'error';
                        } else {
                            $newFileName = generateSafeFilename($file['name']);
                            $uploadDir   = __DIR__ . '/../uploads/materials';
                            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                            $uploadPath = $uploadDir . '/' . $newFileName;

                            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                                $ins = $pdo->prepare("INSERT INTO materials (title, description, file_name, file_path, file_type, course_id, professor_id, uploaded_by, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())");
                                $ins->execute([$title_val, $desc_val, $file['name'], 'uploads/materials/' . $newFileName, $uploadResult['extension'], $cid, $uid, $uid]);
                                $message = 'تم رفع المادة الدراسية بنجاح!'; $messageKind = 'success';
                                $title_val = $desc_val = '';
                            } else {
                                $message = 'خطأ في رفع الملف'; $messageKind = 'error';
                            }
                        }
                    } catch (PDOException $e) {
                        $message = 'حدث خطأ في قاعدة البيانات'; $messageKind = 'error';
                    }
                }
            }
        }
    }
}

$allowedList = 'PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP, XLS, XLSX';
$pageTitle   = 'رفع مادة دراسية | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<section class="relative min-h-[calc(100vh-80px)] flex items-center justify-center py-16 overflow-hidden auth-bg">
    <span class="blob" style="width:360px;height:360px;background:#60A5FA;top:-80px;right:-80px;"></span>
    <span class="blob" style="width:300px;height:300px;background:#2563EB;bottom:-80px;left:-60px;opacity:.4;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>

    <div class="relative z-10 w-full max-w-xl mx-auto px-5">
        <?php if ($courseId): ?>
        <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-600 text-sm font-medium mb-6 transition">
            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i> العودة للكورس
        </a>
        <?php endif; ?>

        <div class="glass rounded-[28px] p-8 reveal">
            <div class="text-center mb-7">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-lg" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <i data-lucide="upload-cloud" style="width:22px;height:22px;"></i>
                </span>
                <h1 class="display font-semibold text-2xl text-slate-900">رفع مادة دراسية</h1>
                <p class="text-sm text-slate-500 mt-1.5">أضف ملفات ومواد لطلابك</p>
            </div>

            <?php if ($message): ?>
            <div class="rounded-xl px-4 py-3 text-sm font-medium mb-6 <?php echo $messageKind === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($messageKind === 'success' && $courseId): ?>
                    <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="block mt-2 font-bold underline">العودة للكورس ←</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-5" id="upload-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="course_id"  value="<?php echo $courseId; ?>">

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">عنوان المادة <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="field-input" required minlength="3" placeholder="مثال: ملخص الفصل الأول" value="<?php echo htmlspecialchars($title_val); ?>">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">وصف المادة</label>
                    <textarea name="description" rows="3" class="field-input resize-none" placeholder="وصف اختياري عن محتوى الملف…"><?php echo htmlspecialchars($desc_val); ?></textarea>
                </div>

                <!-- Drop zone -->
                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">الملف <span class="text-red-500">*</span></label>
                    <label for="file-input" id="drop-zone"
                           class="flex flex-col items-center justify-center gap-3 w-full rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 hover:border-blue-500 hover:bg-blue-50/30 cursor-pointer transition p-8">
                        <span class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:rgba(37,99,235,.1)">
                            <i data-lucide="file-up" style="width:22px;height:22px;color:#2563EB"></i>
                        </span>
                        <div class="text-center">
                            <p class="font-semibold text-slate-700 text-sm">اسحب الملف هنا أو انقر للاختيار</p>
                            <p class="text-xs text-slate-400 mt-1" id="file-label"><?php echo $allowedList; ?> — الحد الأقصى 10 MB</p>
                        </div>
                        <input id="file-input" type="file" name="file" class="sr-only" required
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,.xls,.xlsx"
                               onchange="document.getElementById('file-label').textContent = this.files[0]?.name || '<?php echo $allowedList; ?>'">
                    </label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" id="upload-btn" class="btn-primary-nagah flex-1 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="upload" style="width:16px;height:16px;"></i>
                        <span id="upload-label">رفع المادة</span>
                        <i data-lucide="loader-2" id="upload-spin" class="spin hidden" style="width:16px;height:16px;"></i>
                    </button>
                    <?php if ($courseId): ?>
                    <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="px-6 py-3 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition text-center">إلغاء</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.getElementById('upload-form')?.addEventListener('submit', function() {
    const btn = document.getElementById('upload-btn');
    const spin = document.getElementById('upload-spin');
    btn.disabled = true; btn.style.opacity = '.7';
    spin?.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
// Drag & drop highlight
const dz = document.getElementById('drop-zone');
['dragover','dragenter'].forEach(ev => dz?.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('border-blue-500','bg-blue-50'); }));
['dragleave','drop'].forEach(ev => dz?.addEventListener(ev, () => dz.classList.remove('border-blue-500','bg-blue-50')));
</script>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
