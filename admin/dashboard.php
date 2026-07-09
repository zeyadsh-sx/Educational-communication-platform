<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isProfessor()) {
    redirect('/auth/login.php');
    exit;
}

$userId   = getCurrentUserId();
$base     = nagahBaseUrl();

$professorCourses     = getCourses($userId);
$recentQuestions      = getRecentQuestions($userId, 'professor');
$upcomingAppointments = getUpcomingAppointmentsList($userId, 'professor');
$analytics            = getProfessorAnalytics($userId);

$pdo = getDB();
$totalStudentsAll = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='student'")->fetchColumn();
$activeStudents   = (int)$pdo->query("SELECT COUNT(DISTINCT student_id) FROM course_enrollments WHERE status='active'")->fetchColumn();

$totalMyStudents  = 0;
foreach ($professorCourses as $c) $totalMyStudents += getCourseStudentCount($c['id']);
$pendingQuestions = getPendingQuestionsCount($userId, 'professor');
$upcomingApptCount = getUpcomingAppointmentsCount($userId, 'professor');

$pageTitle = 'لوحة تحكم المعلم | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<div class="flex min-h-[calc(100vh-64px)]">

    <!-- ===== SIDEBAR ===== -->
    <aside class="hidden lg:flex flex-col w-60 shrink-0 border-l border-slate-100 bg-white/80 backdrop-blur sticky top-16 self-start overflow-y-auto" style="height:calc(100vh - 64px)">
        <div class="p-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-white font-bold text-sm shrink-0"
                      style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <?php echo mb_substr($_SESSION['full_name'] ?? 'A', 0, 2); ?>
                </span>
                <div class="min-w-0">
                    <p class="font-bold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></p>
                    <p class="text-xs text-blue-600 font-medium">معلم</p>
                </div>
            </div>
        </div>

        <nav class="p-3 space-y-0.5 flex-1">
            <?php
            $navLinks = [
                ['layout-dashboard', 'لوحة التحكم',    $base.'/admin/dashboard.php',        true],
                ['book-open',        'الكورسات',        $base.'/courses/list.php',            false],
                ['plus-circle',      'كورس جديد',       $base.'/courses/create.php',          false],
                ['megaphone',        'الإعلانات',       $base.'/announcements/view.php',      false],
                ['users',            'إدارة المعلمين',  $base.'/admin/manage_professors.php', false],
                ['user',             'الملف الشخصي',    $base.'/auth/profile.php',            false],
                ['log-out',          'تسجيل الخروج',    $base.'/auth/logout.php',             false],
            ];
            foreach ($navLinks as [$icon, $label, $url, $active]):
            ?>
            <a href="<?php echo $url; ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
               <?php echo $active
                    ? 'bg-blue-50 text-blue-700 font-bold'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'; ?>">
                <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
                <?php echo $label; ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- ===== MAIN ===== -->
    <main class="flex-1 min-w-0 py-8 px-5 sm:px-8">
        <div class="max-w-5xl">

            <!-- Welcome -->
            <div class="mb-8">
                <h1 class="display font-semibold text-2xl sm:text-3xl text-slate-900">
                    مرحباً، <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'أستاذ'); ?> 👋
                </h1>
                <p class="text-slate-500 mt-1 text-sm">إليك ملخص شامل لنشاطك اليوم</p>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <?php foreach ([
                    [count($professorCourses), 'كورساتي',        'book',           '#2563EB', 'rgba(37,99,235,.1)'],
                    [$totalMyStudents,          'إجمالي الطلاب',  'users',          '#16a34a', 'rgba(22,163,74,.1)'],
                    [$pendingQuestions,         'أسئلة معلقة',    'message-circle', '#dc2626', 'rgba(220,38,38,.1)'],
                    [$upcomingApptCount,        'مواعيد قادمة',   'calendar',       '#d97706', 'rgba(217,119,6,.1)'],
                ] as [$val, $label, $icon, $color, $bg]): ?>
                <div class="glass rounded-2xl p-5 reveal">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center mb-3"
                          style="background:<?php echo $bg; ?>">
                        <i data-lucide="<?php echo $icon; ?>" style="width:18px;height:18px;color:<?php echo $color; ?>"></i>
                    </span>
                    <p class="display font-semibold text-3xl" style="color:<?php echo $color; ?>"><?php echo $val; ?></p>
                    <p class="text-xs text-slate-500 mt-1"><?php echo $label; ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Charts row -->
            <div class="grid sm:grid-cols-3 gap-5 mb-8">

                <!-- Questions doughnut -->
                <div class="glass rounded-3xl p-6">
                    <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2">
                        <i data-lucide="pie-chart" style="width:15px;height:15px;color:#2563EB"></i> الأسئلة
                    </h3>
                    <canvas id="questionsChart" style="max-height:160px;"></canvas>
                </div>

                <!-- Active students -->
                <div class="glass rounded-3xl p-6 flex flex-col items-center justify-center text-center">
                    <span class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3"
                          style="background:rgba(22,163,74,.1)">
                        <i data-lucide="users" style="width:22px;height:22px;color:#16a34a"></i>
                    </span>
                    <p class="display font-semibold text-4xl" style="color:#2563EB"><?php echo $activeStudents; ?></p>
                    <p class="text-slate-500 text-xs mt-1">طالب نشط</p>
                    <p class="text-slate-400 text-xs mt-0.5">من <?php echo $totalStudentsAll; ?> مسجل</p>
                    <div class="w-full bg-slate-100 rounded-full h-2 mt-4">
                        <?php $pct = $totalStudentsAll > 0 ? round($activeStudents / $totalStudentsAll * 100) : 0; ?>
                        <div class="h-2 rounded-full" style="width:<?php echo $pct; ?>%;background:linear-gradient(90deg,#2563EB,#60A5FA)"></div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1"><?php echo $pct; ?>% نسبة النشاط</p>
                </div>

                <!-- Enrollment bar -->
                <div class="glass rounded-3xl p-6">
                    <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2">
                        <i data-lucide="bar-chart-2" style="width:15px;height:15px;color:#d97706"></i> التسجيل
                    </h3>
                    <canvas id="enrollmentChart" style="max-height:160px;"></canvas>
                </div>
            </div>

            <!-- Growth chart -->
            <div class="glass rounded-3xl p-6 mb-8">
                <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <i data-lucide="trending-up" style="width:16px;height:16px;color:#2563EB"></i> نمو الطلاب خلال العام
                </h3>
                <canvas id="growthChart" style="max-height:200px;"></canvas>
            </div>

            <!-- My Courses -->
            <div class="glass rounded-3xl overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="book" style="width:17px;height:17px;color:#2563EB"></i> إدارة الكورسات
                    </h2>
                    <a href="<?php echo $base; ?>/courses/create.php"
                       class="btn-primary-nagah inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold shadow hover:-translate-y-0.5 transition-all">
                        <i data-lucide="plus" style="width:14px;height:14px;"></i> كورس جديد
                    </a>
                </div>

                <?php if (empty($professorCourses)): ?>
                <div class="text-center py-14 text-slate-400">
                    <i data-lucide="book-open" style="width:44px;height:44px;" class="mx-auto mb-3 opacity-30"></i>
                    <p class="text-sm mb-4">لم تقم بإنشاء أي كورس بعد</p>
                    <a href="<?php echo $base; ?>/courses/create.php"
                       class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold">
                        أنشئ كورسك الأول
                    </a>
                </div>
                <?php else: ?>
                <div class="p-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($professorCourses as $c): ?>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 hover:bg-blue-50/30 transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="tag-pill text-xs font-bold px-2.5 py-1 rounded-full">
                                <?php echo htmlspecialchars($c['course_code']); ?>
                            </span>
                            <span class="text-xs text-slate-400 flex items-center gap-1">
                                <i data-lucide="users" style="width:11px;height:11px;"></i>
                                <?php echo getCourseStudentCount($c['id']); ?>
                            </span>
                        </div>
                        <h3 class="font-bold text-slate-800 text-sm mb-3 leading-snug">
                            <?php echo htmlspecialchars($c['course_name']); ?>
                        </h3>
                        <div class="flex gap-2">
                            <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $c['id']; ?>"
                               class="flex-1 text-center py-1.5 rounded-xl text-xs font-bold btn-primary-nagah">عرض</a>
                            <a href="<?php echo $base; ?>/courses/manage.php?id=<?php echo $c['id']; ?>"
                               class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 text-slate-600 hover:bg-white transition flex items-center">
                                <i data-lucide="settings" style="width:12px;height:12px;"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Bottom: Questions + Appointments -->
            <div class="grid lg:grid-cols-2 gap-6">

                <!-- Recent questions -->
                <div class="glass rounded-3xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <i data-lucide="message-circle" style="width:16px;height:16px;color:#dc2626"></i>
                            أسئلة حديثة
                            <?php if ($pendingQuestions > 0): ?>
                            <span class="w-5 h-5 rounded-full bg-red-100 text-red-700 text-xs font-bold flex items-center justify-center">
                                <?php echo $pendingQuestions; ?>
                            </span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    <?php if (empty($recentQuestions)): ?>
                    <div class="text-center py-10 text-slate-400">
                        <i data-lucide="message-circle" style="width:36px;height:36px;" class="mx-auto mb-2 opacity-30"></i>
                        <p class="text-xs">لا توجد أسئلة معلقة</p>
                    </div>
                    <?php else: ?>
                    <div class="p-4 space-y-3">
                        <?php foreach ($recentQuestions as $q): ?>
                        <div class="p-3 rounded-2xl bg-slate-50 border-r-4 border-red-400">
                            <p class="font-semibold text-sm text-slate-800"><?php echo htmlspecialchars($q['student_name']); ?></p>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">
                                <?php echo htmlspecialchars(mb_substr($q['question_text'], 0, 70)); ?>…
                            </p>
                            <a href="<?php echo $base; ?>/questions/answer.php?id=<?php echo $q['id']; ?>"
                               class="text-xs font-bold text-blue-600 hover:underline mt-1.5 inline-block">
                                رد الآن ←
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming appointments -->
                <div class="glass rounded-3xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <i data-lucide="calendar" style="width:16px;height:16px;color:#d97706"></i>
                            المواعيد القادمة
                        </h2>
                        <a href="<?php echo $base; ?>/appointments/view.php"
                           class="text-xs font-bold text-blue-600 hover:underline">عرض الكل</a>
                    </div>
                    <?php if (empty($upcomingAppointments)): ?>
                    <div class="text-center py-10 text-slate-400">
                        <i data-lucide="calendar-x" style="width:36px;height:36px;" class="mx-auto mb-2 opacity-30"></i>
                        <p class="text-xs">لا توجد مواعيد قادمة</p>
                    </div>
                    <?php else: ?>
                    <div class="p-4 space-y-3">
                        <?php
                        $statusCfg = [
                            'pending'   => ['bg-amber-100','text-amber-700','معلق'],
                            'confirmed' => ['bg-green-100','text-green-700','مؤكد'],
                            'cancelled' => ['bg-red-100',  'text-red-700',  'ملغي'],
                        ];
                        foreach (array_slice($upcomingAppointments, 0, 5) as $app):
                            [$sbg, $stxt, $slbl] = $statusCfg[$app['status']] ?? ['bg-slate-100','text-slate-600',$app['status']];
                        ?>
                        <div class="p-3 rounded-2xl bg-slate-50 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-slate-800 truncate">
                                    <?php echo htmlspecialchars($app['other_party'] ?? 'طالب'); ?>
                                </p>
                                <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                    <i data-lucide="clock" style="width:11px;height:11px;"></i>
                                    <?php echo isset($app['date_time']) ? date('d/m/Y H:i', strtotime($app['date_time'])) : '—'; ?>
                                </p>
                                <?php if (!empty($app['notes'])): ?>
                                <p class="text-xs text-slate-400 mt-0.5 truncate"><?php echo htmlspecialchars(mb_substr($app['notes'], 0, 50)); ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold shrink-0 <?php echo "$sbg $stxt"; ?>">
                                <?php echo $slbl; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const analytics = <?php echo json_encode($analytics); ?>;
    const font = { family: "'Cairo', 'DM Sans', sans-serif", size: 11 };
    const gridColor = 'rgba(0,0,0,.05)';

    // Questions doughnut
    new Chart(document.getElementById('questionsChart'), {
        type: 'doughnut',
        data: {
            labels: ['معلقة', 'مجابة'],
            datasets: [{ data: [analytics.questions?.pending ?? 0, analytics.questions?.answered ?? 0],
                backgroundColor: ['#EF4444','#10B981'], borderWidth: 0, hoverOffset: 4 }]
        },
        options: { cutout: '68%', plugins: { legend: { position: 'bottom', labels: { font, color: '#64748b', padding: 12 } } } }
    });

    // Enrollment bar
    new Chart(document.getElementById('enrollmentChart'), {
        type: 'bar',
        data: {
            labels: ['رياضيات','فيزياء','كيمياء','عربي','إنجليزي'],
            datasets: [{ data: [120,95,88,76,82],
                backgroundColor: ['#2563EB','#3B82F6','#10B981','#F59E0B','#8B5CF6'],
                borderRadius: 6, borderSkipped: false }]
        },
        options: { plugins: { legend: { display: false } },
            scales: { y: { grid: { color: gridColor }, ticks: { font, color:'#94a3b8' } },
                      x: { grid: { display: false }, ticks: { font, color:'#94a3b8' } } } }
    });

    // Growth line
    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: ['يناير','فبراير','مارس','أبريل','مايو','يونيو'],
            datasets: [{ label: 'طلاب جدد',
                data: [45, 62, 78, 95, 110, <?php echo $totalStudentsAll; ?>],
                borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,.08)',
                fill: true, tension: 0.4, pointBackgroundColor: '#2563EB', pointRadius: 4 }]
        },
        options: { plugins: { legend: { labels: { font, color:'#64748b' } } },
            scales: { y: { grid: { color: gridColor }, ticks: { font, color:'#94a3b8' } },
                      x: { grid: { display: false }, ticks: { font, color:'#94a3b8' } } } }
    });
});
</script>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
