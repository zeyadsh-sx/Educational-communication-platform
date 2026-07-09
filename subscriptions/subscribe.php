<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/course_functions.php';
require_once __DIR__ . '/../includes/subscription_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isStudent()) { redirect('/auth/login.php'); exit; }

$userId  = getCurrentUserId();
$base    = nagahBaseUrl();
$pdo     = getDB();
$message = '';
$msgKind = '';
$newSubId = null;

// POST — إنشاء اشتراك
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = (int)($_POST['course_id'] ?? 0);
    $plan     = in_array($_POST['plan'] ?? '', ['monthly','quarterly','yearly']) ? $_POST['plan'] : 'monthly';

    if (!$courseId) {
        $message = 'اختر مادة أولاً'; $msgKind = 'error';
    } else {
        $result = createSubscription($userId, $courseId, $plan);
        if ($result['success']) {
            $newSubId = $result['id'];
            $message  = 'تم إنشاء طلب الاشتراك بنجاح! ارفع إيصال الدفع للتفعيل.';
            $msgKind  = 'success';
        } else {
            $message = $result['message']; $msgKind = 'error';
        }
    }
}

// جلب الكورسات المتاحة مع أسعارها
$coursesStmt = $pdo->query("
    SELECT c.id, c.course_name, c.course_code, u.full_name AS professor_name,
           cp.monthly_price, cp.quarterly_price, cp.yearly_price
    FROM courses c
    JOIN users u ON c.professor_id = u.id
    LEFT JOIN course_pricing cp ON cp.course_id = c.id
    ORDER BY c.course_name
");
$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

// الاشتراكات النشطة للطالب
$activeSubIds = [];
$activeSubs = $pdo->prepare("SELECT course_id FROM subscriptions WHERE student_id=? AND status IN ('active','pending')");
$activeSubs->execute([$userId]);
foreach ($activeSubs->fetchAll(PDO::FETCH_COLUMN) as $cid) $activeSubIds[] = (int)$cid;

$pageTitle = 'الاشتراك في مادة | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<!-- Hero -->
<section class="relative overflow-hidden py-10 sm:py-12" style="background:linear-gradient(135deg,#1e3a8a,#2563EB,#3b82f6)">
    <div class="absolute inset-0 grid-dots opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-5">
        <a href="<?php echo $base; ?>/student/dashboard.php" class="inline-flex items-center gap-2 text-white/70 hover:text-white text-sm mb-4 transition">
            <i data-lucide="arrow-right" style="width:15px;height:15px;"></i> لوحة التحكم
        </a>
        <h1 class="display font-semibold text-2xl sm:text-3xl text-white">اشترك في مادة دراسية</h1>
        <p class="text-white/70 mt-1 text-sm">اختر المادة والخطة المناسبة لك، ثم ارفع إيصال الدفع</p>
    </div>
</section>

<main class="max-w-5xl mx-auto px-5 py-10 pb-20">

    <?php if ($message): ?>
    <div class="rounded-2xl px-5 py-4 text-sm font-medium mb-7 flex items-center gap-3
        <?php echo $msgKind === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i data-lucide="<?php echo $msgKind === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width:18px;height:18px;flex-shrink:0"></i>
        <span><?php echo htmlspecialchars($message); ?></span>
        <?php if ($newSubId): ?>
        <a href="<?php echo $base; ?>/payments/upload.php?sub_id=<?php echo $newSubId; ?>"
           class="mr-auto btn-primary-nagah px-4 py-2 rounded-full text-sm font-bold flex items-center gap-1.5">
            <i data-lucide="upload" style="width:14px;height:14px;"></i> ارفع الإيصال
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Plan explainer -->
    <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <?php foreach ([
            ['monthly',   'شهري',   'clock',    '30 يوم',   '#2563EB','rgba(37,99,235,.07)'],
            ['quarterly', 'ربع سنوي','calendar', '3 أشهر',  '#16a34a','rgba(22,163,74,.07)'],
            ['yearly',    'سنوي',   'star',     '12 شهر',   '#d97706','rgba(217,119,6,.07)'],
        ] as [$val,$lbl,$icon,$dur,$col,$bg]): ?>
        <label class="plan-label cursor-pointer">
            <input type="radio" name="plan_select" value="<?php echo $val; ?>" class="sr-only peer"
                   <?php echo $val === 'monthly' ? 'checked' : ''; ?> onchange="setPlan('<?php echo $val; ?>')">
            <div class="rounded-2xl border-2 p-4 text-center transition-all
                peer-checked:border-blue-600 peer-checked:bg-blue-50 border-slate-200 hover:border-blue-300"
                 id="plan-card-<?php echo $val; ?>">
                <span class="w-9 h-9 rounded-xl mx-auto flex items-center justify-center mb-2" style="background:<?php echo $bg; ?>">
                    <i data-lucide="<?php echo $icon; ?>" style="width:17px;height:17px;color:<?php echo $col; ?>"></i>
                </span>
                <p class="font-bold text-slate-800 text-sm"><?php echo $lbl; ?></p>
                <p class="text-xs text-slate-400 mt-0.5"><?php echo $dur; ?></p>
            </div>
        </label>
        <?php endforeach; ?>
    </div>

    <!-- Courses grid -->
    <?php if (empty($courses)): ?>
    <div class="glass rounded-3xl p-14 text-center text-slate-400">
        <i data-lucide="book-open" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-30"></i>
        <p>لا توجد مواد متاحة حالياً</p>
    </div>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5" id="courses-grid">
        <?php foreach ($courses as $c):
            $hasActive  = in_array((int)$c['id'], $activeSubIds);
            $hasPrice   = !empty($c['monthly_price']) && $c['monthly_price'] > 0;
        ?>
        <div class="glass rounded-3xl overflow-hidden flex flex-col course-card
            <?php echo $hasActive ? 'opacity-75' : 'hover:shadow-xl'; ?> transition-all">
            <!-- header gradient -->
            <div class="h-20 flex items-center justify-center" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="book" style="width:30px;height:30px;color:white;opacity:.85;"></i>
            </div>
            <div class="p-5 flex flex-col flex-1">
                <span class="tag-pill text-xs font-bold px-2.5 py-1 rounded-full w-fit mb-2">
                    <?php echo htmlspecialchars($c['course_code']); ?>
                </span>
                <h3 class="font-bold text-slate-900 text-base leading-snug mb-1">
                    <?php echo htmlspecialchars($c['course_name']); ?>
                </h3>
                <p class="text-xs text-slate-500 flex items-center gap-1 mb-3">
                    <i data-lucide="user-round" style="width:12px;height:12px;"></i>
                    <?php echo htmlspecialchars($c['professor_name']); ?>
                </p>

                <!-- Prices -->
                <?php if ($hasPrice): ?>
                <div class="space-y-1 mb-4 text-xs text-slate-600">
                    <?php foreach ([
                        ['monthly',   'شهري',    $c['monthly_price']],
                        ['quarterly', 'ربع سنوي',$c['quarterly_price']],
                        ['yearly',    'سنوي',    $c['yearly_price']],
                    ] as [$p,$pl,$pr]): if ($pr > 0): ?>
                    <div class="flex justify-between px-2 py-1 rounded-lg bg-slate-50">
                        <span><?php echo $pl; ?></span>
                        <span class="font-bold" style="color:#2563EB"><?php echo number_format($pr, 0); ?> جنيه</span>
                    </div>
                    <?php endif; endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-xs text-slate-400 mb-4">السعر غير محدد — تواصل مع الإدارة</p>
                <?php endif; ?>

                <div class="mt-auto">
                <?php if ($hasActive): ?>
                    <span class="w-full block text-center py-2.5 rounded-xl text-xs font-bold bg-green-100 text-green-700">
                        <i data-lucide="check-circle" style="width:13px;height:13px;display:inline;vertical-align:middle;margin-left:4px;"></i>
                        مشترك / قيد المراجعة
                    </span>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                        <input type="hidden" name="plan" class="plan-input" value="monthly">
                        <button type="submit"
                                class="w-full py-2.5 rounded-xl text-sm font-bold btn-primary-nagah hover:-translate-y-0.5 transition-all">
                            اشترك الآن
                        </button>
                    </form>
                <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Steps -->
    <div class="mt-12 glass rounded-3xl p-6">
        <h3 class="font-bold text-slate-700 mb-5 flex items-center gap-2">
            <i data-lucide="list-checks" style="width:17px;height:17px;color:#2563EB"></i> كيف يعمل الاشتراك؟
        </h3>
        <div class="grid sm:grid-cols-4 gap-4">
            <?php foreach ([
                ['1','اختر المادة','book-open','#2563EB'],
                ['2','اختر الخطة','calendar','#16a34a'],
                ['3','ارفع الإيصال','upload-cloud','#d97706'],
                ['4','انتظر التفعيل','check-circle','#7c3aed'],
            ] as [$n,$lbl,$icon,$col]): ?>
            <div class="flex flex-col items-center text-center gap-2">
                <span class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm"
                      style="background:<?php echo $col; ?>"><?php echo $n; ?></span>
                <i data-lucide="<?php echo $icon; ?>" style="width:20px;height:20px;color:<?php echo $col; ?>"></i>
                <p class="text-xs font-semibold text-slate-700"><?php echo $lbl; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
function setPlan(val) {
    document.querySelectorAll('.plan-input').forEach(i => i.value = val);
}
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
