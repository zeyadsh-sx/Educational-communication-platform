<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('/auth/login.php');
    exit;
}

$courseId    = (int)($_GET['id'] ?? 0);
$base        = nagahBaseUrl();
$message     = '';
$messageKind = '';

$course = getCourseById($courseId);
if (!$course) {
    $pageTitle = 'غير موجود';
    require __DIR__ . '/../includes/nagah/head.php';
    require __DIR__ . '/../includes/nagah/nav.php';
    echo '<div class="max-w-xl mx-auto px-5 py-20 text-center"><div class="glass rounded-3xl p-12"><i data-lucide="alert-circle" class="mx-auto text-red-400 mb-4" style="width:48px;height:48px;"></i><p class="text-slate-500">الكورس غير موجود.</p></div></div>';
    require __DIR__ . '/../includes/nagah/footer.php';
    exit;
}

$isEnrolled = isStudentEnrolled($courseId, $_SESSION['user_id']);
if ($isEnrolled) {
    $message = 'أنت مسجّل بالفعل في هذا الكورس';
    $messageKind = 'info';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isEnrolled) {
    $result = enrollStudent($courseId, $_SESSION['user_id']);
    if ($result['success']) {
        $message = 'تم انضمامك للكورس بنجاح!';
        $messageKind = 'success';
        $isEnrolled = true;
    } else {
        $message = $result['message'];
        $messageKind = 'error';
    }
}

$studentCount = getCourseStudentCount($courseId);
$pageTitle    = 'الانضمام: ' . htmlspecialchars($course['course_name']) . ' | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<section class="relative min-h-[calc(100vh-80px)] flex items-center justify-center py-16 overflow-hidden auth-bg">
    <span class="blob" style="width:380px;height:380px;background:#60A5FA;top:-80px;right:-80px;"></span>
    <span class="blob" style="width:300px;height:300px;background:#2563EB;bottom:-60px;left:-60px;opacity:.4;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>

    <div class="relative z-10 w-full max-w-lg mx-auto px-5">
        <a href="list.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-600 text-sm font-medium mb-6 transition">
            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i> العودة للكورسات
        </a>

        <div class="glass rounded-[28px] p-8 reveal">
            <!-- Course banner -->
            <div class="h-28 rounded-2xl flex items-center justify-center mb-6 relative overflow-hidden" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <div class="absolute inset-0 grid-dots opacity-20"></div>
                <i data-lucide="book-open" class="relative z-10 text-white" style="width:44px;height:44px;opacity:.9;"></i>
            </div>

            <!-- Badge -->
            <span class="tag-pill text-xs font-bold px-3 py-1 rounded-full"><?php echo htmlspecialchars($course['course_code']); ?></span>

            <h1 class="display font-semibold text-2xl text-slate-900 mt-3 mb-2"><?php echo htmlspecialchars($course['course_name']); ?></h1>

            <!-- Meta -->
            <div class="flex flex-wrap gap-4 text-sm text-slate-500 mb-4">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="user-round" style="width:14px;height:14px;color:#2563EB"></i>
                    <?php echo htmlspecialchars($course['professor_name'] ?? 'غير محدد'); ?>
                </span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="users" style="width:14px;height:14px;color:#16a34a"></i>
                    <?php echo $studentCount; ?> طالب مسجل
                </span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="calendar" style="width:14px;height:14px;color:#F59E0B"></i>
                    <?php echo date('Y-m-d', strtotime($course['created_at'])); ?>
                </span>
            </div>

            <?php if ($course['description']): ?>
            <div class="bg-slate-50 rounded-2xl p-4 mb-5 text-sm text-slate-600 leading-relaxed">
                <?php echo nl2br(htmlspecialchars($course['description'])); ?>
            </div>
            <?php endif; ?>

            <!-- Feedback -->
            <?php if ($message): ?>
            <div class="rounded-xl px-4 py-3 text-sm font-medium mb-5 <?php
                echo $messageKind === 'success' ? 'bg-green-100 text-green-700' :
                    ($messageKind === 'info' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700');
            ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- Action -->
            <?php if ($isEnrolled): ?>
                <a href="view.php?id=<?php echo $courseId; ?>"
                   class="btn-primary-nagah block text-center w-full py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
                    <i data-lucide="arrow-left" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-left:4px;"></i>
                    دخول الكورس
                </a>
            <?php else: ?>
                <form method="POST">
                    <button type="submit" class="btn-primary-nagah w-full py-3.5 rounded-full font-bold shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="user-plus" style="width:17px;height:17px;"></i>
                        انضم للكورس الآن
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
