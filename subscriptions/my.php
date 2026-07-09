<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

requireRole('student');

$userId = getCurrentUserId();
$base   = nagahBaseUrl();
$pdo    = getDB();

$subs   = getStudentSubscriptions($userId);
$today  = new DateTime();

$statusCfg = [
    'pending'   => ['bg-amber-100',  'text-amber-700',  'clock',         'في انتظار التفعيل'],
    'active'    => ['bg-green-100',  'text-green-700',  'check-circle',  'نشط'],
    'expired'   => ['bg-slate-100',  'text-slate-600',  'x-circle',      'منتهي'],
    'cancelled' => ['bg-slate-100',  'text-slate-600',  'ban',           'ملغي'],
    'rejected'  => ['bg-red-100',    'text-red-700',    'x-circle',      'مرفوض'],
];

$planLabels = ['monthly' => 'شهري', 'quarterly' => 'ربع سنوي', 'yearly' => 'سنوي'];

$pageTitle = 'اشتراكاتي | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<section class="relative overflow-hidden py-10" style="background:linear-gradient(135deg,#1e3a8a,#2563EB)">
    <div class="absolute inset-0 grid-dots opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-5">
        <a href="<?php echo $base; ?>/student/dashboard.php" class="inline-flex items-center gap-2 text-white/70 hover:text-white text-sm mb-3 transition">
            <i data-lucide="arrow-right" style="width:15px;height:15px;"></i> لوحة التحكم
        </a>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="display font-semibold text-2xl sm:text-3xl text-white">اشتراكاتي</h1>
                <p class="text-white/70 mt-1 text-sm"><?php echo count($subs); ?> اشتراك مسجل</p>
            </div>
            <a href="<?php echo $base; ?>/subscriptions/subscribe.php"
               class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 backdrop-blur text-white px-5 py-2.5 rounded-full text-sm font-bold transition">
                <i data-lucide="plus" style="width:15px;height:15px;"></i> اشتراك جديد
            </a>
        </div>
    </div>
</section>

<main class="max-w-5xl mx-auto px-5 py-10 pb-20">

    <?php if (empty($subs)): ?>
    <div class="glass rounded-3xl p-16 text-center">
        <i data-lucide="credit-card" style="width:56px;height:56px;" class="mx-auto mb-4 text-slate-300 opacity-50"></i>
        <h3 class="font-bold text-slate-500 text-lg">لا توجد اشتراكات بعد</h3>
        <p class="text-slate-400 text-sm mt-2 mb-6">اشترك في مادة لتبدأ دراستك</p>
        <a href="<?php echo $base; ?>/subscriptions/subscribe.php"
           class="btn-primary-nagah inline-flex items-center gap-2 px-7 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
            <i data-lucide="book-open" style="width:16px;height:16px;"></i> تصفح المواد
        </a>
    </div>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 gap-5">
        <?php foreach ($subs as $s):
            $cfg = $statusCfg[$s['status']] ?? $statusCfg['pending'];
            [$sbg, $stxt, $sicon, $slbl] = $cfg;
            $paymentStatus = $s['payment_status'] ?? null;

            // Days remaining
            $daysLeft = null;
            if ($s['end_date'] && $s['status'] === 'active') {
                $end = new DateTime($s['end_date']);
                $daysLeft = (int)$today->diff($end)->days * ($end >= $today ? 1 : -1);
            }

            $isExpiringSoon = $daysLeft !== null && $daysLeft <= 7 && $daysLeft >= 0;
        ?>
        <article class="glass rounded-3xl overflow-hidden flex flex-col reveal <?php echo $isExpiringSoon ? 'ring-2 ring-amber-400' : ''; ?>">
            <!-- Header -->
            <div class="h-14 flex items-center px-5 gap-3" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="book" style="width:18px;height:18px;color:white;opacity:.8;"></i>
                <span class="font-bold text-white text-sm truncate flex-1"><?php echo htmlspecialchars($s['course_name']); ?></span>
                <span class="text-white/70 text-xs shrink-0"><?php echo htmlspecialchars($s['course_code']); ?></span>
            </div>

            <div class="p-5 flex flex-col flex-1 gap-4">
                <!-- Status row -->
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full <?php echo $sbg . ' ' . $stxt; ?>">
                        <i data-lucide="<?php echo $sicon; ?>" style="width:12px;height:12px;"></i>
                        <?php echo $slbl; ?>
                    </span>
                    <span class="text-xs text-slate-400"><?php echo $planLabels[$s['plan']] ?? $s['plan']; ?> — <?php echo number_format($s['price'], 0); ?> جنيه</span>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-slate-400 mb-0.5">تاريخ الاشتراك</p>
                        <p class="font-semibold text-slate-700"><?php echo $s['start_date'] ? date('d/m/Y', strtotime($s['start_date'])) : '—'; ?></p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 <?php echo $isExpiringSoon ? 'bg-amber-50' : ''; ?>">
                        <p class="text-slate-400 mb-0.5">تاريخ الانتهاء</p>
                        <p class="font-semibold <?php echo $isExpiringSoon ? 'text-amber-700' : 'text-slate-700'; ?>">
                            <?php echo $s['end_date'] ? date('d/m/Y', strtotime($s['end_date'])) : '—'; ?>
                        </p>
                    </div>
                </div>

                <!-- Expiry warning -->
                <?php if ($isExpiringSoon): ?>
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 flex items-center gap-2 text-xs text-amber-700">
                    <i data-lucide="alert-triangle" style="width:13px;height:13px;flex-shrink:0"></i>
                    ينتهي الاشتراك خلال <strong><?php echo $daysLeft; ?></strong> أيام
                </div>
                <?php endif; ?>

                <!-- Payment status -->
                <?php if ($paymentStatus): ?>
                <div class="flex items-center gap-2 text-xs">
                    <span class="font-medium text-slate-500">الدفع:</span>
                    <?php if ($paymentStatus === 'approved'): ?>
                    <span class="text-green-600 font-bold flex items-center gap-1">
                        <i data-lucide="check-circle" style="width:12px;height:12px;"></i> مؤكد
                    </span>
                    <?php elseif ($paymentStatus === 'pending'): ?>
                    <span class="text-amber-600 font-bold flex items-center gap-1">
                        <i data-lucide="clock" style="width:12px;height:12px;"></i> قيد المراجعة
                    </span>
                    <?php elseif ($paymentStatus === 'rejected'): ?>
                    <span class="text-red-600 font-bold flex items-center gap-1">
                        <i data-lucide="x-circle" style="width:12px;height:12px;"></i> مرفوض
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="flex gap-2 mt-auto">
                    <?php if ($s['status'] === 'pending' && !$s['receipt_id']): ?>
                    <a href="<?php echo $base; ?>/payments/upload.php?sub_id=<?php echo $s['id']; ?>"
                       class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold btn-primary-nagah flex items-center justify-center gap-1.5">
                        <i data-lucide="upload" style="width:13px;height:13px;"></i> ارفع الإيصال
                    </a>
                    <?php elseif ($s['status'] === 'active'): ?>
                    <a href="<?php echo $base; ?>/courses/list.php"
                       class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold btn-primary-nagah flex items-center justify-center gap-1.5">
                        <i data-lucide="book-open" style="width:13px;height:13px;"></i> ادخل الكورس
                    </a>
                    <?php elseif (in_array($s['status'], ['expired','cancelled','rejected'])): ?>
                    <a href="<?php echo $base; ?>/subscriptions/subscribe.php"
                       class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold border-2 border-blue-600 text-blue-600 hover:bg-blue-50 transition flex items-center justify-center gap-1.5">
                        <i data-lucide="refresh-cw" style="width:13px;height:13px;"></i> تجديد الاشتراك
                    </a>
                    <?php elseif ($s['status'] === 'pending' && $s['receipt_id']): ?>
                    <span class="flex-1 text-center py-2.5 rounded-xl text-sm font-medium bg-amber-50 text-amber-700 flex items-center justify-center gap-1.5">
                        <i data-lucide="clock" style="width:13px;height:13px;"></i> في انتظار الموافقة
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
