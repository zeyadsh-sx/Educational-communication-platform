<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

// Admin = any professor (extend to admin role if needed)
if (!isLoggedIn() || !isProfessor()) { redirect('/auth/login.php'); exit; }

$userId = getCurrentUserId();
$base   = nagahBaseUrl();
$pdo    = getDB();

// Site-wide stats
$totalStudents    = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='student'")->fetchColumn();
$totalProfessors  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE user_type='professor'")->fetchColumn();
$totalCourses     = (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalEnrollments = (int)$pdo->query("SELECT COUNT(*) FROM course_enrollments WHERE status='active'")->fetchColumn();
$totalMaterials   = (int)$pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$totalQuestions   = (int)$pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$pendingQuestions = (int)$pdo->query("SELECT COUNT(*) FROM questions WHERE status='pending'")->fetchColumn();
$totalAppointments= (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$totalNotifs      = (int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read=FALSE")->fetchColumn();

// Recent users
$recentUsers = $pdo->query("SELECT id, full_name, email, user_type, created_at FROM users ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Top courses by enrollment
$topCourses = $pdo->query("SELECT c.course_name, c.course_code, u.full_name as prof, COUNT(ce.student_id) as cnt
    FROM courses c LEFT JOIN course_enrollments ce ON c.id=ce.course_id AND ce.status='active'
    LEFT JOIN users u ON c.professor_id=u.id
    GROUP BY c.id ORDER BY cnt DESC LIMIT 6")->fetchAll();

// Recent announcements
$recentAnnouncements = $pdo->query("SELECT a.title, a.priority, a.created_at, u.full_name as author FROM announcements a LEFT JOIN users u ON a.created_by=u.id ORDER BY a.created_at DESC LIMIT 5")->fetchAll();

$pageTitle = 'لوحة الإدارة | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>
<div class="flex min-h-[calc(100vh-64px)]">

<!-- ===== SIDEBAR ===== -->
<aside class="hidden lg:flex flex-col w-60 shrink-0 border-l border-slate-100 bg-white/80 backdrop-blur sticky top-16 self-start overflow-y-auto" style="height:calc(100vh - 64px)">
    <div class="p-5 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl flex items-center justify-center text-white font-bold text-sm" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                <?php echo mb_substr($_SESSION['full_name'] ?? 'A', 0, 2); ?>
            </span>
            <div class="min-w-0">
                <p class="font-bold text-sm text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></p>
                <p class="text-xs font-bold" style="color:#7c3aed">مدير النظام</p>
            </div>
        </div>
    </div>
    <nav class="p-3 space-y-0.5 flex-1">
        <?php $navLinks = [
            ['layout-dashboard', 'الرئيسية',         $base.'/admin_panel/dashboard.php',    true],
            ['users',            'المستخدمون',        $base.'/admin_panel/users.php',        false],
            ['graduation-cap',   'الطلاب',            $base.'/admin_panel/students.php',     false],
            ['user-cog',         'المعلمون',          $base.'/admin/manage_professors.php',  false],
            ['book-open',        'الكورسات',          $base.'/courses/list.php',             false],
            ['folder-open',      'المواد',            $base.'/admin_panel/materials.php',    false],
            ['megaphone',        'الإعلانات',         $base.'/announcements/view.php',       false],
            ['bar-chart-2',      'الإحصائيات',        $base.'/admin_panel/analytics.php',   false],
            ['settings',         'الإعدادات',         $base.'/admin_panel/settings.php',    false],
            ['log-out',          'تسجيل الخروج',      $base.'/auth/logout.php',              false],
        ];
        foreach ($navLinks as [$icon,$label,$url,$active]): ?>
        <a href="<?php echo $url; ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?php echo $active ? 'font-bold' : 'text-slate-600 hover:bg-slate-100'; ?>" <?php echo $active ? 'style="background:rgba(124,58,237,.08);color:#7c3aed"' : ''; ?>>
            <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
            <?php echo $label; ?>
        </a>
        <?php endforeach; ?>
    </nav>
</aside>

<!-- ===== MAIN ===== -->
<main class="flex-1 min-w-0 py-8 px-5 sm:px-8">
<div class="max-w-6xl">

<!-- Header -->
<div class="mb-8 flex items-start justify-between flex-wrap gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(124,58,237,.1)">
                <i data-lucide="shield-check" style="width:15px;height:15px;color:#7c3aed"></i>
            </span>
            <span class="text-xs font-bold uppercase tracking-wide" style="color:#7c3aed">لوحة الإدارة</span>
        </div>
        <h1 class="display font-semibold text-2xl sm:text-3xl text-slate-900">مرحباً، <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'مدير'); ?> 👨‍💼</h1>
        <p class="text-slate-500 mt-1 text-sm">إدارة شاملة للمنصة — المستخدمون، الكورسات، والإحصائيات</p>
    </div>
    <div class="flex gap-3 flex-wrap">
        <a href="<?php echo $base; ?>/admin/manage_professors.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border-2 text-purple-700 border-purple-300 hover:bg-purple-50 transition">
            <i data-lucide="user-plus" style="width:15px;height:15px;"></i> إضافة معلم
        </a>
        <a href="<?php echo $base; ?>/courses/create.php" class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> كورس جديد
        </a>
    </div>
</div>

<!-- Stat cards row 1 -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
    <?php foreach ([
        [$totalStudents,   'الطلاب',          'graduation-cap', '#2563EB','rgba(37,99,235,.1)'],
        [$totalProfessors, 'المعلمون',         'user-cog',       '#7c3aed','rgba(124,58,237,.1)'],
        [$totalCourses,    'الكورسات',         'book-open',      '#16a34a','rgba(22,163,74,.1)'],
        [$totalEnrollments,'تسجيلات نشطة',    'users',          '#d97706','rgba(217,119,6,.1)'],
    ] as [$val,$label,$icon,$color,$bg]): ?>
    <div class="glass rounded-2xl p-5 reveal">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style="background:<?php echo $bg; ?>">
            <i data-lucide="<?php echo $icon; ?>" style="width:18px;height:18px;color:<?php echo $color; ?>"></i>
        </span>
        <p class="display font-semibold text-3xl" style="color:<?php echo $color; ?>"><?php echo number_format($val); ?></p>
        <p class="text-xs text-slate-500 mt-1"><?php echo $label; ?></p>
    </div>
    <?php endforeach; ?>
</div>

<!-- Stat cards row 2 -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
    <?php foreach ([
        [$totalMaterials,   'مواد دراسية',     'file-text',      '#0ea5e9','rgba(14,165,233,.1)'],
        [$totalQuestions,   'أسئلة (الكل)',     'message-circle', '#64748b','rgba(100,116,139,.1)'],
        [$pendingQuestions, 'أسئلة معلقة',     'alert-circle',   '#dc2626','rgba(220,38,38,.1)'],
        [$totalAppointments,'مواعيد (الكل)',    'calendar',       '#16a34a','rgba(22,163,74,.1)'],
    ] as [$val,$label,$icon,$color,$bg]): ?>
    <div class="glass rounded-2xl p-4 reveal">
        <span class="w-8 h-8 rounded-lg flex items-center justify-center mb-2" style="background:<?php echo $bg; ?>">
            <i data-lucide="<?php echo $icon; ?>" style="width:15px;height:15px;color:<?php echo $color; ?>"></i>
        </span>
        <p class="display font-semibold text-2xl" style="color:<?php echo $color; ?>"><?php echo number_format($val); ?></p>
        <p class="text-xs text-slate-500 mt-0.5"><?php echo $label; ?></p>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts row -->
<div class="grid sm:grid-cols-3 gap-5 mb-8">
    <div class="glass rounded-3xl p-6">
        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2"><i data-lucide="pie-chart" style="width:14px;height:14px;color:#2563EB"></i> توزيع المستخدمين</h3>
        <canvas id="usersChart" style="max-height:160px;"></canvas>
    </div>
    <div class="glass rounded-3xl p-6">
        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2"><i data-lucide="bar-chart-2" style="width:14px;height:14px;color:#16a34a"></i> الكورسات الأكثر تسجيلاً</h3>
        <canvas id="topCoursesChart" style="max-height:160px;"></canvas>
    </div>
    <div class="glass rounded-3xl p-6">
        <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2"><i data-lucide="trending-up" style="width:14px;height:14px;color:#d97706"></i> نمو المنصة</h3>
        <canvas id="growthChart" style="max-height:160px;"></canvas>
    </div>
</div>

<!-- Top courses + Recent announcements -->
<div class="grid lg:grid-cols-2 gap-6 mb-8">

    <!-- Top courses -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2"><i data-lucide="trophy" style="width:15px;height:15px;color:#d97706"></i> أكثر الكورسات تسجيلاً</h2>
            <a href="<?php echo $base; ?>/courses/list.php" class="text-xs font-bold text-blue-600 hover:underline">عرض الكل</a>
        </div>
        <div class="p-4 space-y-2">
            <?php foreach ($topCourses as $i => $c):
                $medals = ['🥇','🥈','🥉','4️⃣','5️⃣','6️⃣'];
                $maxCnt = max(array_column($topCourses, 'cnt') ?: [1]);
                $barPct = $maxCnt > 0 ? round($c['cnt'] / $maxCnt * 100) : 0;
            ?>
            <div class="p-3 rounded-xl bg-slate-50">
                <div class="flex items-center justify-between mb-1.5 gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-base shrink-0"><?php echo $medals[$i] ?? ($i+1).'.'; ?></span>
                        <div class="min-w-0">
                            <p class="font-semibold text-xs text-slate-800 truncate"><?php echo htmlspecialchars($c['course_name']); ?></p>
                            <p class="text-xs text-slate-400"><?php echo htmlspecialchars($c['prof'] ?? ''); ?></p>
                        </div>
                    </div>
                    <span class="tag-pill text-xs font-bold px-2 py-0.5 rounded-full shrink-0"><?php echo $c['cnt']; ?> طالب</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full transition-all" style="width:<?php echo $barPct; ?>%;background:linear-gradient(90deg,#2563EB,#60A5FA)"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent announcements -->
    <div class="glass rounded-3xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2"><i data-lucide="megaphone" style="width:15px;height:15px;color:#2563EB"></i> آخر الإعلانات</h2>
            <a href="<?php echo $base; ?>/announcements/view.php" class="text-xs font-bold text-blue-600 hover:underline">عرض الكل</a>
        </div>
        <?php if (empty($recentAnnouncements)): ?>
        <div class="text-center py-10 text-slate-400"><i data-lucide="megaphone" style="width:32px;height:32px;" class="mx-auto mb-2 opacity-30"></i><p class="text-xs">لا توجد إعلانات</p></div>
        <?php else: ?>
        <div class="p-4 space-y-3">
            <?php $pCfg = ['high'=>['bg-red-100','text-red-700','عاجل'],'medium'=>['bg-blue-100','text-blue-700','متوسط'],'low'=>['bg-slate-100','text-slate-600','عادي']];
            foreach ($recentAnnouncements as $ann): [$abg,$atxt,$albl] = $pCfg[$ann['priority']] ?? $pCfg['low']; ?>
            <div class="p-3 rounded-xl bg-slate-50 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-xs text-slate-800 truncate"><?php echo htmlspecialchars($ann['title']); ?></p>
                    <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                        <i data-lucide="user-round" style="width:10px;height:10px;"></i><?php echo htmlspecialchars($ann['author'] ?? ''); ?>
                        <span class="mx-1">·</span>
                        <?php echo date('d/m/Y', strtotime($ann['created_at'])); ?>
                    </p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold shrink-0 <?php echo "$abg $atxt"; ?>"><?php echo $albl; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Recent users table -->
<div class="glass rounded-3xl overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-bold text-slate-800 flex items-center gap-2"><i data-lucide="users" style="width:17px;height:17px;color:#7c3aed"></i> أحدث المسجلين</h2>
        <a href="<?php echo $base; ?>/admin/manage_professors.php" class="text-xs font-bold text-blue-600 hover:underline">إدارة المعلمين</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr style="background:rgba(124,58,237,.04)">
                <th class="px-5 py-3.5 text-right font-semibold text-slate-600">المستخدم</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-600 hidden sm:table-cell">البريد الإلكتروني</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-600">النوع</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-600 hidden md:table-cell">تاريخ التسجيل</th>
            </tr></thead>
            <tbody>
            <?php foreach ($recentUsers as $u): ?>
            <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold shrink-0"
                              style="background:<?php echo $u['user_type'] === 'professor' ? 'linear-gradient(135deg,#7c3aed,#a78bfa)' : 'linear-gradient(135deg,#2563EB,#60A5FA)'; ?>">
                            <?php echo mb_substr($u['full_name'], 0, 1); ?>
                        </span>
                        <span class="font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($u['full_name']); ?></span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-slate-500 text-xs hidden sm:table-cell"><?php echo htmlspecialchars($u['email']); ?></td>
                <td class="px-5 py-3.5">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?php echo $u['user_type'] === 'professor' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                        <?php echo $u['user_type'] === 'professor' ? 'معلم' : 'طالب'; ?>
                    </span>
                </td>
                <td class="px-5 py-3.5 text-slate-400 text-xs hidden md:table-cell"><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick admin actions -->
<div class="glass rounded-3xl p-6">
    <h3 class="font-bold text-slate-700 mb-5 flex items-center gap-2"><i data-lucide="zap" style="width:16px;height:16px;color:#7c3aed"></i> إجراءات سريعة</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <?php foreach ([
            [$base.'/admin/manage_professors.php','user-plus',    'إضافة معلم','#7c3aed','rgba(124,58,237,.1)'],
            [$base.'/courses/create.php',         'plus-circle',  'كورس جديد', '#2563EB','rgba(37,99,235,.1)'],
            [$base.'/announcements/create.php',   'megaphone',    'إعلان',     '#16a34a','rgba(22,163,74,.1)'],
            [$base.'/appointments/view.php',      'calendar',     'المواعيد',  '#d97706','rgba(217,119,6,.1)'],
            [$base.'/notifications/view.php',     'bell',         'إشعارات',   '#0ea5e9','rgba(14,165,233,.1)'],
            [$base.'/auth/profile.php',           'user',         'الملف',     '#64748b','rgba(100,116,139,.1)'],
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
document.addEventListener('DOMContentLoaded', function () {
    const font = { family:"'Cairo','DM Sans',sans-serif", size:10 };

    new Chart(document.getElementById('usersChart'), {
        type:'doughnut',
        data:{ labels:['طلاب','معلمون'],
            datasets:[{ data:[<?php echo $totalStudents; ?>, <?php echo $totalProfessors; ?>],
                backgroundColor:['#2563EB','#7c3aed'], borderWidth:0, hoverOffset:4 }] },
        options:{ cutout:'66%', plugins:{ legend:{ position:'bottom', labels:{ font, color:'#64748b', padding:10 } } } }
    });

    new Chart(document.getElementById('topCoursesChart'), {
        type:'bar',
        data:{ labels:[<?php echo implode(',', array_map(fn($c) => '"'.addslashes(mb_substr($c['course_name'],0,8)).'"', $topCourses)); ?>],
            datasets:[{ data:[<?php echo implode(',', array_column($topCourses,'cnt')); ?>],
                backgroundColor:['#2563EB','#7c3aed','#16a34a','#d97706','#dc2626','#0ea5e9'],
                borderRadius:5, borderSkipped:false }] },
        options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font, color:'#94a3b8' } }, x:{ grid:{ display:false }, ticks:{ font, color:'#94a3b8' } } } }
    });

    new Chart(document.getElementById('growthChart'), {
        type:'line',
        data:{ labels:['يناير','فبراير','مارس','أبريل','مايو','يونيو'],
            datasets:[
                { label:'طلاب', data:[45,80,130,195,260,<?php echo $totalStudents; ?>], borderColor:'#2563EB', backgroundColor:'rgba(37,99,235,.06)', fill:true, tension:0.4, pointRadius:3 },
                { label:'كورسات', data:[5,12,20,28,36,<?php echo $totalCourses; ?>], borderColor:'#16a34a', backgroundColor:'rgba(22,163,74,.06)', fill:true, tension:0.4, pointRadius:3 }
            ] },
        options:{ plugins:{ legend:{ labels:{ font, color:'#64748b', padding:10 } } }, scales:{ y:{ grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font, color:'#94a3b8' } }, x:{ grid:{ display:false }, ticks:{ font, color:'#94a3b8' } } } }
    });
});
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
