<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/academy_data.php';
require_once __DIR__ . '/../includes/header.php';

$search = trim($_GET['search'] ?? '');
$userType = $_SESSION['user_type'] ?? null;
$userId = $_SESSION['user_id'] ?? null;
$isLoggedIn = isLoggedIn();

if ($isLoggedIn && $userType === 'professor') {
    $courses = getCourses($userId, $search);
} else {
    $courses = getCourses(null, $search);
}

$pageTitle = 'الكورسات | أكاديمية ماستر';
$staticCourses = getAcademyCourses();
?>

<section class="section-padding" style="padding-top: 2rem;">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">الكورسات</span>
            <h2><?php echo $isLoggedIn && $userType === 'professor' ? 'كورساتي' : 'الكورسات المتاحة'; ?></h2>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
            <form method="GET" style="flex: 1; max-width: 400px;">
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           class="form-control" placeholder="ابحث عن كورس...">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <?php if ($isLoggedIn && $userType === 'professor'): ?>
                <a href="create.php" class="btn btn-accent"><i class="fas fa-plus"></i> كورس جديد</a>
            <?php elseif (!$isLoggedIn): ?>
                <a href="<?php echo getBaseUrl(); ?>/auth/register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> سجّل للانضمام</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($courses)): ?>
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);"><i class="fas fa-database"></i> كورسات المنصة</h3>
        <div class="features-grid" style="margin-bottom: 3rem;">
            <?php foreach ($courses as $course):
                $studentCount = getCourseStudentCount($course['id']);
                $isEnrolled = ($userType === 'student') ? isStudentEnrolled($course['id'], $userId) : false;
            ?>
            <div class="card glass course-card-ma">
                <div class="course-card-image" style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); height: 100px;">
                    <i class="fas fa-book" style="font-size: 2rem;"></i>
                </div>
                <div class="course-card-body">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <span class="badge badge-primary"><?php echo htmlspecialchars($course['course_code']); ?></span>
                        <?php if ($isEnrolled): ?><span class="badge badge-success">مسجّل</span><?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                    <?php if ($course['description']): ?>
                        <p style="font-size: 0.85rem;"><?php echo htmlspecialchars(mb_substr($course['description'], 0, 100)); ?>...</p>
                    <?php endif; ?>
                    <div class="course-card-meta">
                        <span><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($course['professor_name'] ?? 'غير محدد'); ?></span>
                        <span><i class="fas fa-users"></i> <?php echo $studentCount; ?> طالب</span>
                    </div>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <?php if ($isLoggedIn): ?>
                            <a href="view.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">عرض التفاصيل</a>
                            <?php if ($userType === 'student' && !$isEnrolled): ?>
                                <a href="join.php?id=<?php echo $course['id']; ?>" class="btn btn-accent btn-sm">انضم</a>
                            <?php endif; ?>
                            <?php if ($userType === 'professor' && $course['professor_id'] == $userId): ?>
                                <a href="manage.php?id=<?php echo $course['id']; ?>" class="btn btn-outline btn-sm">إدارة</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo getBaseUrl(); ?>/auth/login.php" class="btn btn-primary btn-sm">سجّل للوصول</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php foreach ($staticCourses as $key => $group): ?>
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);"><i class="fas fa-graduation-cap"></i> <?php echo $group['title']; ?></h3>
        <div class="features-grid" style="margin-bottom: 2.5rem;">
            <?php foreach ($group['courses'] as $course): ?>
            <div class="card glass course-card-ma">
                <div class="course-card-image" style="background: linear-gradient(135deg, <?php echo $course['color']; ?>, <?php echo $course['color']; ?>99); height: 100px;">
                    <i class="fas <?php echo $course['icon']; ?>" style="font-size: 2rem;"></i>
                </div>
                <div class="course-card-body">
                    <h3><?php echo $course['name']; ?></h3>
                    <p style="font-size: 0.85rem;"><?php echo $course['desc']; ?></p>
                    <div class="course-card-meta">
                        <span><i class="fas fa-user-tie"></i> <?php echo $course['teacher']; ?></span>
                        <span><i class="fas fa-play-circle"></i> <?php echo $course['lessons']; ?> درس</span>
                    </div>
                    <a href="<?php echo getBaseUrl(); ?>/auth/register.php" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-user-plus"></i> <?php echo __('enroll'); ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
