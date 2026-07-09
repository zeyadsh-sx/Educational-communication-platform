<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../includes/academy_data.php';

renderStudentPage('الواجبات', function () {
    $homework = getSampleHomework();
    $statusLabels = ['pending' => 'معلق', 'submitted' => 'مُسلّم', 'graded' => 'مُقيّم'];
    $statusClasses = ['pending' => 'badge-warning', 'submitted' => 'badge-primary', 'graded' => 'badge-success'];
    ?>
    <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-tasks" style="color: var(--primary);"></i> الواجبات</h1>
    <p style="color: var(--text-secondary); margin-bottom: 2rem;">تابع واجباتك وارفع حلولك</p>

    <?php foreach ($homework as $hw): ?>
    <div class="card glass" style="padding: 1.5rem; margin-bottom: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div style="flex: 1;">
                <h3 style="margin-bottom: 0.5rem;"><?php echo $hw['title']; ?></h3>
                <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
                    <i class="fas fa-book"></i> <?php echo $hw['course']; ?>
                    &nbsp;|&nbsp;
                    <i class="fas fa-calendar"></i> موعد التسليم: <?php echo $hw['due']; ?>
                </div>
                <span class="badge <?php echo $statusClasses[$hw['status']]; ?>"><?php echo $statusLabels[$hw['status']]; ?></span>
                <?php if ($hw['grade'] !== null): ?>
                    <strong style="margin-right: 0.75rem; color: var(--primary);">الدرجة: <?php echo $hw['grade']; ?>/20</strong>
                <?php endif; ?>
                <?php if (!empty($hw['feedback'])): ?>
                    <div style="margin-top: 0.75rem; padding: 0.75rem; background: rgba(37,99,235,0.05); border-radius: var(--radius-md); font-size: 0.9rem;">
                        <strong>ملاحظات المعلم:</strong> <?php echo $hw['feedback']; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($hw['status'] === 'pending'): ?>
            <form enctype="multipart/form-data" style="min-width: 200px;">
                <input type="file" class="form-control" style="margin-bottom: 0.5rem; font-size: 0.85rem;">
                <button type="button" class="btn btn-primary btn-sm btn-block" onclick="alert('تم رفع الواجب بنجاح!')">
                    <i class="fas fa-upload"></i> رفع الحل
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php
});
