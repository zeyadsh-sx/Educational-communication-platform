<?php
require_once __DIR__ . '/_layout.php';
studentPageStart('الواجبات', 'homework');

$userId  = getCurrentUserId();
$pdo     = getDB();
$base    = nagahBaseUrl();
$message = '';
$msgKind = '';

// Submit homework answer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['hw_id'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'توكن الأمان غير صحيح'; $msgKind = 'error';
    } else {
        $hwId    = (int)$_POST['hw_id'];
        $answer  = trim($_POST['answer_text'] ?? '');
        $file    = $_FILES['answer_file'] ?? null;
        $filePath = null;

        // Optional file upload
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf','doc','docx','jpg','jpeg','png','zip'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $message = 'نوع الملف غير مسموح'; $msgKind = 'error';
            } elseif ($file['size'] > 10 * 1024 * 1024) {
                $message = 'حجم الملف يتجاوز 10 MB'; $msgKind = 'error';
            } else {
                $dir = __DIR__ . '/../uploads/homework';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $fname = 'hw_' . $hwId . '_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $dir . '/' . $fname)) {
                    $filePath = 'uploads/homework/' . $fname;
                }
            }
        }

        if ($msgKind !== 'error') {
            // Check table exists
            try {
                // Upsert submission
                $check = $pdo->prepare("SELECT id FROM homework_submissions WHERE homework_id=? AND student_id=?");
                $check->execute([$hwId, $userId]);
                if ($check->fetch()) {
                    $upd = $pdo->prepare("UPDATE homework_submissions SET answer_text=?, answer_file=?, submitted_at=NOW(), status='submitted' WHERE homework_id=? AND student_id=?");
                    $upd->execute([$answer, $filePath, $hwId, $userId]);
                } else {
                    $ins = $pdo->prepare("INSERT INTO homework_submissions (homework_id, student_id, answer_text, answer_file, status) VALUES (?,?,?,?,'submitted')");
                    $ins->execute([$hwId, $userId, $answer, $filePath]);
                }
                $message = 'تم تسليم الواجب بنجاح! 🎉'; $msgKind = 'success';
            } catch (PDOException $e) {
                $message = 'خطأ في قاعدة البيانات'; $msgKind = 'error';
            }
        }
    }
}

// Fetch homeworks for enrolled courses
$hwStmt = $pdo->prepare("
    SELECT h.*, c.course_name, u.full_name AS professor_name,
           hs.answer_text, hs.answer_file, hs.grade, hs.feedback, hs.submitted_at,
           hs.status AS sub_status
    FROM homeworks h
    JOIN courses c ON h.course_id = c.id
    JOIN users u ON c.professor_id = u.id
    JOIN course_enrollments ce ON ce.course_id = h.course_id AND ce.student_id = ?
    LEFT JOIN homework_submissions hs ON hs.homework_id = h.id AND hs.student_id = ?
    WHERE ce.status = 'active'
    ORDER BY h.due_date ASC, h.created_at DESC
");
$hwList = [];
try {
    $hwStmt->execute([$userId, $userId]);
    $hwList = $hwStmt->fetchAll();
} catch (PDOException $e) { /* table may not exist yet */ }

$now = new DateTime();
?>

<!-- Page header -->
<div class="mb-8">
    <h1 class="display font-semibold text-2xl sm:text-3xl text-slate-900 flex items-center gap-3">
        <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
              style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
            <i data-lucide="notebook-pen" style="width:17px;height:17px;"></i>
        </span>
        الواجبات
    </h1>
    <p class="text-slate-500 mt-1 text-sm"><?php echo count($hwList); ?> واجب</p>
</div>

<?php if ($message): ?>
<div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-7 flex items-center gap-3
    <?php echo $msgKind==='success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
    <i data-lucide="<?php echo $msgKind==='success'?'check-circle':'alert-circle'; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if (empty($hwList)): ?>
<div class="glass rounded-3xl p-14 text-center text-slate-400">
    <i data-lucide="notebook-pen" style="width:52px;height:52px;" class="mx-auto mb-4 opacity-30"></i>
    <h3 class="font-bold text-lg text-slate-500">لا توجد واجبات حالياً</h3>
    <p class="text-sm mt-2">ستظهر هنا واجبات مواد اشتراكاتك</p>
</div>
<?php else: ?>
<div class="space-y-5">
<?php foreach ($hwList as $hw):
    $dueDate    = new DateTime($hw['due_date']);
    $isOverdue  = $dueDate < $now && $hw['sub_status'] !== 'submitted';
    $isSubmitted = $hw['sub_status'] === 'submitted';
    $daysLeft   = (int)$now->diff($dueDate)->days * ($dueDate >= $now ? 1 : -1);
    $isExpiring = $daysLeft >= 0 && $daysLeft <= 2 && !$isSubmitted;
?>
<article class="glass rounded-3xl overflow-hidden <?php echo $isExpiring ? 'ring-2 ring-amber-400' : ''; ?>">
    <div class="px-6 pt-5 pb-4 flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <!-- Subject + title -->
            <div class="flex items-center gap-2 mb-1">
                <span class="tag-pill text-xs font-bold px-2.5 py-1 rounded-full">
                    <?php echo htmlspecialchars($hw['course_name']); ?>
                </span>
                <?php if ($isSubmitted): ?>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-green-100 text-green-700 flex items-center gap-1">
                    <i data-lucide="check" style="width:11px;height:11px;"></i> مُسلَّم
                </span>
                <?php elseif ($isOverdue): ?>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700">فات الوقت</span>
                <?php elseif ($isExpiring): ?>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 flex items-center gap-1">
                    <i data-lucide="alert-triangle" style="width:11px;height:11px;"></i> ينتهي قريباً
                </span>
                <?php endif; ?>
            </div>
            <h3 class="font-bold text-slate-900 text-base"><?php echo htmlspecialchars($hw['title']); ?></h3>
            <?php if (!empty($hw['description'])): ?>
            <p class="text-sm text-slate-600 mt-1 leading-relaxed"><?php echo nl2br(htmlspecialchars($hw['description'])); ?></p>
            <?php endif; ?>
            <div class="flex items-center gap-4 mt-2 text-xs text-slate-400">
                <span class="flex items-center gap-1">
                    <i data-lucide="user-round" style="width:11px;height:11px;"></i>
                    <?php echo htmlspecialchars($hw['professor_name']); ?>
                </span>
                <span class="flex items-center gap-1 <?php echo $isOverdue ? 'text-red-500 font-bold' : ($isExpiring ? 'text-amber-600 font-bold' : ''); ?>">
                    <i data-lucide="clock" style="width:11px;height:11px;"></i>
                    التسليم: <?php echo date('d/m/Y', strtotime($hw['due_date'])); ?>
                </span>
            </div>
        </div>
        <?php if ($hw['grade'] !== null): ?>
        <div class="text-center shrink-0">
            <p class="display font-semibold text-2xl" style="color:#2563EB"><?php echo $hw['grade']; ?></p>
            <p class="text-xs text-slate-400">/ <?php echo $hw['max_grade'] ?? 100; ?></p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($hw['feedback']): ?>
    <div class="mx-6 mb-4 rounded-2xl p-4 bg-blue-50 border border-blue-100">
        <p class="text-xs font-bold text-blue-700 mb-1 flex items-center gap-1">
            <i data-lucide="message-square" style="width:12px;height:12px;"></i> تعليق المعلم
        </p>
        <p class="text-sm text-blue-800"><?php echo nl2br(htmlspecialchars($hw['feedback'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Submission form -->
    <?php if (!$isSubmitted && !$isOverdue): ?>
    <details class="border-t border-slate-100">
        <summary class="px-6 py-3.5 flex items-center gap-2 cursor-pointer text-sm font-bold text-blue-600 hover:bg-blue-50/50 transition list-none">
            <i data-lucide="upload" style="width:14px;height:14px;"></i>
            تسليم الواجب
            <i data-lucide="chevron-down" style="width:14px;height:14px;margin-right:auto" class="details-chevron"></i>
        </summary>
        <div class="px-6 pb-6 pt-2">
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="hw_id" value="<?php echo $hw['id']; ?>">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">إجابتك النصية</label>
                    <textarea name="answer_text" rows="4" class="field-input resize-none"
                              placeholder="اكتب إجابتك هنا…"><?php echo htmlspecialchars($hw['answer_text'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">أو ارفع ملف (PDF / صورة / Word)</label>
                    <input type="file" name="answer_file" class="field-input py-2 text-sm"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip">
                </div>
                <button type="submit"
                        class="btn-primary-nagah inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-bold shadow hover:-translate-y-0.5 transition-all text-sm">
                    <i data-lucide="send" style="width:14px;height:14px;"></i> تسليم الواجب
                </button>
            </form>
        </div>
    </details>
    <?php elseif ($isSubmitted): ?>
    <div class="px-6 py-3.5 border-t border-slate-100 flex items-center gap-2 text-sm text-green-700 bg-green-50/50">
        <i data-lucide="check-circle" style="width:15px;height:15px;"></i>
        سُلِّم في <?php echo $hw['submitted_at'] ? date('d/m/Y H:i', strtotime($hw['submitted_at'])) : '—'; ?>
    </div>
    <?php endif; ?>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>

<style>details[open] .details-chevron { transform: rotate(180deg); }</style>
<?php studentPageEnd(); ?>
