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

// POST — approve / reject receipt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action    = $_POST['action']  ?? '';
    $receiptId = (int)($_POST['receipt_id'] ?? 0);
    $reason    = trim($_POST['reason'] ?? '');

    if ($receiptId > 0) {
        // fetch receipt → get subscription id
        $rec = $pdo->prepare("SELECT * FROM payment_receipts WHERE id=?");
        $rec->execute([$receiptId]);
        $receipt = $rec->fetch();

        if ($receipt) {
            if ($action === 'approve') {
                $sub = getSubscriptionById((int)$receipt['subscription_id']);
                if ($sub) {
                    activateSubscription((int)$receipt['subscription_id'], $sub['plan'], $userId);
                    $message = 'تم قبول الإيصال وتفعيل الاشتراك ✅';
                    $msgKind = 'success';
                }
            } elseif ($action === 'reject') {
                rejectSubscription((int)$receipt['subscription_id'], $reason ?: 'الإيصال غير مقبول', $userId);
                $message = 'تم رفض الإيصال وإشعار الطالب';
                $msgKind = 'error';
            }
        }
    }
}

// Filters
$filter = in_array($_GET['filter'] ?? '', ['pending','approved','rejected']) ? $_GET['filter'] : 'all';

$whereSQL = $filter !== 'all' ? "WHERE pr.status = '$filter'" : '';

$receipts = $pdo->query("
    SELECT pr.*,
           u.full_name  AS student_name,
           u.phone      AS student_phone,
           c.course_name,
           s.plan, s.price, s.status AS sub_status
    FROM   payment_receipts pr
    JOIN   users u ON pr.student_id = u.id
    JOIN   subscriptions s ON pr.subscription_id = s.id
    JOIN   courses c ON s.course_id = c.id
    $whereSQL
    ORDER  BY pr.created_at DESC
")->fetchAll();

// Counts for filter badges
$pendingCount   = (int)$pdo->query("SELECT COUNT(*) FROM payment_receipts WHERE status='pending'")->fetchColumn();
$approvedCount  = (int)$pdo->query("SELECT COUNT(*) FROM payment_receipts WHERE status='approved'")->fetchColumn();
$rejectedCount  = (int)$pdo->query("SELECT COUNT(*) FROM payment_receipts WHERE status='rejected'")->fetchColumn();
$totalRevenue   = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payment_receipts WHERE status='approved'")->fetchColumn();

$statusCfg = [
    'pending'  => ['bg-amber-100', 'text-amber-700',  'clock',        'في الانتظار'],
    'approved' => ['bg-green-100', 'text-green-700',  'check-circle', 'مقبول'],
    'rejected' => ['bg-red-100',   'text-red-700',    'x-circle',     'مرفوض'],
];
$planLabels = ['monthly' => 'شهري', 'quarterly' => 'ربع سنوي', 'yearly' => 'سنوي'];

$_activeSidebar = 'payments';
$pageTitle = 'إيصالات الدفع | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_admin.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-6xl">

    <!-- Page header -->
    <div class="mb-7 flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
                <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
                      style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                    <i data-lucide="receipt" style="width:17px;height:17px;"></i>
                </span>
                إيصالات الدفع
            </h1>
            <p class="text-slate-500 mt-1 text-sm">راجع وأقبل أو ارفض إيصالات التحويل</p>
        </div>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-7">
        <?php foreach ([
            [$pendingCount,          'في الانتظار',   'clock',        '#d97706', 'rgba(217,119,6,.1)'],
            [$approvedCount,         'مقبول',          'check-circle', '#16a34a', 'rgba(22,163,74,.1)'],
            [$rejectedCount,         'مرفوض',          'x-circle',     '#dc2626', 'rgba(220,38,38,.1)'],
            [number_format($totalRevenue,0).' ج', 'إجمالي الإيرادات', 'banknote', '#2563EB', 'rgba(37,99,235,.1)'],
        ] as [$val,$lbl,$icon,$col,$bg]): ?>
        <div class="glass rounded-2xl p-4 text-center">
            <span class="w-8 h-8 rounded-lg mx-auto flex items-center justify-center mb-1.5"
                  style="background:<?php echo $bg; ?>">
                <i data-lucide="<?php echo $icon; ?>" style="width:15px;height:15px;color:<?php echo $col; ?>"></i>
            </span>
            <p class="display font-semibold text-xl" style="color:<?php echo $col; ?>"><?php echo $val; ?></p>
            <p class="text-xs text-slate-500"><?php echo $lbl; ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Feedback -->
    <?php if ($message): ?>
    <div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-6 flex items-center gap-3
        <?php echo $msgKind==='success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i data-lucide="<?php echo $msgKind==='success'?'check-circle':'alert-circle'; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="flex gap-2 flex-wrap mb-6">
        <?php foreach ([
            ['all',      'الكل',          null],
            ['pending',  'في الانتظار',   $pendingCount],
            ['approved', 'مقبولة',        $approvedCount],
            ['rejected', 'مرفوضة',        $rejectedCount],
        ] as [$f,$lbl,$cnt]): ?>
        <a href="?filter=<?php echo $f; ?>"
           class="flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold transition
           <?php echo $filter===$f ? 'btn-primary-nagah shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
            <?php echo $lbl; ?>
            <?php if ($cnt !== null): ?>
            <span class="inline-flex w-5 h-5 items-center justify-center rounded-full text-[10px] font-bold
                  <?php echo $filter===$f ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-600'; ?>">
                <?php echo $cnt; ?>
            </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Receipts grid -->
    <?php if (empty($receipts)): ?>
    <div class="glass rounded-3xl p-14 text-center text-slate-400">
        <i data-lucide="receipt" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-30"></i>
        <p class="text-sm">لا توجد إيصالات</p>
    </div>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($receipts as $r):
            [$sbg,$stxt,$sicon,$slbl] = $statusCfg[$r['status']] ?? $statusCfg['pending'];
        ?>
        <article class="glass rounded-3xl overflow-hidden flex flex-col reveal">

            <!-- Receipt image -->
            <div class="relative bg-slate-100 h-44 overflow-hidden cursor-pointer group"
                 onclick="showImg('<?php echo $base.'/'.htmlspecialchars($r['receipt_image']); ?>')">
                <?php if ($r['receipt_image'] && file_exists(__DIR__.'/../'.$r['receipt_image'])): ?>
                <img src="<?php echo $base.'/'.htmlspecialchars($r['receipt_image']); ?>"
                     alt="إيصال"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-slate-400">
                    <i data-lucide="image-off" style="width:36px;height:36px;opacity:.4;"></i>
                </div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
                    <i data-lucide="zoom-in" style="width:28px;height:28px;color:white;opacity:0;"
                       class="group-hover:opacity-100 transition"></i>
                </div>
                <!-- Status badge -->
                <span class="absolute top-2 right-2 inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full <?php echo $sbg.' '.$stxt; ?>">
                    <i data-lucide="<?php echo $sicon; ?>" style="width:11px;height:11px;"></i>
                    <?php echo $slbl; ?>
                </span>
            </div>

            <!-- Info -->
            <div class="p-5 flex flex-col flex-1 gap-3">
                <!-- Student -->
                <div>
                    <p class="font-bold text-slate-800"><?php echo htmlspecialchars($r['student_name']); ?></p>
                    <?php if ($r['student_phone']): ?>
                    <a href="tel:<?php echo htmlspecialchars($r['student_phone']); ?>"
                       class="text-xs text-slate-400 flex items-center gap-1 mt-0.5 hover:text-green-600 transition">
                        <i data-lucide="phone" style="width:10px;height:10px;"></i>
                        <?php echo htmlspecialchars($r['student_phone']); ?>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Course + plan -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-slate-50 rounded-xl p-2.5">
                        <p class="text-slate-400 mb-0.5">المادة</p>
                        <p class="font-semibold text-slate-700 truncate"><?php echo htmlspecialchars($r['course_name']); ?></p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-2.5">
                        <p class="text-slate-400 mb-0.5">الخطة</p>
                        <p class="font-semibold text-slate-700"><?php echo $planLabels[$r['plan']] ?? $r['plan']; ?></p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-2.5">
                        <p class="text-slate-400 mb-0.5">المبلغ</p>
                        <p class="font-semibold text-slate-700">
                            <?php echo $r['amount'] ? number_format($r['amount'],0).' ج' : number_format($r['price'],0).' ج'; ?>
                        </p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-2.5">
                        <p class="text-slate-400 mb-0.5">التاريخ</p>
                        <p class="font-semibold text-slate-700"><?php echo date('d/m/Y', strtotime($r['created_at'])); ?></p>
                    </div>
                </div>

                <?php if ($r['notes']): ?>
                <p class="text-xs text-slate-500 leading-relaxed bg-slate-50 rounded-xl p-2.5">
                    <span class="font-bold text-slate-600">ملاحظة:</span> <?php echo htmlspecialchars($r['notes']); ?>
                </p>
                <?php endif; ?>

                <!-- Actions -->
                <?php if ($r['status'] === 'pending'): ?>
                <div class="flex gap-2 mt-auto">
                    <form method="POST" class="flex-1">
                        <input type="hidden" name="csrf_token"   value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action"       value="approve">
                        <input type="hidden" name="receipt_id"   value="<?php echo $r['id']; ?>">
                        <button type="submit" onclick="return confirm('قبول الإيصال وتفعيل الاشتراك؟')"
                                class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-green-600 text-white font-bold text-sm hover:bg-green-700 transition">
                            <i data-lucide="check" style="width:14px;height:14px;"></i> قبول
                        </button>
                    </form>
                    <button onclick="showRejectModal(<?php echo $r['id']; ?>)"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-red-100 text-red-700 font-bold text-sm hover:bg-red-200 transition">
                        <i data-lucide="x" style="width:14px;height:14px;"></i> رفض
                    </button>
                </div>
                <?php elseif ($r['status'] === 'approved'): ?>
                <div class="text-center py-2 rounded-xl bg-green-50 text-green-700 text-sm font-bold flex items-center justify-center gap-1.5">
                    <i data-lucide="check-circle" style="width:14px;height:14px;"></i> مُفعَّل
                </div>
                <?php else: ?>
                <div class="text-center py-2 rounded-xl bg-red-50 text-red-600 text-sm font-bold flex items-center justify-center gap-1.5">
                    <i data-lucide="x-circle" style="width:14px;height:14px;"></i> مرفوض
                </div>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</main>
</div>

<!-- Reject modal -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,.65);backdrop-filter:blur(6px)">
    <div class="glass rounded-3xl w-full max-w-md p-7">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i data-lucide="x-circle" style="width:17px;height:17px;color:#dc2626"></i> سبب الرفض
        </h3>
        <form method="POST">
            <input type="hidden" name="csrf_token"  value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action"      value="reject">
            <input type="hidden" name="receipt_id"  id="reject-receipt-id">
            <textarea name="reason" rows="3" class="field-input resize-none w-full mb-4"
                      placeholder="اكتب سبب الرفض للطالب…"></textarea>
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 py-2.5 rounded-full bg-red-600 text-white font-bold text-sm hover:bg-red-700 transition">
                    تأكيد الرفض
                </button>
                <button type="button" onclick="closeRejectModal()"
                        class="flex-1 py-2.5 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Image lightbox -->
<div id="imgModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,.85);backdrop-filter:blur(8px)"
     onclick="closeImg()">
    <img id="lightbox-img" src="#" alt="إيصال"
         class="max-w-full max-h-[90vh] rounded-2xl shadow-2xl object-contain">
</div>

<script>
function showRejectModal(id) {
    document.getElementById('reject-receipt-id').value = id;
    const m = document.getElementById('rejectModal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeRejectModal() {
    const m = document.getElementById('rejectModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
function showImg(src) {
    document.getElementById('lightbox-img').src = src;
    const m = document.getElementById('imgModal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeImg() {
    const m = document.getElementById('imgModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
