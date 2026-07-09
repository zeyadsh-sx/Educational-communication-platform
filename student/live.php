<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../includes/academy_data.php';

renderStudentPage('الحصص المباشرة', function () {
    $liveSessions = [
        ['subject' => 'الرياضيات', 'teacher' => 'أ. محمد حسن', 'time' => '2026-07-10 16:00', 'status' => 'upcoming'],
        ['subject' => 'الفيزياء', 'teacher' => 'أ. أحمد سالم', 'time' => '2026-07-09 14:00', 'status' => 'live'],
        ['subject' => 'الكيمياء', 'teacher' => 'د. سارة محمود', 'time' => '2026-07-08 10:00', 'status' => 'ended'],
    ];
    ?>
    <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-broadcast-tower" style="color: var(--primary);"></i> الحصص المباشرة</h1>
    <p style="color: var(--text-secondary); margin-bottom: 2rem;">انضم للحصص المباشرة مع معلميك</p>

    <?php foreach ($liveSessions as $session): ?>
    <div class="card glass" style="padding: 1.5rem; margin-bottom: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3><?php echo $session['subject']; ?></h3>
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                    <i class="fas fa-user-tie"></i> <?php echo $session['teacher']; ?>
                    &nbsp;|&nbsp;
                    <i class="fas fa-calendar"></i> <?php echo $session['time']; ?>
                </p>
            </div>
            <?php if ($session['status'] === 'live'): ?>
                <a href="#" class="btn btn-accent btn-sm"><i class="fas fa-video"></i> انضم الآن</a>
            <?php elseif ($session['status'] === 'upcoming'): ?>
                <span class="badge badge-primary">قادمة</span>
            <?php else: ?>
                <span class="badge badge-outline">انتهت</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php
});
