<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../includes/academy_data.php';

renderStudentPage('الامتحانات', function () {
    $exams = getSampleExams();
    ?>
    <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-file-alt" style="color: var(--primary);"></i> الامتحانات</h1>
    <p style="color: var(--text-secondary); margin-bottom: 2rem;">اختبر معلوماتك وراجع إجاباتك</p>

    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <?php foreach ($exams as $exam): ?>
        <div class="card glass" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h3 style="margin-bottom: 0.5rem;"><?php echo $exam['title']; ?></h3>
                    <div style="font-size: 0.9rem; color: var(--text-secondary);">
                        <span><i class="fas fa-book"></i> <?php echo $exam['course']; ?></span>
                        &nbsp;|&nbsp;
                        <span><i class="fas fa-question-circle"></i> <?php echo $exam['questions']; ?> سؤال</span>
                        &nbsp;|&nbsp;
                        <span><i class="fas fa-clock"></i> <?php echo $exam['duration']; ?> دقيقة</span>
                    </div>
                    <?php if ($exam['status'] === 'completed'): ?>
                        <div style="margin-top: 0.75rem;">
                            <span class="badge badge-success">مكتمل</span>
                            <strong style="color: var(--primary); margin-right: 0.5rem;">الدرجة: <?php echo $exam['score']; ?>%</strong>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <?php if ($exam['status'] === 'available'): ?>
                        <a href="#" class="btn btn-primary btn-sm" onclick="alert('سيبدأ الامتحان قريباً!'); return false;">
                            <i class="fas fa-play"></i> ابدأ الامتحان
                        </a>
                    <?php else: ?>
                        <a href="#" class="btn btn-outline btn-sm" onclick="alert('مراجعة الإجابات'); return false;">
                            <i class="fas fa-eye"></i> مراجعة الإجابات
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
});
