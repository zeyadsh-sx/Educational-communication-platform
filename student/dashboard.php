<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isStudent()) { redirect('/auth/login.php'); exit; }

$userId   = getCurrentUserId();
$base     = nagahBaseUrl();
$pdo      = getDB();

// Data
$studentCourses       = getStudentCourses($userId);
$upcomingAppointments = getUpcomingAppointmentsList($userId, 'student', 3);
$recentNotifs         = getRecentNotifications($userId, 5);
$analytics            = getStudentAnalytics($userId);
$leaderboard          = getLeaderboard(5);
$achievements         = getStudentAchievements($userId);

$pointsRow   = $pdo->prepare("SELECT points FROM users WHERE id = ?");
$pointsRow->execute([$userId]);
$studentPoints = (int)($pointsRow->fetchColumn() ?: 0);
$studentRank   = getStudentRank($userId);
$unreadNotifs  = getUnreadNotificationsCount($userId);

// Fake derived stats (based on real course count)
$coursesCount     = count($studentCourses);
$completedLessons = min($coursesCount * 12, 48);
$attendanceRate   = 92;
$upcomingExams    = 3;

// Materials count
$matStmt = $pdo->prepare("SELECT COUNT(*) FROM materials m JOIN course_enrollments ce ON m.course_id=ce.course_id WHERE ce.student_id=? AND ce.status='active'");
$matStmt->execute([$userId]);
$materialsCount = (int)$matStmt->fetchColumn();

// Level badge
$level = $studentPoints >= 1000 ? ['👑','الأسطورة','#d97706'] :
        ($studentPoints >= 500  ? ['🏆','بطل','#7c3aed'] :
        ($studentPoints >= 200  ? ['🔥','نشط','#dc2626'] :
        ($studentPoints >= 50   ? ['🚀','ناشئ','#2563EB'] :
                                  ['⭐','مبتدئ','#64748b'])));

$_activeSidebar = 'dashboard';
$pageTitle = 'لوحة الطالب | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>
<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_student.php'; ?>

<!-- MAIN -->
<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-5xl">

<!-- Welcome -->
<div class="mb-8 flex items-start justify-between flex-wrap gap-4">
    <div>
        <h1 class="display font-semibold text-2xl sm:text-3xl text-slate-900">
            لوحة الطالب
        </h1>
        <p class="text-slate-500 mt-1 text-sm">إليك نظرة شاملة على تقدمك الدراسي</p>
    </div>
    <a href="<?php echo $base; ?>/courses/list.php" class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all">
        <i data-lucide="plus" style="width:15px;height:15px;"></i> انضم لكورس جديد
    </a>
</div>

<!-- Stat cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php foreach ([
        [$coursesCount,     'الكورسات',       'book-open',     '#2563EB','rgba(37,99,235,.1)'],
        [$completedLessons, 'دروس مكتملة',    'check-circle',  '#16a34a','rgba(22,163,74,.1)'],
        [$upcomingExams,    'امتحانات قادمة', 'file-check-2',  '#dc2626','rgba(220,38,38,.1)'],
        [$attendanceRate.'%','نسبة الحضور',   'user-check',    '#d97706','rgba(217,119,6,.1)'],
    ] as [$val,$label,$icon,$color,$bg]): ?>
    <div class="glass rounded-2xl p-5 reveal">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style="background:<?php echo $bg; ?>">
            <i data-lucide="<?php echo $icon; ?>" style="width:18px;height:18px;color:<?php echo $color; ?>"></i>
        </span>
        <p class="display font-semibold text-3xl" style="color:<?php echo $color; ?>"><?php echo $val; ?></p>
        <p class="text-xs text-slate-500 mt-1"><?php echo $label; ?></p>
    </div>
    <?php endforeach; ?>
</div>

<!-- Points + chart row -->
<div class="grid sm:grid-cols-3 gap-5 mb-8">
    <!-- Points card -->
    <div class="glass rounded-3xl p-6 flex flex-col items-center justify-center text-center">
        <div class="text-4xl mb-2"><?php echo $level[0]; ?></div>
        <p class="display font-semibold text-4xl" style="color:<?php echo $level[2]; ?>"><?php echo number_format($studentPoints); ?></p>
        <p class="text-slate-500 text-xs mt-1">نقطة — مستوى <?php echo $level[1]; ?></p>
        <div class="mt-3 flex items-center gap-2 text-sm font-bold" style="color:#2563EB">
            <i data-lucide="trophy" style="width:15px;height:15px;"></i> ترتيبك #<?php echo $studentRank; ?>
        </div>
        <?php if (!empty($achievements)): ?>
        <div class="flex gap-1.5 mt-4 flex-wrap justify-center">
            <?php foreach (array_slice($achievements, 0, 4) as $ach): ?>
            <span class="text-lg" title="<?php echo htmlspecialchars($ach['achievement_name']); ?>"><?php echo $ach['achievement_icon']; ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <!-- Materials chart -->
    <div class="glass rounded-3xl p-6">
        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2">
            <i data-lucide="bar-chart-2" style="width:14px;height:14px;color:#2563EB"></i> المواد لكل كورس
        </h3>
        <canvas id="matChart" style="max-height:160px;"></canvas>
    </div>
    <!-- Appointments chart -->
    <div class="glass rounded-3xl p-6">
        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2">
            <i data-lucide="pie-chart" style="width:14px;height:14px;color:#d97706"></i> المواعيد
        </h3>
        <canvas id="apptChart" style="max-height:160px;"></canvas>
    </div>
</div>

<!-- My Courses -->
<div class="glass rounded-3xl overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-bold text-slate-800 flex items-center gap-2">
            <i data-lucide="book-open" style="width:17px;height:17px;color:#2563EB"></i> كورساتي
        </h2>
        <a href="<?php echo $base; ?>/courses/list.php" class="text-xs font-bold text-blue-600 hover:underline">تصفح المزيد</a>
    </div>
    <?php if (empty($studentCourses)): ?>
    <div class="text-center py-14 text-slate-400">
        <i data-lucide="book-open" style="width:44px;height:44px;" class="mx-auto mb-3 opacity-30"></i>
        <p class="text-sm mb-4">لم تنضم لأي كورس بعد</p>
        <a href="<?php echo $base; ?>/courses/list.php" class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold">استكشف الكورسات</a>
    </div>
    <?php else: ?>
    <div class="p-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($studentCourses as $c): ?>
        <?php
            $matCountC = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE course_id=?");
            $matCountC->execute([$c['id']]); $cMats = $matCountC->fetchColumn();
            $progress = min(100, ($cMats > 0 ? 65 : 20));
        ?>
        <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 hover:bg-blue-50/30 transition">
            <div class="h-16 rounded-xl flex items-center justify-center mb-3" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="book" style="width:24px;height:24px;color:white;opacity:.85;"></i>
            </div>
            <span class="tag-pill text-xs font-bold px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($c['course_code']); ?></span>
            <h3 class="font-bold text-slate-800 text-sm mt-2 mb-1"><?php echo htmlspecialchars($c['course_name']); ?></h3>
            <p class="text-xs text-slate-500 mb-3 flex items-center gap-1">
                <i data-lucide="user-round" style="width:12px;height:12px;"></i> <?php echo htmlspecialchars($c['professor_name'] ?? ''); ?>
            </p>
            <!-- Progress bar -->
            <div class="mb-3">
                <div class="flex justify-between text-xs text-slate-400 mb-1"><span>التقدم</span><span><?php echo $progress; ?>%</span></div>
                <div class="w-full bg-slate-200 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full" style="width:<?php echo $progress; ?>%;background:linear-gradient(90deg,#2563EB,#60A5FA)"></div>
                </div>
            </div>
            <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $c['id']; ?>" class="block text-center py-1.5 rounded-xl text-xs font-bold btn-primary-nagah">دخول الكورس</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Bottom row: Notifications + Appointments + Leaderboard -->
<div class="grid lg:grid-cols-3 gap-6 mb-8">

    <!-- Notifications -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="bell" style="width:15px;height:15px;color:#F59E0B"></i> الإشعارات
                <?php if ($unreadNotifs > 0): ?><span class="w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center"><?php echo $unreadNotifs; ?></span><?php endif; ?>
            </h2>
            <a href="<?php echo $base; ?>/notifications/view.php" class="text-xs font-bold text-blue-600 hover:underline">الكل</a>
        </div>
        <?php if (empty($recentNotifs)): ?>
        <div class="text-center py-10 text-slate-400"><i data-lucide="bell-off" style="width:32px;height:32px;" class="mx-auto mb-2 opacity-30"></i><p class="text-xs">لا توجد إشعارات</p></div>
        <?php else: ?>
        <div class="p-3 space-y-2">
            <?php foreach ($recentNotifs as $n): ?>
            <div class="p-3 rounded-xl bg-slate-50 text-xs">
                <p class="text-slate-700 leading-relaxed"><?php echo htmlspecialchars(mb_substr($n['message'], 0, 70)); ?><?php echo mb_strlen($n['message']) > 70 ? '…' : ''; ?></p>
                <p class="text-slate-400 mt-1 flex items-center gap-1"><i data-lucide="clock" style="width:10px;height:10px;"></i><?php echo date('d/m H:i', strtotime($n['created_at'])); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming appointments -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="calendar" style="width:15px;height:15px;color:#2563EB"></i> مواعيدي القادمة
            </h2>
            <a href="<?php echo $base; ?>/appointments/book.php" class="text-xs font-bold text-blue-600 hover:underline">احجز موعد</a>
        </div>
        <?php if (empty($upcomingAppointments)): ?>
        <div class="text-center py-10 text-slate-400"><i data-lucide="calendar-x" style="width:32px;height:32px;" class="mx-auto mb-2 opacity-30"></i><p class="text-xs">لا توجد مواعيد</p></div>
        <?php else: ?>
        <div class="p-3 space-y-2">
            <?php $apptColors = ['pending'=>'bg-amber-100 text-amber-700','confirmed'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
            $apptLabels = ['pending'=>'معلق','confirmed'=>'مؤكد','cancelled'=>'ملغي'];
            foreach ($upcomingAppointments as $app): $sc = $apptColors[$app['status']] ?? 'bg-slate-100 text-slate-600'; $sl = $apptLabels[$app['status']] ?? $app['status']; ?>
            <div class="p-3 rounded-xl bg-slate-50">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold text-xs text-slate-800"><?php echo htmlspecialchars($app['professor_name'] ?? 'المعلم'); ?></p>
                        <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1"><i data-lucide="clock" style="width:10px;height:10px;"></i><?php echo isset($app['date_time']) ? date('d/m/Y H:i', strtotime($app['date_time'])) : '—'; ?></p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold <?php echo $sc; ?>"><?php echo $sl; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Leaderboard -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="trophy" style="width:15px;height:15px;color:#d97706"></i> المتصدرون
            </h2>
        </div>
        <div class="p-3 space-y-2">
            <?php foreach ($leaderboard as $i => $leader):
                $medals = ['🥇','🥈','🥉'];
                $isMe   = $leader['id'] == $userId;
            ?>
            <div class="p-3 rounded-xl flex items-center gap-3 <?php echo $isMe ? 'bg-blue-50 border border-blue-200' : 'bg-slate-50'; ?>">
                <span class="text-base shrink-0"><?php echo $medals[$i] ?? ($i+1).'.'; ?></span>
                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0" style="background:linear-gradient(135deg,#2563EB,#60A5FA)"><?php echo mb_substr($leader['full_name'], 0, 1); ?></span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-xs text-slate-800 truncate"><?php echo htmlspecialchars($leader['full_name']); ?> <?php echo $isMe ? '(أنت)' : ''; ?></p>
                    <p class="text-xs text-slate-400"><?php echo number_format($leader['points']); ?> نقطة</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Exam / Homework / Schedule quick section -->
<div class="grid sm:grid-cols-3 gap-5">
    <?php $quickCards = [
        ['file-check-2','#dc2626','rgba(220,38,38,.1)','امتحاناتي القادمة','رياضيات — الثلاثاء 15/7','فيزياء — الأحد 20/7','كيمياء — الأربعاء 23/7'],
        ['notebook-pen','#7c3aed','rgba(124,58,237,.1)','واجباتي الحالية','واجب الجبر — يُسلّم 15/7','تقرير كيمياء — يُسلّم 12/7','مقال أدبي — يُسلّم 10/7'],
        ['award','#16a34a','rgba(22,163,74,.1)','شهاداتي','لا توجد شهادات بعد — أكمل كورسك الأول!','',''],
    ];
    foreach ($quickCards as [$icon,$color,$bg,$title,$l1,$l2,$l3]): ?>
    <div class="glass rounded-3xl p-5">
        <div class="flex items-center gap-2 mb-4">
            <span class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:<?php echo $bg; ?>"><i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;color:<?php echo $color; ?>"></i></span>
            <h3 class="font-bold text-slate-700 text-sm"><?php echo $title; ?></h3>
        </div>
        <ul class="space-y-2 text-xs text-slate-600">
            <?php foreach ([$l1,$l2,$l3] as $line): if ($line): ?>
            <li class="flex items-start gap-1.5"><i data-lucide="chevron-left" style="width:12px;height:12px;color:<?php echo $color; ?>;margin-top:1px;flex-shrink:0"></i><?php echo htmlspecialchars($line); ?></li>
            <?php endif; endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>
</div>

</div></main></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const font = { family: "'Cairo','DM Sans',sans-serif", size: 10 };
    const analytics = <?php echo json_encode($analytics); ?>;

    new Chart(document.getElementById('matChart'), {
        type: 'bar',
        data: { labels: analytics.courses.length ? analytics.courses : ['لا يوجد'],
            datasets: [{ data: analytics.materials_count.length ? analytics.materials_count : [0],
                backgroundColor: '#2563EB', borderRadius: 5, borderSkipped: false }] },
        options: { plugins: { legend: { display: false } }, scales: {
            y: { grid: { color: 'rgba(0,0,0,.05)' }, ticks: { font, color: '#94a3b8' } },
            x: { grid: { display: false }, ticks: { font, color: '#94a3b8' } } } }
    });

    new Chart(document.getElementById('apptChart'), {
        type: 'doughnut',
        data: { labels: ['معلق','مكتمل','ملغي'],
            datasets: [{ data: [analytics.appointments.pending, analytics.appointments.completed, analytics.appointments.cancelled],
                backgroundColor: ['#F59E0B','#10B981','#EF4444'], borderWidth: 0, hoverOffset: 4 }] },
        options: { cutout: '65%', plugins: { legend: { position: 'bottom', labels: { font, color: '#64748b', padding: 10 } } } }
    });
});
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
