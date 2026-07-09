<?php
require_once __DIR__ . '/_layout.php';

renderStudentPage('الدروس المسجلة', function () {
    $videos = [
        ['title' => 'المعادلات التربيعية', 'course' => 'الرياضيات', 'duration' => '45:30', 'watched' => true],
        ['title' => 'قوانين نيوتن', 'course' => 'الفيزياء', 'duration' => '38:15', 'watched' => true],
        ['title' => 'الكيمياء العضوية - الألكانات', 'course' => 'الكيمياء', 'duration' => '52:00', 'watched' => false],
        ['title' => 'قواعد اللغة - النحو', 'course' => 'العربية', 'duration' => '41:20', 'watched' => false],
    ];
    ?>
    <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-play-circle" style="color: var(--primary);"></i> الدروس المسجلة</h1>
    <p style="color: var(--text-secondary); margin-bottom: 2rem;">شاهد الدروس في أي وقت</p>

    <div class="features-grid">
        <?php foreach ($videos as $video): ?>
        <div class="card glass course-card-ma">
            <div class="course-card-image" style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); height: 120px;">
                <i class="fas fa-play-circle" style="font-size: 2.5rem;"></i>
            </div>
            <div class="course-card-body">
                <h3 style="font-size: 1rem;"><?php echo $video['title']; ?></h3>
                <div class="course-card-meta">
                    <span><i class="fas fa-book"></i> <?php echo $video['course']; ?></span>
                    <span><i class="fas fa-clock"></i> <?php echo $video['duration']; ?></span>
                </div>
                <?php if ($video['watched']): ?>
                    <span class="badge badge-success"><i class="fas fa-check"></i> مكتمل</span>
                <?php else: ?>
                    <a href="#" class="btn btn-primary btn-sm btn-block" style="margin-top: 0.75rem;"><i class="fas fa-play"></i> مشاهدة</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
});
