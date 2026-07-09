<?php
require_once __DIR__ . '/_layout.php';

renderStudentPage('الحضور', function () {
    $attendance = [
        ['date' => '2026-07-08', 'subject' => 'الرياضيات', 'status' => 'present'],
        ['date' => '2026-07-08', 'subject' => 'الفيزياء', 'status' => 'present'],
        ['date' => '2026-07-07', 'subject' => 'الكيمياء', 'status' => 'absent'],
        ['date' => '2026-07-07', 'subject' => 'العربية', 'status' => 'present'],
        ['date' => '2026-07-06', 'subject' => 'الإنجليزية', 'status' => 'present'],
    ];
    ?>
    <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-calendar-check" style="color: var(--primary);"></i> الحضور</h1>
    <p style="color: var(--text-secondary); margin-bottom: 1rem;">نسبة حضورك: <strong style="color: var(--success); font-size: 1.25rem;">92%</strong></p>

    <div class="card glass schedule-table-wrap">
        <table class="schedule-table">
            <thead>
                <tr><th>التاريخ</th><th>المادة</th><th>الحالة</th></tr>
            </thead>
            <tbody>
                <?php foreach ($attendance as $a): ?>
                <tr>
                    <td><?php echo $a['date']; ?></td>
                    <td><?php echo $a['subject']; ?></td>
                    <td>
                        <?php if ($a['status'] === 'present'): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> حاضر</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="fas fa-times"></i> غائب</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
});
