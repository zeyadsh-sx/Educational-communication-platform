<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || (!isProfessor() && !isAdmin())) {
    redirect('/auth/login.php');
    exit;
}

$courseId    = (int)($_GET['course_id'] ?? 0);
$base        = nagahBaseUrl();
$message     = '';
$messageKind = '';
$title_val   = '';
$content_val = '';
$priority_val = 'medium';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $message = 'توكن الأمان غير صحيح. الرجاء تحديث الصفحة.';
        $messageKind = 'error';
    } else {
        $title_val   = trim(getSafePost('title', '', 'string'));
        $content_val = trim(getSafePost('content', '', 'string'));
        $cid         = (int) getSafePost('course_id', 0, 'int');
        $priority_val = in_array(getSafePost('priority', 'medium'), ['low','medium','high'])
                        ? getSafePost('priority', 'medium')
                        : 'medium';

        if (empty($title_val) || empty($content_val)) {
            $message = 'الرجاء ملء جميع الحقول المطلوبة';
            $messageKind = 'error';
        } else {
            try {
                $pdo  = getDB();
                $uid  = getCurrentUserId();
                $stmt = $pdo->prepare("INSERT INTO announcements (title, content, professor_id, course_id, priority, created_by) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$title_val, $content_val, $uid, $cid ?: null, $priority_val, $uid]);
                $message = 'تم نشر الإعلان بنجاح!';
                $messageKind = 'success';
                $title_val = $content_val = '';
            } catch (PDOException $e) {
                $message = 'حدث خطأ عند حفظ الإعلان';
                $messageKind = 'error';
            }
        }
    }
}

$pageTitle = 'إنشاء إعلان | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<section class="relative min-h-[calc(100vh-80px)] flex items-center justify-center py-16 overflow-hidden auth-bg">
    <span class="blob" style="width:380px;height:380px;background:#60A5FA;top:-100px;right:-80px;"></span>
    <span class="blob" style="width:300px;height:300px;background:#F59E0B;bottom:-80px;left:-60px;opacity:.35;"></span>
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
                    <i data-lucide="megaphone" style="width:22px;height:22px;"></i>
                </span>
                <h1 class="display font-semibold text-2xl text-slate-900">إنشاء إعلان جديد</h1>
                <p class="text-sm text-slate-500 mt-1.5">أرسل إعلاناً لطلاب الكورس</p>
            </div>

            <?php if ($message): ?>
                <div class="rounded-xl px-4 py-3 text-sm font-medium mb-6 <?php echo $messageKind === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                    <?php if ($messageKind === 'success' && $courseId): ?>
                        <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="block mt-2 font-bold underline">العودة للكورس ←</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5" id="ann-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="course_id"  value="<?php echo $courseId; ?>">

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">عنوان الإعلان <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="field-input" required placeholder="مثال: درس جديد في الغد" value="<?php echo htmlspecialchars($title_val); ?>">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">الأولوية</label>
                    <div class="flex gap-3">
                        <?php foreach (['low'=>['منخفضة','bg-slate-100 text-slate-600','border-slate-300'], 'medium'=>['متوسطة','bg-blue-50 text-blue-700','border-blue-300'], 'high'=>['عالية','bg-red-50 text-red-700','border-red-300']] as $pval => [$plabel, $pbg, $pborder]): ?>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="priority" value="<?php echo $pval; ?>" class="sr-only peer" <?php echo $priority_val === $pval ? 'checked' : ''; ?>>
                            <span class="block text-center text-xs font-bold py-2.5 rounded-xl border-2 transition peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 <?php echo $pbg . ' ' . $pborder; ?>">
                                <?php echo $plabel; ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">محتوى الإعلان <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="5" class="field-input resize-none" required placeholder="اكتب تفاصيل الإعلان…"><?php echo htmlspecialchars($content_val); ?></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary-nagah flex-1 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" style="width:16px;height:16px;"></i> نشر الإعلان
                    </button>
                    <?php if ($courseId): ?>
                    <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="px-6 py-3 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition text-center">إلغاء</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
