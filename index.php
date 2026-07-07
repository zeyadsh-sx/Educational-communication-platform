<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    session_start();
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/includes/gamification.php';
    require_once __DIR__ . '/includes/header.php';

    $pageTitle = 'الرئيسية | EduFlow';
    $leaderboard = getLeaderboard(5);
} catch (Exception $e) {
    die("<h1>Error Loading Application</h1><p>Error: " . $e->getMessage() . "</p>");
}
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title animate-fade"><?php echo __('hero_title'); ?></h1>
        <p class="hero-subtitle animate-fade" style="animation-delay: 0.1s;"><?php echo __('hero_subtitle'); ?></p>
        
        <div class="hero-btns animate-fade" style="animation-delay: 0.2s; display: flex; gap: 1rem; justify-content: center;">
            <?php if (!isLoggedIn()): ?>
                <a href="<?php echo getBaseUrl(); ?>/auth/login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt"></i> <?php echo __('login'); ?>
                </a>
                <a href="<?php echo getBaseUrl(); ?>/auth/register.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-user-plus"></i> <?php echo __('register'); ?>
                </a>
            <?php else: ?>
                <?php if (isProfessor()): ?>
                    <a href="<?php echo getBaseUrl(); ?>/admin/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt"></i> <?php echo __('dashboard'); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo getBaseUrl(); ?>/student/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt"></i> <?php echo __('dashboard'); ?>
                    </a>
                <?php endif; ?>
                <a href="<?php echo getBaseUrl(); ?>/courses/list.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-book"></i> <?php echo __('browse_courses'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Stats Grid -->
<section class="stats" style="margin-top: -4rem; position: relative; z-index: 10;">
    <div class="container">
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div class="card glass stat-card animate-fade" style="animation-delay: 0.3s;">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value">+1k</div>
                <div class="stat-label"><?php echo getEmoji('users'); ?> <?php echo __('stat_students'); ?></div>
            </div>
            <div class="card glass stat-card animate-fade" style="animation-delay: 0.4s;">
                <div class="stat-icon" style="color: var(--success);"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="stat-value">+50</div>
                <div class="stat-label"><?php echo getEmoji('professor'); ?> <?php echo __('stat_profs'); ?></div>
            </div>
            <div class="card glass stat-card animate-fade" style="animation-delay: 0.5s;">
                <div class="stat-icon" style="color: var(--accent);"><i class="fas fa-book"></i></div>
                <div class="stat-value">+200</div>
                <div class="stat-label"><?php echo getEmoji('courses'); ?> <?php echo __('stat_courses'); ?></div>
            </div>
            <div class="card glass stat-card animate-fade" style="animation-delay: 0.6s;">
                <div class="stat-icon" style="color: var(--info);"><i class="fas fa-file-alt"></i></div>
                <div class="stat-value">+5k</div>
                <div class="stat-label"><?php echo getEmoji('materials'); ?> <?php echo __('stat_materials'); ?></div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="features" style="padding: 6rem 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 4rem; align-items: start;">
            
            <!-- Features Column -->
            <div>
                <h2 style="margin-bottom: 2rem;"><?php echo __('features_title'); ?></h2>
                
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="card glass animate-fade" style="padding: 1.5rem;">
                        <h3 style="display: flex; align-items: center; gap: 1rem; color: var(--primary);">
                            <i class="fas fa-bolt"></i> <?php echo __('prof_features_title'); ?>
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.5rem;">
                            نظام متكامل لإدارة المحاضرات، التواصل مع الطلاب، وتنظيم المواعيد المكتبية بكل سهولة.
                        </p>
                    </div>
                    
                    <div class="card glass animate-fade" style="padding: 1.5rem; animation-delay: 0.1s;">
                        <h3 style="display: flex; align-items: center; gap: 1rem; color: var(--success);">
                            <i class="fas fa-graduation-cap"></i> <?php echo __('student_features_title'); ?>
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.5rem;">
                            بيئة تعليمية تفاعلية تمكنك من الوصول للمواد العلمية، طرح الأسئلة، وحجز المواعيد مع الأساتذة.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Leaderboard Column (Creative Feature) -->
            <div>
                <div class="card glass animate-fade" style="padding: 2rem; border-color: var(--primary);">
                    <h2 style="display: flex; align-items: center; gap: 1rem; font-size: 1.5rem; margin-bottom: 2rem;">
                        <?php echo getEmoji('rank'); ?> لوحة شرف الطلاب
                    </h2>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($leaderboard as $index => $student): ?>
                            <div class="leaderboard-item">
                                <span class="rank"><?php echo getEmoji('rank'); ?> #<?php echo $index + 1; ?></span>
                                <div class="avatar" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                    <?php echo mb_substr($student['full_name'], 0, 1); ?>
                                </div>
                                <div style="flex-grow: 1;">
                                    <div style="font-weight: 700;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo getEmoji('streak'); ?> طالب نشط</div>
                                </div>
                                <div class="badge badge-primary"><?php echo getEmoji('points'); ?> <?php echo $student['points']; ?> نقطة</div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($leaderboard)): ?>
                            <p style="text-align: center; color: var(--text-muted);">لا يوجد طلاب في لوحة الشرف بعد.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: 2rem; text-align: center;">
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                            شارك في الكورسات واطرح الأسئلة لتظهر في لوحة الشرف!
                        </p>
                        <a href="<?php echo getBaseUrl(); ?>/auth/register.php" class="btn btn-outline btn-sm" style="width: 100%;">انضم إلينا الآن</a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
