<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

requireRole('admin');

$base = nagahBaseUrl();
$pdo  = getDB();

// ── Stats ──────────────────────────────────────────────────────────────────
$totalStudents    = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='student'")->fetchColumn();
$totalProfessors  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='professor'")->fetchColumn();
$totalCourses     = (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalEnrollments = (int)$pdo->query("SELECT COUNT(*) FROM course_enrollments WHERE status='active'")->fetchColumn();
$totalMaterials   = (int)$pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$totalQuestions   = (int)$pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$totalAppointments= (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$avgEnrollPerCourse = $totalCourses > 0 ? round($totalEnrollments / $totalCourses, 1) : 0;

// ── Monthly registrations (last 6 months) ─────────────────────────────────
$monthlyData = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
           SUM(user_type='student')   AS students,
           SUM(user_type='professor') AS professors
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
")->fetchAll();

// ── Top 8 courses by enrollment ───────────────────────────────────────────
$topCourses = $pdo->query("
    SELECT c.course_name, c.course_code, u.full_name AS prof,
           COUNT(ce.student_id) AS cnt
    FROM courses c
    LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.status='active'
    LEFT JOIN users u ON c.professor_id = u.id
    GROUP BY c.id
    ORDER BY cnt DESC
    LIMIT 8
")->fetchAll();

// ── Questions by status ───────────────────────────────────────────────────
$qPending   = (int)$pdo->query("SELECT COUNT(*) FROM questions WHERE status='pending'")->fetchColumn();
$qAnswered  = (int)$pdo->query("SELECT COUNT(*) FROM questions WHERE status='answered'")->fetchColumn();

// ── Subscriptions summary ─────────────────────────────────────────────────
$subActive  = 0; $subPending = 0; $totalRevenue = 0;
try {
    $subActive  = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='active'")->fetchColumn();
    $subPending = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='pending'")->fetchColumn();
    $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payment_receipts WHERE status='approved'")->fetchColumn();
} catch (Exception $e) {}

$_activeSidebar = 'analytics';
$pageTitle = 'الإحصائيات | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>
<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_admin.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-6xl">

    <div class="mb-7">
        <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
            <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
                  style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                <i data-lucide="bar-chart-2" style="width:17px;height:17px;"></i>
            </span>
            الإحصائيات والتحليلات
        </h1>
        <p class="text-slate-500 mt-1 text-sm">نظرة شاملة على أداء المنصة</p>
    </div>

    <!-- KPI cards row 1 -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <?php foreach ([
            [$totalStudents,   'طلاب',           'graduation-cap','#2563EB','rgba(37,99,235,.1)'],
            [$totalProfessors, 'معلمون',          'user-cog',      '#7c3aed','rgba(124,58,237,.1)'],
            [$totalCourses,    'كورسات',          'book-open',     '#16a34a','rgba(22,163,74,.1)'],
            [$totalEnrollments,'تسجيلات نشطة',   'users',         '#d97706','rgba(217,119,6,.1)'],
        ] as [$v,$l,$ic,$col,$bg]): ?>
        <div class="glass rounded-2xl p-5 reveal">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style="background:<?php echo $bg; ?>">
                <i data-lucide="<?php echo $ic; ?>" style="width:18px;height:18px;color:<?php echo $col; ?>"></i>
            </span>
            <p class="display font-semibold text-3xl" style="color:<?php echo $col; ?>"><?php echo number_format($v); ?></p>
            <p class="text-xs text-slate-500 mt-1"><?php echo $l; ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- KPI cards row 2 -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <?php foreach ([
            [$totalMaterials,           'مواد دراسية',     'file-text',  '#0ea5e9','rgba(14,165,233,.1)'],
            [$avgEnrollPerCourse,       'متوسط طلاب/كورس', 'trending-up','#16a34a','rgba(22,163,74,.1)'],
            [$subActive,               'اشتراكات نشطة',   'check-circle','#16a34a','rgba(22,163,74,.1)'],
            [number_format($totalRevenue,0).' ج','إجمالي الإيرادات','banknote','#2563EB','rgba(37,99,235,.1)'],
        ] as [$v,$l,$ic,$col,$bg]): ?>
        <div class="glass rounded-2xl p-4 reveal">
            <span class="w-8 h-8 rounded-lg flex items-center justify-center mb-2" style="background:<?php echo $bg; ?>">
                <i data-lucide="<?php echo $ic; ?>" style="width:15px;height:15px;color:<?php echo $col; ?>"></i>
            </span>
            <p class="display font-semibold text-2xl" style="color:<?php echo $col; ?>"><?php echo $v; ?></p>
            <p class="text-xs text-slate-500 mt-0.5"><?php echo $l; ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts -->
    <div class="grid lg:grid-cols-3 gap-6 mb-8">
        <div class="glass rounded-3xl p-6 lg:col-span-2">
            <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2">
                <i data-lucide="trending-up" style="width:14px;height:14px;color:#2563EB"></i>
                التسجيلات الشهرية (آخر 6 أشهر)
            </h3>
            <canvas id="growthChart" style="max-height:220px;"></canvas>
        </div>
        <div class="glass rounded-3xl p-6">
            <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2">
                <i data-lucide="pie-chart" style="width:14px;height:14px;color:#7c3aed"></i>
                توزيع الأسئلة
            </h3>
            <canvas id="questionsChart" style="max-height:220px;"></canvas>
        </div>
    </div>

    <!-- Top courses -->
    <div class="glass rounded-3xl p-6 mb-8">
        <h3 class="font-bold text-slate-700 mb-5 flex items-center gap-2">
            <i data-lucide="trophy" style="width:15px;height:15px;color:#d97706"></i>
            أكثر الكورسات تسجيلاً
        </h3>
        <?php $maxCnt = max(array_column($topCourses, 'cnt') ?: [1]); ?>
        <div class="space-y-3">
            <?php foreach ($topCourses as $i => $c):
                $pct = $maxCnt > 0 ? round($c['cnt'] / $maxCnt * 100) : 0;
                $medals = ['🥇','🥈','🥉'];
            ?>
            <div class="flex items-center gap-3">
                <span class="w-6 shrink-0 text-base"><?php echo $medals[$i] ?? ($i+1); ?></span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($c['course_name']); ?></p>
                        <span class="text-xs font-bold text-slate-500 shrink-0 mr-2"><?php echo $c['cnt']; ?> طالب</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all" style="width:<?php echo $pct; ?>%;background:linear-gradient(90deg,#2563EB,#60A5FA)"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const font = { family:"'Cairo','DM Sans',sans-serif", size:10 };

    // Growth chart
    const monthlyLabels  = <?php echo json_encode(array_column($monthlyData, 'month')); ?>;
    const monthlyStudents = <?php echo json_encode(array_column($monthlyData, 'students')); ?>;
    const monthlyProfs   = <?php echo json_encode(array_column($monthlyData, 'professors')); ?>;

    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [
                { label: 'طلاب',   data: monthlyStudents, borderColor:'#2563EB', backgroundColor:'rgba(37,99,235,.07)', fill:true, tension:0.4, pointRadius:4 },
                { label: 'معلمون', data: monthlyProfs,    borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,.07)', fill:true, tension:0.4, pointRadius:4 },
            ]
        },
        options: { plugins:{ legend:{ labels:{ font, color:'#64748b', padding:12 } } }, scales:{ y:{ grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font, color:'#94a3b8' } }, x:{ grid:{ display:false }, ticks:{ font, color:'#94a3b8' } } } }
    });

    // Questions chart
    new Chart(document.getElementById('questionsChart'), {
        type: 'doughnut',
        data: {
            labels: ['معلقة', 'مجاوَبة'],
            datasets: [{ data: [<?php echo $qPending; ?>, <?php echo $qAnswered; ?>], backgroundColor: ['#f59e0b','#16a34a'], borderWidth:0, hoverOffset:4 }]
        },
        options: { cutout:'62%', plugins:{ legend:{ position:'bottom', labels:{ font, color:'#64748b', padding:10 } } } }
    });
});
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
