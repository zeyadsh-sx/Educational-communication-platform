<?php
// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    session_start();
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/includes/header.php';

    $pageTitle = 'الرئيسية';
} catch (Exception $e) {
    die("<h1>Error Loading Application</h1><p>Error: " . $e->getMessage() . "</p><p>File: " . $e->getFile() . ":" . $e->getLine() . "</p>");
}
?>

<div class="hero-section">
    <div class="hero-content">
        <h1><?php echo __('hero_title'); ?></h1>
        <p><?php echo __('hero_subtitle'); ?></p>
        
        <?php if (!isLoggedIn()): ?>
            <div class="hero-buttons">
                <a href="/auth/login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt"></i> <?php echo __('login'); ?>
                </a>
                <a href="/auth/register.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-user-plus"></i> <?php echo __('register'); ?>
                </a>
            </div>
        <?php else: ?>
            <div class="hero-buttons">
                <?php if (isProfessor()): ?>
                    <a href="/admin/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt"></i> <?php echo __('dashboard'); ?>
                    </a>
                <?php else: ?>
                    <a href="/student/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt"></i> <?php echo __('dashboard'); ?>
                    </a>
                <?php endif; ?>
                <a href="/courses/list.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-book"></i> <?php echo __('browse_courses'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="features-section">
    <div class="container">
        <h2 class="section-title"><?php echo __('features_title'); ?></h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3><?php echo __('prof_features_title'); ?></h3>
                <ul>
                    <li><?php echo __('prof_feature_1'); ?></li>
                    <li><?php echo __('prof_feature_2'); ?></li>
                    <li><?php echo __('prof_feature_3'); ?></li>
                    <li><?php echo __('prof_feature_4'); ?></li>
                </ul>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3><?php echo __('student_features_title'); ?></h3>
                <ul>
                    <li><?php echo __('student_feature_1'); ?></li>
                    <li><?php echo __('student_feature_2'); ?></li>
                    <li><?php echo __('student_feature_3'); ?></li>
                    <li><?php echo __('student_feature_4'); ?></li>
                </ul>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3><?php echo __('security_features_title'); ?></h3>
                <ul>
                    <li><?php echo __('security_feature_1'); ?></li>
                    <li><?php echo __('security_feature_2'); ?></li>
                    <li><?php echo __('security_feature_3'); ?></li>
                    <li><?php echo __('security_feature_4'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <i class="fas fa-users"></i>
                <h3>+1000</h3>
                <p><?php echo __('stat_students'); ?></p>
            </div>
            <div class="stat-item">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>+50</h3>
                <p><?php echo __('stat_profs'); ?></p>
            </div>
            <div class="stat-item">
                <i class="fas fa-book"></i>
                <h3>+200</h3>
                <p><?php echo __('stat_courses'); ?></p>
            </div>
            <div class="stat-item">
                <i class="fas fa-file-alt"></i>
                <h3>+5000</h3>
                <p><?php echo __('stat_materials'); ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
    color: white;
    padding: 120px 20px;
    text-align: center;
    animation: fadeIn 1s ease-out;
}

.hero-content h1 {
    font-size: 3.5rem;
    margin-bottom: 25px;
    font-weight: 800;
    text-shadow: 0 4px 20px rgba(0,0,0,0.2);
    animation: slideIn 0.8s ease-out;
}

.hero-content p {
    font-size: 1.6rem;
    margin-bottom: 50px;
    opacity: 0.95;
    animation: fadeIn 1s ease-out 0.2s both;
}

.hero-buttons {
    display: flex;
    gap: 25px;
    justify-content: center;
    flex-wrap: wrap;
    animation: fadeIn 1s ease-out 0.4s both;
}

.features-section {
    padding: 100px 20px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.section-title {
    text-align: center;
    font-size: 2.8rem;
    color: #2c3e50;
    margin-bottom: 60px;
    font-weight: 800;
    position: relative;
    display: inline-block;
    width: 100%;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 40px;
    max-width: 1200px;
    margin: 0 auto;
}

.feature-card {
    background: white;
    padding: 50px 35px;
    border-radius: 20px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.1);
    text-align: center;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid transparent;
    animation: scaleIn 0.6s ease-out;
}

.feature-card:nth-child(1) { animation-delay: 0.1s; }
.feature-card:nth-child(2) { animation-delay: 0.2s; }
.feature-card:nth-child(3) { animation-delay: 0.3s; }

.feature-card:hover {
    transform: translateY(-15px) scale(1.05);
    box-shadow: 0 25px 80px rgba(102, 126, 234, 0.3);
    border-color: #667eea;
}

.feature-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    transition: all 0.4s;
}

.feature-card:hover .feature-icon {
    animation: bounce 0.6s ease;
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
}

.feature-icon i {
    font-size: 2.5rem;
    color: white;
}

.feature-card h3 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.8rem;
    font-weight: 700;
    transition: color 0.3s;
}

.feature-card:hover h3 {
    color: #667eea;
}

.feature-card ul {
    list-style: none;
    padding: 0;
    text-align: right;
}

.feature-card ul li {
    padding: 12px 0;
    color: #7f8c8d;
    font-size: 1.05rem;
    transition: all 0.3s;
}

.feature-card ul li:hover {
    color: #667eea;
    transform: translateX(-10px);
}

.feature-card ul li::before {
    content: "✓";
    color: #27ae60;
    margin-left: 12px;
    font-weight: bold;
    font-size: 1.2rem;
}

.stats-section {
    padding: 100px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    position: relative;
    overflow: hidden;
}

.stats-section::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 40px;
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
    position: relative;
    z-index: 1;
}

.stat-item {
    animation: scaleIn 0.6s ease-out;
}

.stat-item:nth-child(1) { animation-delay: 0.1s; }
.stat-item:nth-child(2) { animation-delay: 0.2s; }
.stat-item:nth-child(3) { animation-delay: 0.3s; }
.stat-item:nth-child(4) { animation-delay: 0.4s; }

.stat-item i {
    font-size: 3.5rem;
    margin-bottom: 20px;
    opacity: 0.9;
    display: block;
    transition: all 0.3s;
}

.stat-item:hover i {
    transform: scale(1.2);
    animation: bounce 0.6s ease;
}

.stat-item h3 {
    font-size: 3rem;
    margin-bottom: 12px;
    font-weight: 800;
    text-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.stat-item p {
    font-size: 1.2rem;
    opacity: 0.95;
    font-weight: 500;
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2.2rem;
    }
    
    .hero-content p {
        font-size: 1.3rem;
    }
    
    .section-title {
        font-size: 2.2rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
