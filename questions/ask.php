<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

requireRole('student');

$userId   = getCurrentUserId();
$base     = nagahBaseUrl();
$pdo      = getDB();
$courseId = (int)($_GET['course_id'] ?? 0);
$message  = '';
$msgKind  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'توكن الأمان غير صحيح. الرجاء تحديث الصفحة.';
        $msgKind = 'error';
    } else {
        $cid           = (int)($_POST['course_id']     ?? 0);
        $questionText  = trim($_POST['question_text']  ?? '');

        if (empty($questionText) || !$cid) {
            $message = 'الرجاء اختيار الكورس وكتابة سؤالك'; $msgKind = 'error';
        } else {
            // Verify student is enrolled
            $enrStmt = $pdo->prepare("SELECT id FROM course_enrollments WHERE course_id=? AND student_id=? AND status='active'");
            $enrStmt->execute([$cid, $userId]);
            if (!$enrStmt->fetch()) {
                $message = 'أنت غير مسجل في هذا الكورس'; $msgKind = 'error';
            } else {
                // Get professor_id
                $cStmt = $pdo->prepare("SELECT professor_id FROM courses WHERE id=?");
                $cStmt->execute([$cid]);
                $course = $cStmt->fetch();

                if (!$course) {
                    $message = 'الكورس غير موجود'; $msgKind = 'error';
                } else {
                    $ins = $pdo->prepare("INSERT INTO questions (question_text, student_id, professor_id, course_id, status) VALUES (?,?,?,?,'pending')");
                    $ins->execute([$questionText, $userId, $course['professor_id'], $cid]);

                    // Award points
                    awardPoints($userId, 'ask_question');

                    // Notify professor
                    $nameStmt = $pdo->prepare("SELECT full_name FROM users WHERE id=?");
                    $nameStmt->execute([$userId]);
                    $studentName = $nameStmt->fetchColumn();

                    if (function_exists('sendSystemNotification')) {
                        require_once __DIR__ . '/../includes/subscription_functions.php';
                        sendSystemNotification($course['professor_id'], "📩 سؤال جديد من $studentName — انتظر ردك!", 'general');
                    }

                    $message = 'تم إرسال سؤالك بنجاح! حصلت على 10 نقاط 🎉';
                    $msgKind = 'success';
                    $courseId = $cid;
                }
            }
        }
    }
}

$studentCourses = getStudentCourses($userId);

$pageTitle = 'طرح سؤال | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<section class="relative min-h-[calc(100vh-80px)] flex items-center justify-center py-16 overflow-hidden auth-bg">
    <span class="blob" style="width:380px;height:380px;background:#60A5FA;top:-80px;right:-80px;"></span>
    <span class="blob" style="width:300px;height:300px;background:#7c3aed;bottom:-80px;left:-60px;opacity:.3;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>

    <div class="relative z-10 w-full max-w-xl mx-auto px-5">
        <?php if ($courseId): ?>
        <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $courseId; ?>"
           class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-600 text-sm font-medium mb-6 transition">
            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i> العودة للكورس
        </a>
        <?php else: ?>
        <a href="<?php echo $base; ?>/student/dashboard.php"
           class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-600 text-sm font-medium mb-6 transition">
            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i> لوحة التحكم
        </a>
        <?php endif; ?>

        <div class="glass rounded-[28px] p-8 reveal">
            <div class="text-center mb-7">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-lg"
                      style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                    <i data-lucide="help-circle" style="width:22px;height:22px;"></i>
                </span>
                <h1 class="display font-semibold text-2xl text-slate-900">طرح سؤال جديد</h1>
                <p class="text-sm text-slate-500 mt-1.5">اطرح سؤالك على المعلم وستحصل على 10 نقاط</p>
            </div>

            <?php if ($message): ?>
            <div class="rounded-xl px-4 py-3 text-sm font-medium mb-6
                <?php echo $msgKind === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($msgKind === 'success' && $courseId): ?>
                <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $courseId; ?>"
                   class="block mt-2 font-bold underline">العودة للكورس ←</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (empty($studentCourses)): ?>
            <div class="text-center py-8 text-slate-400">
                <i data-lucide="book-open" style="width:44px;height:44px;" class="mx-auto mb-3 opacity-30"></i>
                <p class="text-sm">يجب أن تنضم لكورس أولاً قبل طرح سؤال</p>
                <a href="<?php echo $base; ?>/courses/list.php"
                   class="mt-4 inline-flex items-center gap-2 btn-primary-nagah px-5 py-2.5 rounded-full text-sm font-bold">
                    تصفح الكورسات
                </a>
            </div>
            <?php else: ?>
            <form method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">
                        الكورس <span class="text-red-500">*</span>
                    </label>
                    <select name="course_id" class="field-input" required>
                        <option value="">— اختر الكورس —</option>
                        <?php foreach ($studentCourses as $c): ?>
                        <option value="<?php echo $c['id']; ?>"
                                <?php echo $courseId == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['course_name']); ?>
                            (<?php echo htmlspecialchars($c['course_code']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">
                        سؤالك <span class="text-red-500">*</span>
                    </label>
                    <textarea name="question_text" rows="5" required class="field-input resize-none"
                              placeholder="اكتب سؤالك بوضوح…"></textarea>
                </div>

                <div class="bg-blue-50 rounded-xl px-4 py-3 text-xs text-blue-700 flex items-start gap-2">
                    <i data-lucide="info" style="width:14px;height:14px;flex-shrink:0;margin-top:1px;"></i>
                    <span>تأكد من وضوح السؤال — ستُشعَر فور رد المعلم — ستحصل على <strong>10 نقاط</strong> مقابل كل سؤال</span>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                            class="btn-primary-nagah flex-1 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" style="width:16px;height:16px;"></i> إرسال السؤال
                    </button>
                    <a href="<?php echo $base; ?>/student/dashboard.php"
                       class="px-6 py-3 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition text-center">
                        إلغاء
                    </a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
