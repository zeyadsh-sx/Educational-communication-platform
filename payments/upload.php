<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/subscription_functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

requireRole('student');

$userId  = getCurrentUserId();
$base    = nagahBaseUrl();
$pdo     = getDB();
$message = '';
$msgKind = '';

// --- جلب الاشتراكات التي تحتاج إيصال ---
$pendingSubs = $pdo->prepare("
    SELECT s.*, c.course_name, c.course_code
    FROM subscriptions s
    JOIN courses c ON s.course_id = c.id
    WHERE s.student_id = ? AND s.status = 'pending'
    ORDER BY s.created_at DESC
");
$pendingSubs->execute([$userId]);
$subs = $pendingSubs->fetchAll();

// pre-select from URL
$preSubId = (int)($_GET['sub_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $subId  = (int)($_POST['sub_id']  ?? 0);
    $amount = $_POST['amount'] !== '' ? (float)$_POST['amount'] : null;
    $notes  = trim($_POST['notes'] ?? '');

    // verify this subscription belongs to the student
    $checkSub = $pdo->prepare("SELECT id, status FROM subscriptions WHERE id=? AND student_id=?");
    $checkSub->execute([$subId, $userId]);
    $sub = $checkSub->fetch();

    if (!$sub) {
        $message = 'اشتراك غير صحيح'; $msgKind = 'error';
    } elseif (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
        $message = 'الرجاء اختيار صورة الإيصال'; $msgKind = 'error';
    } else {
        $result = uploadReceipt($subId, $userId, $_FILES['receipt'], $amount, $notes ?: null);
        $message = $result['success'] ? 'تم رفع الإيصال بنجاح! سيراجعه الأدمن قريباً.' : $result['message'];
        $msgKind = $result['success'] ? 'success' : 'error';
        if ($result['success']) {
            // refresh list
            $pendingSubs->execute([$userId]);
            $subs = $pendingSubs->fetchAll();
        }
    }
}

$pageTitle = 'رفع إيصال الدفع | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<section class="relative overflow-hidden py-10" style="background:linear-gradient(135deg,#1e3a8a,#2563EB)">
    <div class="absolute inset-0 grid-dots opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-5">
        <a href="<?php echo $base; ?>/subscriptions/my.php" class="inline-flex items-center gap-2 text-white/70 hover:text-white text-sm mb-3 transition">
            <i data-lucide="arrow-right" style="width:15px;height:15px;"></i> اشتراكاتي
        </a>
        <h1 class="display font-semibold text-2xl sm:text-3xl text-white">رفع إيصال الدفع</h1>
        <p class="text-white/70 mt-1 text-sm">ارفع صورة التحويل وسيتم تفعيل اشتراكك خلال 24 ساعة</p>
    </div>
</section>

<main class="max-w-4xl mx-auto px-5 py-10 pb-20">

    <?php if ($message): ?>
    <div class="rounded-2xl px-5 py-4 text-sm font-medium mb-7 flex items-center gap-3
        <?php echo $msgKind === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i data-lucide="<?php echo $msgKind === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="width:18px;height:18px;flex-shrink:0"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-5 gap-8">

        <!-- Upload form -->
        <div class="lg:col-span-3">
            <div class="glass rounded-3xl p-7">
                <h2 class="font-bold text-slate-800 text-lg mb-6 flex items-center gap-2">
                    <i data-lucide="upload-cloud" style="width:18px;height:18px;color:#2563EB"></i>
                    رفع الإيصال
                </h2>

                <?php if (empty($subs)): ?>
                <div class="text-center py-10 text-slate-400">
                    <i data-lucide="check-circle" style="width:44px;height:44px;" class="mx-auto mb-3 opacity-30"></i>
                    <p class="text-sm font-medium">لا توجد اشتراكات تنتظر الدفع</p>
                    <a href="<?php echo $base; ?>/subscriptions/subscribe.php"
                       class="mt-4 inline-flex items-center gap-2 btn-primary-nagah px-5 py-2.5 rounded-full text-sm font-bold">
                        اشترك في مادة
                    </a>
                </div>
                <?php else: ?>
                <form method="POST" enctype="multipart/form-data" id="receipt-form" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">
                            الاشتراك <span class="text-red-500">*</span>
                        </label>
                        <select name="sub_id" class="field-input" required>
                            <option value="">— اختر الاشتراك —</option>
                            <?php foreach ($subs as $s): ?>
                            <option value="<?php echo $s['id']; ?>"
                                <?php echo $preSubId === (int)$s['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['course_name']); ?> — <?php echo ucfirst($s['plan']); ?>
                                (<?php echo number_format($s['price'], 0); ?> جنيه)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">المبلغ المدفوع (جنيه)</label>
                        <input type="number" name="amount" step="0.01" min="0" class="field-input" placeholder="مثال: 150">
                    </div>

                    <!-- Drop zone -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">
                            صورة الإيصال / التحويل <span class="text-red-500">*</span>
                        </label>
                        <label for="receipt-file"
                               class="flex flex-col items-center justify-center gap-3 w-full rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 hover:border-blue-500 hover:bg-blue-50/30 cursor-pointer transition p-8"
                               id="drop-zone">
                            <span class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background:rgba(37,99,235,.1)">
                                <i data-lucide="image" style="width:26px;height:26px;color:#2563EB"></i>
                            </span>
                            <div class="text-center">
                                <p class="font-semibold text-slate-700 text-sm">اسحب الصورة هنا أو انقر للاختيار</p>
                                <p class="text-xs text-slate-400 mt-1" id="file-label">JPG / PNG / WEBP — الحد الأقصى 5 MB</p>
                            </div>
                            <input id="receipt-file" type="file" name="receipt" class="sr-only" required
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   onchange="previewImg(this)">
                        </label>
                        <img id="img-preview" src="#" alt="preview" class="hidden mt-3 rounded-2xl max-h-48 mx-auto object-contain border border-slate-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1.5 text-slate-800">ملاحظات (اختياري)</label>
                        <textarea name="notes" rows="3" class="field-input resize-none" placeholder="رقم العملية أو أي ملاحظة للإدارة…"></textarea>
                    </div>

                    <button type="submit" id="submit-btn"
                            class="w-full py-3.5 rounded-full btn-primary-nagah font-bold shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" style="width:16px;height:16px;"></i>
                        إرسال الإيصال للإدارة
                        <i data-lucide="loader-2" id="submit-spin" class="spin hidden" style="width:16px;height:16px;"></i>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info panel -->
        <div class="lg:col-span-2 space-y-5">
            <!-- Payment info -->
            <div class="glass rounded-3xl p-6">
                <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2 text-sm">
                    <i data-lucide="info" style="width:15px;height:15px;color:#2563EB"></i> بيانات التحويل
                </h3>
                <div class="space-y-3 text-sm">
                    <?php foreach ([
                        ['bank',       'البنك',           'بنك مصر'],
                        ['hash',       'رقم الحساب',      '1234567890'],
                        ['user-round', 'اسم صاحب الحساب', 'أكاديمية ماستر'],
                        ['smartphone', 'فودافون كاش',     '01001234567'],
                        ['smartphone', 'اورنج كاش',       '01201234567'],
                    ] as [$icon, $lbl, $val]): ?>
                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
                        <i data-lucide="<?php echo $icon; ?>" style="width:15px;height:15px;color:#2563EB;flex-shrink:0;margin-top:1px"></i>
                        <div>
                            <p class="text-xs text-slate-400"><?php echo $lbl; ?></p>
                            <p class="font-semibold text-slate-800"><?php echo $val; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Steps -->
            <div class="glass rounded-3xl p-6">
                <h3 class="font-bold text-slate-700 mb-4 text-sm flex items-center gap-2">
                    <i data-lucide="list-ordered" style="width:15px;height:15px;color:#16a34a"></i> خطوات التفعيل
                </h3>
                <ol class="space-y-3">
                    <?php foreach ([
                        'حوّل المبلغ عبر أي وسيلة دفع',
                        'صوّر إيصال التحويل',
                        'ارفع الصورة هنا',
                        'انتظر موافقة الإدارة (24 ساعة)',
                        'سيتم تفعيل اشتراكك تلقائيًا',
                    ] as $i => $step): ?>
                    <li class="flex items-start gap-3 text-xs text-slate-600">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-white font-bold shrink-0 text-[10px]"
                              style="background:linear-gradient(135deg,#2563EB,#60A5FA)"><?php echo $i+1; ?></span>
                        <?php echo $step; ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
</main>

<script>
function previewImg(input) {
    const preview = document.getElementById('img-preview');
    const label   = document.getElementById('file-label');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.classList.remove('hidden'); };
        reader.readAsDataURL(input.files[0]);
        label.textContent = input.files[0].name;
    }
}
document.getElementById('receipt-form')?.addEventListener('submit', () => {
    const btn  = document.getElementById('submit-btn');
    const spin = document.getElementById('submit-spin');
    btn.disabled = true; btn.style.opacity = '.7';
    spin?.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
// drag highlight
const dz = document.getElementById('drop-zone');
['dragover','dragenter'].forEach(ev => dz?.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('border-blue-500','bg-blue-50'); }));
['dragleave','drop'].forEach(ev => dz?.addEventListener(ev, () => dz.classList.remove('border-blue-500','bg-blue-50')));
</script>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
