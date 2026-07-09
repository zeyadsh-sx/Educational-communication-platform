<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/academy_data.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

$search     = trim($_GET['search'] ?? '');
$userType   = $_SESSION['user_type'] ?? null;
$userId     = $_SESSION['user_id'] ?? null;
$isLoggedIn = isLoggedIn();
$base       = nagahBaseUrl();

if ($isLoggedIn && $userType === 'professor') {
    $courses = getCourses($userId, $search);
} else {
    $courses = getCourses(null, $search);
}

$staticCourses = getAcademyCourses();
$pageTitle = 'الكورسات | أكاديمية ماستر';

require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<!-- Hero -->
<section class="relative w-full overflow-hidden py-16 sm:py-20">
    <span class="blob" style="width:360px;height:360px;background:#60A5FA;top:-100px;right:-80px;"></span>
    <span class="blob" style="width:300px;height:300px;background:#2563EB;bottom:-80px;left:-60px;opacity:.35;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-5 text-center">
        <span class="reveal tag-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide" style="animation-delay:.05s">
            <i data-lucide="book-open" style="width:13px;height:13px;"></i>
            المواد الدراسية
        </span>
        <h1 class="display reveal mt-4 text-3xl sm:text-4xl lg:text-5xl font-semibold text-slate-900 leading-tight" style="animation-delay:.15s">
            <?php if ($isLoggedIn && $userType === 'professor'): ?>كورساتي<?php else: ?>استكشف <span style="color:#2563EB">الكورسات</span><?php endif; ?>
        </h1>
        <p class="reveal mt-4 text-slate-500 max-w-xl mx-auto" style="animation-delay:.25s">
            مناهج شاملة للثانوية العامة والبكالوريا — ابحث وانضم للكورس المناسب لك
        </p>

        <!-- Search bar -->
        <div class="reveal mt-8 max-w-lg mx-auto" style="animation-delay:.35s">
            <form method="GET" class="flex gap-2">
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" style="width:18px;height:18px;right:14px;"></i>
                    <input type="text" name="search"
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="ابحث عن كورس أو مادة..."
                           class="field-input w-full pr-10" style="border-radius:999px;">
                </div>
                <button type="submit" class="btn-primary-nagah px-5 py-2.5 rounded-full font-bold shadow-lg">بحث</button>
            </form>
        </div>
    </div>
</section>

<main class="max-w-7xl mx-auto px-5 pb-20">

<?php if (!empty($courses)): ?>
<!-- Platform Courses -->
<div class="mb-12">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="database" style="width:16px;height:16px;"></i>
            </span>
            <h2 class="display font-semibold text-xl text-slate-900">كورسات المنصة</h2>
        </div>
        <?php if ($isLoggedIn && $userType === 'professor'): ?>
            <a href="create.php" class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all">
                <i data-lucide="plus" style="width:16px;height:16px;"></i> كورس جديد
            </a>
        <?php elseif (!$isLoggedIn): ?>
            <a href="<?php echo $base; ?>/auth/register.php" class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all">
                <i data-lucide="user-plus" style="width:16px;height:16px;"></i> سجّل للانضمام
            </a>
        <?php endif; ?>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($courses as $course):
            $studentCount = getCourseStudentCount($course['id']);
            $isEnrolled   = ($userType === 'student') ? isStudentEnrolled($course['id'], $userId) : false;
        ?>
        <article class="glass rounded-3xl overflow-hidden lift flex flex-col">
            <div class="h-28 flex items-center justify-center text-white relative" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="book" style="width:40px;height:40px;opacity:.8;"></i>
                <?php if ($isEnrolled): ?>
                    <span class="absolute top-3 left-3 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">مسجّل</span>
                <?php endif; ?>
            </div>
            <div class="p-5 flex flex-col flex-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="tag-pill text-xs font-bold px-3 py-1 rounded-full"><?php echo htmlspecialchars($course['course_code']); ?></span>
                </div>
                <h3 class="font-bold text-slate-900 text-base leading-snug mb-1"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                <?php if ($course['description']): ?>
                    <p class="text-xs text-slate-500 leading-relaxed mb-3 flex-1"><?php echo htmlspecialchars(mb_substr($course['description'], 0, 90)); ?>…</p>
                <?php else: ?>
                    <div class="flex-1"></div>
                <?php endif; ?>
                <div class="flex items-center justify-between text-xs text-slate-500 mb-4 pt-3 border-t border-slate-100">
                    <span class="flex items-center gap-1"><i data-lucide="user-round" style="width:13px;height:13px;"></i> <?php echo htmlspecialchars($course['professor_name'] ?? 'غير محدد'); ?></span>
                    <span class="flex items-center gap-1"><i data-lucide="users" style="width:13px;height:13px;"></i> <?php echo $studentCount; ?> طالب</span>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <?php if ($isLoggedIn): ?>
                        <a href="view.php?id=<?php echo $course['id']; ?>" class="btn-primary-nagah flex-1 text-center py-2 rounded-xl text-sm font-bold">عرض الكورس</a>
                        <?php if ($userType === 'student' && !$isEnrolled): ?>
                            <a href="join.php?id=<?php echo $course['id']; ?>" class="px-4 py-2 rounded-xl text-sm font-bold border-2 border-blue-600 text-blue-600 hover:bg-blue-50 transition">انضم</a>
                        <?php endif; ?>
                        <?php if ($userType === 'professor' && $course['professor_id'] == $userId): ?>
                            <a href="manage.php?id=<?php echo $course['id']; ?>" class="px-4 py-2 rounded-xl text-sm font-bold border-2 border-slate-300 text-slate-600 hover:bg-slate-50 transition flex items-center gap-1">
                                <i data-lucide="settings" style="width:13px;height:13px;"></i> إدارة
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo $base; ?>/auth/login.php" class="btn-primary-nagah flex-1 text-center py-2 rounded-xl text-sm font-bold">سجّل للوصول</a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Static Academy Courses -->
<?php foreach ($staticCourses as $group): ?>
<div class="mb-12">
    <div class="flex items-center gap-3 mb-6">
        <span class="w-8 h-8 rounded-xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#F59E0B,#fbbf24)">
            <i data-lucide="graduation-cap" style="width:16px;height:16px;"></i>
        </span>
        <h2 class="display font-semibold text-xl text-slate-900"><?php echo $group['title']; ?></h2>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($group['courses'] as $course): ?>
        <article class="glass rounded-3xl overflow-hidden lift flex flex-col">
            <div class="h-28 flex items-center justify-center text-white relative" style="background:<?php echo $course['color']; ?>22;">
                <span class="w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg" style="background:<?php echo $course['color']; ?>">
                    <i class="fas <?php echo $course['icon']; ?>" style="font-size:1.5rem;"></i>
                </span>
            </div>
            <div class="p-5 flex flex-col flex-1">
                <h3 class="font-bold text-slate-900 text-base leading-snug mb-1"><?php echo $course['name']; ?></h3>
                <p class="text-xs text-slate-500 leading-relaxed mb-3 flex-1"><?php echo $course['desc']; ?></p>
                <div class="flex items-center justify-between text-xs text-slate-500 mb-4 pt-3 border-t border-slate-100">
                    <span class="flex items-center gap-1"><i data-lucide="user-round" style="width:13px;height:13px;"></i> <?php echo $course['teacher']; ?></span>
                    <span class="flex items-center gap-1"><i data-lucide="play-circle" style="width:13px;height:13px;"></i> <?php echo $course['lessons']; ?> درس</span>
                </div>
                <a href="<?php echo $base; ?>/auth/register.php" class="btn-primary-nagah block text-center py-2.5 rounded-xl text-sm font-bold">
                    <i data-lucide="user-plus" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-left:4px;"></i> سجّل الآن
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($courses) && empty($staticCourses)): ?>
<div class="glass rounded-3xl p-16 text-center">
    <i data-lucide="search-x" class="mx-auto text-slate-300 mb-4" style="width:56px;height:56px;"></i>
    <h3 class="font-bold text-slate-500 text-lg">لا توجد نتائج</h3>
    <p class="text-slate-400 text-sm mt-2">جرّب كلمة بحث أخرى</p>
</div>
<?php endif; ?>

</main>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
