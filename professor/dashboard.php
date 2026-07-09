<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isProfessor()) { redirect('/auth/login.php'); exit; }

$userId   = getCurrentUserId();
$base     = nagahBaseUrl();
$pdo      = getDB();

// Data
$myCourses            = getProfessorCourses($userId);
$recentQuestions      = getRecentQuestions($userId, 'professor', 5);
$upcomingAppointments = getUpcomingAppointmentsList($userId, 'professor', 4);
$analytics            = getProfessorAnalytics($userId);

$pendingQ    = getPendingQuestionsCount($userId, 'professor');
$apptCount   = getUpcomingAppointmentsCount($userId, 'professor');
$unreadNotif = getUnreadNotificationsCount($userId);

$totalMyStudents = 0;
foreach ($myCourses as $c) $totalMyStudents += (int)($c['student_count'] ?? 0);

$totalMaterials = 0;
foreach ($myCourses as $c) $totalMaterials += (int)($c['material_count'] ?? 0);

$pageTitle = 'لوحة تحكم المعلم | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>
<div class="flex min-h-[calc(100vh-64px)]">

<!-- ===== SIDEBAR ===== -->
<aside class="hidden lg:flex flex-col w-60 shrink-0 border-l border-slate-100 bg-white/80 backdrop-blur sticky top-16 self-start overflow-y-auto" style="height:calc(100vh - 64px)">
    <div class="p-5 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-white font-bold text-sm shrink-0" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <?php echo mb_substr($_SESSION['full_name'] ?? 'P', 0, 2); ?>
            </span>
            <div class="min-w-0">
                <p class="font-bold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></p>
                <p class="text-xs text-blue-600 font-medium">معلم</p>
            </div>
        </div>
    </div>
    <nav class="p-3 space-y-0.5 flex-1">
        <?php $navLinks = [
            ['layout-dashboard','لوحة التحكم',      $base.'/professor/dashboard.php',       true],
            ['book',            'كورساتي',            $base.'/courses/list.php',              false],
            ['plus-circle',     'كورس جديد',          $base.'/courses/create.php',            false],
            ['upload-cloud',    'رفع مادة',           $base.'/materials/upload.php',          false],
            ['megaphone',       'الإعلانات',          $base.'/announcements/create.php',      false],
            ['message-circle',  'الأسئلة',            $base.'/courses/list.php',              false],
            ['users',           'إدارة المعلمين',     $base.'/admin/manage_professors.php',   false],
            ['user',            'الملف الشخصي',       $base.'/auth/profile.php',              false],
            ['log-out',         'تسجيل الخروج',       $base.'/auth/logout.php',               false],
        ];
        foreach ($navLinks as [$icon,$label,$url,$active]): ?>
        <a href="<?php echo $url; ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo $active ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-100'; ?>">
            <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
            <?php echo $label; ?>
            <?php if ($icon === 'bell' && $unreadNotif > 0): ?><span class="mr-auto w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center"><?php echo $unreadNotif; ?></span><?php endif; ?>
            <?php if ($icon === 'message-circle' && $pendingQ > 0): ?><span class="mr-auto w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center"><?php echo $pendingQ; ?></span><?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
</aside>

<!-- ===== MAIN ===== -->
<main class="flex-1 min-w-0 py-8 px-5 sm:px-8">
<div class="max-w-5xl">

<!-- Welcome -->
<div class="mb-8 flex items-start justify-between flex-wrap gap-4">
    <div>
        <h1 class="display font-semibold text-2xl sm:text-3xl text-slate-900">مرحباً، <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'أستاذ'); ?> 👨‍🏫</h1>
        <p class="text-slate-500 mt-1 text-sm">إدارة كورساتك وتفاعلك مع الطلاب من مكان واحد</p>
    </div>
    <div class="flex gap-3 flex-wrap">
        <a href="<?php echo $base; ?>/courses/create.php" class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> كورس جديد
        </a>
        <a href="<?php echo $base; ?>/announcements/create.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border-2 border-blue-600 text-blue-600 hover:bg-blue-50 transition">
            <i data-lucide="megaphone" style="width:15px;height:15px;"></i> إعلان جديد
        </a>
    </div>
</div>

<!-- Stat cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php foreach ([
        [count($myCourses),  'كورساتي',         'book',           '#2563EB','rgba(37,99,235,.1)'],
        [$totalMyStudents,    'إجمالي الطلاب',   'users',          '#16a34a','rgba(22,163,74,.1)'],
        [$pendingQ,           'أسئلة معلقة',     'message-circle', '#dc2626','rgba(220,38,38,.1)'],
        [$totalMaterials,     'مواد مرفوعة',     'folder-open',    '#d97706','rgba(217,119,6,.1)'],
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

<!-- Charts row -->
<div class="grid sm:grid-cols-3 gap-5 mb-8">
    <div class="glass rounded-3xl p-6">
        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2"><i data-lucide="pie-chart" style="width:14px;height:14px;color:#2563EB"></i> الأسئلة</h3>
        <canvas id="qChart" style="max-height:160px;"></canvas>
    </div>
    <div class="glass rounded-3xl p-6">
        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2"><i data-lucide="bar-chart-2" style="width:14px;height:14px;color:#16a34a"></i> طلاب لكل كورس</h3>
        <canvas id="studChart" style="max-height:160px;"></canvas>
    </div>
    <div class="glass rounded-3xl p-6">
        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2"><i data-lucide="calendar" style="width:14px;height:14px;color:#d97706"></i> المواعيد</h3>
        <?php $apptPending = 0; $apptConfirmed = 0;
        foreach ($upcomingAppointments as $a) {
            if ($a['status'] === 'pending') $apptPending++;
            elseif ($a['status'] === 'confirmed') $apptConfirmed++;
        } ?>
        <div class="space-y-3 mt-2">
            <?php foreach ([['معلق',$apptPending,'#F59E0B'],['مؤكد',$apptConfirmed,'#10B981'],['قادمة',$apptCount,'#2563EB']] as [$lbl,$n,$col]): ?>
            <div>
                <div class="flex justify-between text-xs mb-1"><span class="text-slate-500"><?php echo $lbl; ?></span><span class="font-bold" style="color:<?php echo $col; ?>"><?php echo $n; ?></span></div>
                <div class="w-full bg-slate-100 rounded-full h-1.5"><div class="h-1.5 rounded-full" style="width:<?php echo $apptCount > 0 ? min(100, round($n/$apptCount*100)) : 0; ?>%;background:<?php echo $col; ?>"></div></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Growth line chart -->
<div class="glass rounded-3xl p-6 mb-8">
    <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
        <i data-lucide="trending-up" style="width:16px;height:16px;color:#2563EB"></i> نمو الطلاب خلال العام
    </h3>
    <canvas id="growthChart" style="max-height:200px;"></canvas>
</div>

<!-- My Courses table -->
<div class="glass rounded-3xl overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-bold text-slate-800 flex items-center gap-2"><i data-lucide="book" style="width:17px;height:17px;color:#2563EB"></i> إدارة الكورسات</h2>
        <a href="<?php echo $base; ?>/courses/create.php" class="btn-primary-nagah inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold shadow hover:-translate-y-0.5 transition-all"><i data-lucide="plus" style="width:14px;height:14px;"></i> كورس جديد</a>
    </div>
    <?php if (empty($myCourses)): ?>
    <div class="text-center py-14 text-slate-400">
        <i data-lucide="book" style="width:44px;height:44px;" class="mx-auto mb-3 opacity-30"></i>
        <p class="text-sm mb-4">لم تقم بإنشاء أي كورس بعد</p>
        <a href="<?php echo $base; ?>/courses/create.php" class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold">أنشئ كورسك الأول</a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr style="background:rgba(37,99,235,.04)">
                <th class="px-5 py-3.5 text-right font-semibold text-slate-600">الكورس</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-600 hidden sm:table-cell">الطلاب</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-600 hidden md:table-cell">المواد</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-600 hidden md:table-cell">الأسئلة</th>
                <th class="px-5 py-3.5 text-center font-semibold text-slate-600">إجراءات</th>
            </tr></thead>
            <tbody>
            <?php foreach ($myCourses as $c): ?>
            <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white shrink-0" style="background:linear-gradient(135deg,#2563EB,#60A5FA)"><i data-lucide="book" style="width:15px;height:15px;"></i></span>
                        <div><p class="font-semibold text-slate-800"><?php echo htmlspecialchars($c['course_name']); ?></p><span class="tag-pill text-xs font-bold px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($c['course_code']); ?></span></div>
                    </div>
                </td>
                <td class="px-5 py-3.5 hidden sm:table-cell"><span class="flex items-center gap-1 text-slate-600"><i data-lucide="users" style="width:13px;height:13px;"></i><?php echo $c['student_count'] ?? 0; ?></span></td>
                <td class="px-5 py-3.5 hidden md:table-cell"><span class="flex items-center gap-1 text-slate-600"><i data-lucide="file-text" style="width:13px;height:13px;"></i><?php echo $c['material_count'] ?? 0; ?></span></td>
                <td class="px-5 py-3.5 hidden md:table-cell">
                    <span class="flex items-center gap-1 <?php echo ($c['question_count']??0) > 0 ? 'text-red-600 font-bold' : 'text-slate-600'; ?>">
                        <i data-lucide="message-circle" style="width:13px;height:13px;"></i><?php echo $c['question_count'] ?? 0; ?>
                    </span>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-center gap-2">
                        <a href="<?php echo $base; ?>/courses/view.php?id=<?php echo $c['id']; ?>" class="px-3 py-1.5 rounded-xl text-xs font-bold btn-primary-nagah">عرض</a>
                        <a href="<?php echo $base; ?>/materials/upload.php?course_id=<?php echo $c['id']; ?>" class="px-3 py-1.5 rounded-xl text-xs border border-slate-300 text-slate-600 hover:bg-slate-50 transition flex items-center gap-1"><i data-lucide="upload" style="width:11px;height:11px;"></i>رفع</a>
                        <a href="<?php echo $base; ?>/courses/manage.php?id=<?php echo $c['id']; ?>" class="p-1.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition"><i data-lucide="settings" style="width:13px;height:13px;"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Bottom row: Questions + Appointments -->
<div class="grid lg:grid-cols-2 gap-6">

    <!-- Pending questions -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="message-circle" style="width:16px;height:16px;color:#dc2626"></i> أسئلة معلقة
                <?php if ($pendingQ > 0): ?><span class="w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center"><?php echo $pendingQ; ?></span><?php endif; ?>
            </h2>
        </div>
        <?php if (empty($recentQuestions)): ?>
        <div class="text-center py-10 text-slate-400"><i data-lucide="check-circle" style="width:36px;height:36px;" class="mx-auto mb-2 opacity-30"></i><p class="text-xs">كل الأسئلة تمت الإجابة عليها 🎉</p></div>
        <?php else: ?>
        <div class="p-4 space-y-3">
            <?php foreach ($recentQuestions as $q): ?>
            <div class="p-3 rounded-2xl bg-slate-50 <?php echo $q['status'] === 'pending' ? 'border-r-4 border-red-400' : 'border-r-4 border-green-400'; ?>">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <div>
                        <p class="font-semibold text-xs text-slate-800"><?php echo htmlspecialchars($q['student_name'] ?? 'طالب'); ?></p>
                        <p class="text-xs text-slate-400"><?php echo htmlspecialchars($q['course_name'] ?? ''); ?></p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold shrink-0 <?php echo $q['status'] === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'; ?>"><?php echo $q['status'] === 'pending' ? 'معلق' : 'مجاب'; ?></span>
                </div>
                <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars(mb_substr($q['question_text'], 0, 70)); ?>…</p>
                <?php if ($q['status'] === 'pending'): ?>
                <a href="<?php echo $base; ?>/questions/answer.php?id=<?php echo $q['id']; ?>" class="text-xs font-bold text-blue-600 hover:underline mt-1.5 inline-block">رد الآن ←</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming appointments -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="calendar" style="width:16px;height:16px;color:#d97706"></i> المواعيد القادمة
            </h2>
            <a href="<?php echo $base; ?>/appointments/view.php" class="text-xs font-bold text-blue-600 hover:underline">عرض الكل</a>
        </div>
        <?php if (empty($upcomingAppointments)): ?>
        <div class="text-center py-10 text-slate-400"><i data-lucide="calendar-x" style="width:36px;height:36px;" class="mx-auto mb-2 opacity-30"></i><p class="text-xs">لا توجد مواعيد قادمة</p></div>
        <?php else: ?>
        <div class="p-4 space-y-3">
            <?php $sCfg = ['pending'=>['bg-amber-100','text-amber-700','معلق'],'confirmed'=>['bg-green-100','text-green-700','مؤكد'],'cancelled'=>['bg-red-100','text-red-700','ملغي']];
            foreach ($upcomingAppointments as $app): [$abg,$atxt,$albl] = $sCfg[$app['status']] ?? ['bg-slate-100','text-slate-600',$app['status']]; ?>
            <div class="p-3 rounded-2xl bg-slate-50 flex items-start justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold shrink-0" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)"><?php echo mb_substr($app['student_name'] ?? 'ط', 0, 1); ?></span>
                    <div class="min-w-0">
                        <p class="font-semibold text-xs text-slate-800 truncate"><?php echo htmlspecialchars($app['student_name'] ?? 'طالب'); ?></p>
                        <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5"><i data-lucide="clock" style="width:10px;height:10px;"></i><?php echo isset($app['date_time']) ? date('d/m/Y H:i', strtotime($app['date_time'])) : '—'; ?></p>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-1.5 shrink-0">
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold <?php echo "$abg $atxt"; ?>"><?php echo $albl; ?></span>
                    <?php if ($app['status'] === 'pending'): ?>
                    <div class="flex gap-1">
                        <button onclick="updateAppt(<?php echo $app['id']; ?>,'confirmed')" class="px-2 py-0.5 rounded-lg bg-green-100 text-green-700 text-xs font-bold hover:bg-green-200 transition">✓</button>
                        <button onclick="updateAppt(<?php echo $app['id']; ?>,'cancelled')" class="px-2 py-0.5 rounded-lg bg-red-100 text-red-700 text-xs font-bold hover:bg-red-200 transition">✕</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Quick actions -->
<div class="glass rounded-3xl p-6 mt-6">
    <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2 text-sm"><i data-lucide="zap" style="width:15px;height:15px;color:#d97706"></i> إجراءات سريعة</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <?php foreach ([
            [$base.'/courses/create.php',       'plus-circle', 'إنشاء كورس',    '#2563EB','rgba(37,99,235,.1)'],
            [$base.'/materials/upload.php',      'upload-cloud','رفع مادة',      '#16a34a','rgba(22,163,74,.1)'],
            [$base.'/announcements/create.php',  'megaphone',   'إعلان جديد',   '#7c3aed','rgba(124,58,237,.1)'],
            [$base.'/appointments/view.php',     'calendar',    'المواعيد',     '#d97706','rgba(217,119,6,.1)'],
        ] as [$url,$icon,$label,$color,$bg]): ?>
        <a href="<?php echo $url; ?>" class="flex flex-col items-center gap-2 p-4 rounded-2xl hover:scale-105 transition-all text-center" style="background:<?php echo $bg; ?>">
            <i data-lucide="<?php echo $icon; ?>" style="width:22px;height:22px;color:<?php echo $color; ?>"></i>
            <span class="text-xs font-bold" style="color:<?php echo $color; ?>"><?php echo $label; ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

</div></main></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const analytics = <?php echo json_encode($analytics); ?>;
const font = { family:"'Cairo','DM Sans',sans-serif", size:10 };
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('qChart'), {
        type:'doughnut',
        data:{ labels:['معلقة','مجابة'], datasets:[{ data:[analytics.questions?.pending??0, analytics.questions?.answered??0], backgroundColor:['#EF4444','#10B981'], borderWidth:0, hoverOffset:4 }] },
        options:{ cutout:'66%', plugins:{ legend:{ position:'bottom', labels:{ font, color:'#64748b', padding:10 } } } }
    });
    new Chart(document.getElementById('studChart'), {
        type:'bar',
        data:{ labels: analytics.courses.length ? analytics.courses.map(n => n.length>8?n.slice(0,8)+'…':n) : ['—'],
            datasets:[{ data: analytics.students_count.length ? analytics.students_count : [0], backgroundColor:'#2563EB', borderRadius:5, borderSkipped:false }] },
        options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font, color:'#94a3b8' } }, x:{ grid:{ display:false }, ticks:{ font, color:'#94a3b8' } } } }
    });
    new Chart(document.getElementById('growthChart'), {
        type:'line',
        data:{ labels:['يناير','فبراير','مارس','أبريل','مايو','يونيو'],
            datasets:[{ label:'طلاب جدد', data:[12,25,38,52,67,<?php echo $totalMyStudents; ?>], borderColor:'#2563EB', backgroundColor:'rgba(37,99,235,.08)', fill:true, tension:0.4, pointBackgroundColor:'#2563EB', pointRadius:4 }] },
        options:{ plugins:{ legend:{ labels:{ font, color:'#64748b' } } }, scales:{ y:{ grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font, color:'#94a3b8' } }, x:{ grid:{ display:false }, ticks:{ font, color:'#94a3b8' } } } }
    });
});
function updateAppt(id, status) {
    if (!confirm('تأكيد الإجراء؟')) return;
    fetch('<?php echo $base; ?>/api/appointments/update.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({appointment_id:id, status}) })
        .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message||'خطأ'); });
}
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
