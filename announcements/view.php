<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
    exit;
}

$courseId = (int)($_GET['course_id'] ?? 0);
$base     = nagahBaseUrl();
$pdo      = getDB();

$announcements = [];
if ($courseId) {
    $stmt = $pdo->prepare("SELECT a.*, u.full_name as author FROM announcements a LEFT JOIN users u ON a.created_by = u.id WHERE a.course_id = ? ORDER BY a.created_at DESC");
    $stmt->execute([$courseId]);
    $announcements = $stmt->fetchAll();
} else {
    // All announcements for the logged-in user's courses
    $userId   = $_SESSION['user_id'];
    $userType = $_SESSION['user_type'];
    if ($userType === 'professor') {
        $stmt = $pdo->prepare("SELECT a.*, u.full_name as author, c.course_name FROM announcements a LEFT JOIN users u ON a.created_by = u.id LEFT JOIN courses c ON a.course_id = c.id WHERE c.professor_id = ? ORDER BY a.created_at DESC");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("SELECT a.*, u.full_name as author, c.course_name FROM announcements a LEFT JOIN users u ON a.created_by = u.id LEFT JOIN courses c ON a.course_id = c.id JOIN course_enrollments ce ON ce.course_id = c.id AND ce.student_id = ? ORDER BY a.created_at DESC");
        $stmt->execute([$userId]);
    }
    $announcements = $stmt->fetchAll();
}

$priorityConfig = [
    'high'   => ['label'=>'عاجل',   'bg'=>'bg-red-50',   'border'=>'border-r-4 border-red-500',  'badge'=>'bg-red-100 text-red-700'],
    'medium' => ['label'=>'متوسط',  'bg'=>'bg-blue-50',  'border'=>'border-r-4 border-blue-500', 'badge'=>'bg-blue-100 text-blue-700'],
    'low'    => ['label'=>'عادي',   'bg'=>'bg-slate-50', 'border'=>'border-r-4 border-slate-300','badge'=>'bg-slate-100 text-slate-600'],
];

$pageTitle = 'الإعلانات | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<!-- Header -->
<section class="relative overflow-hidden py-12 sm:py-14" style="background:linear-gradient(135deg,#1e3a8a,#2563EB,#3b82f6)">
    <div class="absolute inset-0 grid-dots opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-5">
        <?php if ($courseId): ?>
        <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $courseId; ?>" class="inline-flex items-center gap-2 text-white/80 hover:text-white text-sm font-medium mb-4 transition">
            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i> العودة للكورس
        </a>
        <?php endif; ?>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="display font-semibold text-3xl text-white">الإعلانات</h1>
                <p class="text-white/70 mt-1"><?php echo count($announcements); ?> إعلان</p>
            </div>
            <?php if (isProfessor() && $courseId): ?>
            <a href="create.php?course_id=<?php echo $courseId; ?>" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 backdrop-blur text-white px-5 py-2.5 rounded-full text-sm font-bold transition">
                <i data-lucide="plus" style="width:16px;height:16px;"></i> إعلان جديد
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<main class="max-w-4xl mx-auto px-5 py-10 pb-20">

    <?php if (empty($announcements)): ?>
    <div class="glass rounded-3xl p-16 text-center">
        <i data-lucide="megaphone" class="mx-auto text-slate-300 mb-4" style="width:56px;height:56px;"></i>
        <h3 class="font-bold text-slate-500 text-lg">لا توجد إعلانات حتى الآن</h3>
        <p class="text-slate-400 text-sm mt-2">ستظهر هنا الإعلانات الجديدة من المعلمين</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($announcements as $ann):
            $p  = $ann['priority'] ?? 'low';
            $pc = $priorityConfig[$p] ?? $priorityConfig['low'];
            $timeAgo = date('d/m/Y H:i', strtotime($ann['created_at']));
        ?>
        <article class="glass rounded-2xl p-6 <?php echo $pc['border']; ?> reveal">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white shrink-0" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                        <i data-lucide="megaphone" style="width:16px;height:16px;"></i>
                    </span>
                    <div class="min-w-0">
                        <h3 class="font-bold text-slate-800 truncate"><?php echo htmlspecialchars($ann['title']); ?></h3>
                        <?php if (!empty($ann['course_name'])): ?>
                            <p class="text-xs text-slate-400"><?php echo htmlspecialchars($ann['course_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full <?php echo $pc['badge']; ?>"><?php echo $pc['label']; ?></span>
                    <span class="text-xs text-slate-400 hidden sm:block"><?php echo $timeAgo; ?></span>
                </div>
            </div>
            <p class="text-slate-600 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($ann['content'])); ?></p>
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <i data-lucide="user-round" style="width:12px;height:12px;"></i>
                    <?php echo htmlspecialchars($ann['author'] ?? 'المعلم'); ?>
                </span>
                <span class="text-xs text-slate-400 flex items-center gap-1 sm:hidden">
                    <i data-lucide="clock" style="width:12px;height:12px;"></i>
                    <?php echo $timeAgo; ?>
                </span>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
