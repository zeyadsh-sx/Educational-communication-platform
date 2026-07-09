<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/subscription_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

requireRole('admin');

$userId  = getCurrentUserId();
$base    = nagahBaseUrl();
$pdo     = getDB();
$message = '';
$msgKind = '';

// POST — approve or reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action   = $_POST['action'] ?? '';
    $subId    = (int)($_POST['sub_id'] ?? 0);
    $reason   = trim($_POST['reason'] ?? '');

    if ($action === 'approve' && $subId) {
        $sub = getSubscriptionById($subId);
        if ($sub) {
            activateSubscription($subId, $sub['plan'], $userId);
            $message = 'تم تفعيل الاشتراك بنجاح';
            $msgKind = 'success';
        }
    } elseif ($action === 'reject' && $subId) {
        rejectSubscription($subId, $reason ?: 'لم يتم قبول الإيصال', $userId);
        $message = 'تم رفض الاشتراك';
        $msgKind = 'error';
    } elseif ($action === 'expire' && $subId) {
        $pdo->prepare("UPDATE subscriptions SET status='expired' WHERE id=?")->execute([$subId]);
        $message = 'تم إنهاء الاشتراك';
        $msgKind = 'success';
    }
}

$filter = in_array($_GET['filter'] ?? '', ['pending','active','rejected','expired']) ? $_GET['filter'] : 'all';
$subs   = getAllSubscriptionsAdmin($filter);

$statusCfg = [
    'pending'   => ['bg-amber-100','text-amber-700', 'clock',        'معلق'],
    'active'    => ['bg-green-100','text-green-700', 'check-circle', 'نشط'],
    'expired'   => ['bg-slate-100','text-slate-500', 'x-circle',     'منتهي'],
    'cancelled' => ['bg-slate-100','text-slate-500', 'ban',          'ملغي'],
    'rejected'  => ['bg-red-100',  'text-red-700',   'x-circle',     'مرفوض'],
];

$planLabels = ['monthly' => 'شهري', 'quarterly' => 'ربع سنوي', 'yearly' => 'سنوي'];
$_activeSidebar = 'subscriptions';

$pageTitle = 'إدارة الاشتراكات | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_admin.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8">
<div class="max-w-6xl">

    <!-- Header -->
    <div class="mb-7">
        <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
            <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                <i data-lucide="credit-card" style="width:17px;height:17px;"></i>
            </span>
            إدارة الاشتراكات
        </h1>
    </div>

    <!-- Feedback -->
    <?php if ($message): ?>
    <div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-6 flex items-center gap-3
        <?php echo $msgKind === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i data-lucide="<?php echo $msgKind === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="flex gap-2 flex-wrap mb-6">
        <?php foreach ([
            ['all','الكل','slate'],
            ['pending','معلقة','amber'],
            ['active','نشطة','green'],
            ['rejected','مرفوضة','red'],
            ['expired','منتهية','slate'],
        ] as [$f,$lbl,$col]): ?>
        <a href="?filter=<?php echo $f; ?>"
           class="px-4 py-2 rounded-full text-sm font-bold transition-all
           <?php echo $filter === $f
               ? "bg-{$col}-100 text-{$col}-700 ring-2 ring-{$col}-300"
               : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
            <?php echo $lbl; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <?php if (empty($subs)): ?>
    <div class="glass rounded-3xl p-14 text-center text-slate-400">
        <i data-lucide="credit-card" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-30"></i>
        <p class="text-sm">لا توجد اشتراكات</p>
    </div>
    <?php else: ?>
    <div class="glass rounded-3xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:rgba(124,58,237,.04)">
                        <th class="px-5 py-4 text-right font-semibold text-slate-600">الطالب</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden sm:table-cell">المادة</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">الخطة</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden md:table-cell">الانتهاء</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600">الحالة</th>
                        <th class="px-5 py-4 text-right font-semibold text-slate-600 hidden lg:table-cell">الإيصال</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($subs as $s):
                    [$sbg, $stxt, $sicon, $slbl] = $statusCfg[$s['status']] ?? $statusCfg['pending'];
                ?>
                <tr class="border-t border-slate-50 hover:bg-slate-50/60 transition">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($s['student_name']); ?></p>
                        <?php if (!empty($s['student_phone'])): ?>
                        <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                            <i data-lucide="phone" style="width:10px;height:10px;"></i>
                            <?php echo htmlspecialchars($s['student_phone']); ?>
                        </p>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5 hidden sm:table-cell">
                        <p class="font-medium text-slate-700"><?php echo htmlspecialchars($s['course_name']); ?></p>
                        <p class="text-xs text-slate-400"><?php echo htmlspecialchars($s['professor_name']); ?></p>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <span class="tag-pill text-xs font-bold px-2.5 py-1 rounded-full">
                            <?php echo $planLabels[$s['plan']] ?? $s['plan']; ?>
                        </span>
                        <p class="text-xs text-slate-400 mt-0.5"><?php echo number_format($s['price'], 0); ?> جنيه</p>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell text-xs text-slate-500">
                        <?php echo $s['end_date'] ? date('d/m/Y', strtotime($s['end_date'])) : '—'; ?>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full <?php echo $sbg . ' ' . $stxt; ?>">
                            <i data-lucide="<?php echo $sicon; ?>" style="width:11px;height:11px;"></i>
                            <?php echo $slbl; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell">
                        <?php if (!empty($s['receipt_image'])): ?>
                        <a href="<?php echo $base . '/' . htmlspecialchars($s['receipt_image']); ?>" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:underline">
                            <i data-lucide="image" style="width:13px;height:13px;"></i> عرض
                        </a>
                        <?php else: ?>
                        <span class="text-xs text-slate-400">لا يوجد</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-2">
                            <?php if ($s['status'] === 'pending'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action"   value="approve">
                                <input type="hidden" name="sub_id"   value="<?php echo $s['id']; ?>">
                                <button type="submit" onclick="return confirm('تأكيد تفعيل الاشتراك؟')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-green-100 text-green-700 hover:bg-green-200 text-xs font-bold transition">
                                    <i data-lucide="check" style="width:12px;height:12px;"></i> قبول
                                </button>
                            </form>
                            <button onclick="showRejectModal(<?php echo $s['id']; ?>)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-100 text-red-700 hover:bg-red-200 text-xs font-bold transition">
                                <i data-lucide="x" style="width:12px;height:12px;"></i> رفض
                            </button>
                            <?php elseif ($s['status'] === 'active'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action"  value="expire">
                                <input type="hidden" name="sub_id"  value="<?php echo $s['id']; ?>">
                                <button type="submit" onclick="return confirm('إنهاء الاشتراك؟')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-xs font-bold transition">
                                    <i data-lucide="ban" style="width:12px;height:12px;"></i> إنهاء
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-xs text-slate-400">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
</main>
</div>

<!-- Reject modal -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,.6);backdrop-filter:blur(6px)">
    <div class="glass rounded-3xl w-full max-w-md p-7">
        <h3 class="font-bold text-slate-800 mb-4">سبب الرفض</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action"  value="reject">
            <input type="hidden" name="sub_id"  id="reject-sub-id" value="">
            <textarea name="reason" rows="4" class="field-input resize-none mb-4 w-full" placeholder="اكتب سبب الرفض للطالب…"></textarea>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-2.5 rounded-full bg-red-600 text-white font-bold text-sm hover:bg-red-700 transition">تأكيد الرفض</button>
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-2.5 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(id) {
    document.getElementById('reject-sub-id').value = id;
    const m = document.getElementById('rejectModal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeRejectModal() {
    const m = document.getElementById('rejectModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
