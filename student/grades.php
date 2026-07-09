<?php
require_once __DIR__ . '/_layout.php';

renderStudentPage('الدرجات', function () {
    $grades = [
        ['course' => 'الرياضيات', 'exam1' => 18, 'exam2' => 17, 'homework' => 19, 'total' => 88],
        ['course' => 'الفيزياء', 'exam1' => 16, 'exam2' => 15, 'homework' => 18, 'total' => 82],
        ['course' => 'الكيمياء', 'exam1' => 19, 'exam2' => 18, 'homework' => 20, 'total' => 92],
        ['course' => 'اللغة الإنجليزية', 'exam1' => 17, 'exam2' => 16, 'homework' => 17, 'total' => 85],
    ];
    ?>
    <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-chart-bar" style="color: var(--primary);"></i> الدرجات</h1>
    <p style="color: var(--text-secondary); margin-bottom: 2rem;">تابع درجاتك في جميع المواد</p>

    <div class="card glass schedule-table-wrap">
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>المادة</th>
                    <th>امتحان 1</th>
                    <th>امتحان 2</th>
                    <th>الواجبات</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $g): ?>
                <tr>
                    <td><strong><?php echo $g['course']; ?></strong></td>
                    <td><?php echo $g['exam1']; ?>/20</td>
                    <td><?php echo $g['exam2']; ?>/20</td>
                    <td><?php echo $g['homework']; ?>/20</td>
                    <td><span class="badge badge-primary"><?php echo $g['total']; ?>%</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
});
