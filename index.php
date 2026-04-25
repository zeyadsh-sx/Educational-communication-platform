<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'الرئيسية';
?>

<div class="hero-section">
    <div class="hero-content">
        <h1>منصة الاتصال التعليمي</h1>
        <p>منصة متكاملة لتسهيل التواصل بين الطلاب وأساتذتهم</p>
        
        <?php if (!isLoggedIn()): ?>
            <div class="hero-buttons">
                <a href="/auth/login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                </a>
                <a href="/auth/register.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-user-plus"></i> إنشاء حساب
                </a>
            </div>
        <?php else: ?>
            <div class="hero-buttons">
                <?php if (isProfessor()): ?>
                    <a href="/admin/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                    </a>
                <?php else: ?>
                    <a href="/student/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                    </a>
                <?php endif; ?>
                <a href="/courses/list.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-book"></i> تصفح الكورسات
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="features-section">
    <div class="container">
        <h2 class="section-title">مميزات المنصة</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h3>للأساتذة</h3>
                <ul>
                    <li>رفع المحاضرات والمواد الدراسية</li>
                    <li>إدارة الساعات المكتبية</li>
                    <li>الرد على أسئلة الطلاب</li>
                    <li>نشر الإعلانات</li>
                </ul>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3>للطلاب</h3>
                <ul>
                    <li>الوصول للمواد الدراسية</li>
                    <li>طرح الأسئلة والحصول على إجابات</li>
                    <li>حجز المواعيد المكتبية</li>
                    <li>متابعة الإعلانات</li>
                </ul>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>أمان عالي</h3>
                <ul>
                    <li>تشفير البيانات</li>
                    <li>حماية الملفات</li>
                    <li>إدارة صلاحيات</li>
                    <li>نسخ احتياطية</li>
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
                <p>طالب وطالبة</p>
            </div>
            <div class="stat-item">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>+50</h3>
                <p>دكتور ودكتورة</p>
            </div>
            <div class="stat-item">
                <i class="fas fa-book"></i>
                <h3>+200</h3>
                <p>كورس دراسي</p>
            </div>
            <div class="stat-item">
                <i class="fas fa-file-alt"></i>
                <h3>+5000</h3>
                <p>مادة دراسية</p>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 100px 20px;
    text-align: center;
}

.hero-content h1 {
    font-size: 3rem;
    margin-bottom: 20px;
    font-weight: 700;
}

.hero-content p {
    font-size: 1.5rem;
    margin-bottom: 40px;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-lg {
    padding: 15px 40px;
    font-size: 1.1rem;
}

.features-section {
    padding: 80px 20px;
    background: #f8f9fa;
}

.section-title {
    text-align: center;
    font-size: 2.5rem;
    color: #2c3e50;
    margin-bottom: 50px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s;
}

.feature-card:hover {
    transform: translateY(-10px);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.feature-icon i {
    font-size: 2rem;
    color: white;
}

.feature-card h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.5rem;
}

.feature-card ul {
    list-style: none;
    padding: 0;
    text-align: right;
}

.feature-card ul li {
    padding: 8px 0;
    color: #7f8c8d;
}

.feature-card ul li::before {
    content: "✓";
    color: #27ae60;
    margin-left: 10px;
    font-weight: bold;
}

.stats-section {
    padding: 80px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
}

.stat-item i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.9;
}

.stat-item h3 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.stat-item p {
    font-size: 1.1rem;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .hero-content p {
        font-size: 1.2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
