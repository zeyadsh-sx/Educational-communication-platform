<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
    exit;
}

$userId   = getCurrentUserId();
$userType = getCurrentUserType();
$base     = nagahBaseUrl();
$pdo      = getDB();

if ($userType === 'professor') {
    $stmt = $pdo->prepare("SELECT a.*, u.full_name as other_name, u.email as other_email FROM appointments a LEFT JOIN users u ON a.student_id = u.id WHERE a.professor_id = ? ORDER BY a.appointment_date DESC, a.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT a.*, u.full_name as other_name, u.email as other_email FROM appointments a LEFT JOIN users u ON a.professor_id = u.id WHERE a.student_id = ? ORDER BY a.appointment_date DESC, a.created_at DESC");
}
$stmt->execute([$userId]);
$appointments = $stmt->fetchAll();

$statusConfig = [
    'pending'   => ['label'=>'في الانتظار', 'bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'icon'=>'clock'],
    'confirmed' => ['label'=>'مؤكد',        'bg'=>'bg-green-100',  'text'=>'text-green-700',  'icon'=>'check-circle'],
    'cancelled' => ['label'=>'ملغي',         'bg'=>'bg-red-100',    'text'=>'text-red-700',    'icon'=>'x-circle'],
    'completed' => ['label'=>'مكتمل',        'bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'icon'=>'check-check'],
];

$pageTitle = 'مواعيدي | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<!-- Banner -->
<section class="relative overflow-hidden py-12 sm:py-14" style="background:linear-gradient(135deg,#1e3a8a,#2563EB,#3b82f6)">
    <div class="absolute inset-0 grid-dots opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-5">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="display font-semibold text-3xl text-white">مواعيدي</h1>
                <p class="text-white/70 mt-1"><?php echo count($appointments); ?> موعد مسجل</p>
            </div>
            <?php if ($userType === 'student'): ?>
            <a href="book.php" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 backdrop-blur text-white px-5 py-2.5 rounded-full text-sm font-bold transition">
                <i data-lucide="calendar-plus" style="width:16px;height:16px;"></i> حجز موعد جديد
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<main class="max-w-5xl mx-auto px-5 py-10 pb-20">

    <?php if (empty($appointments)): ?>
    <div class="glass rounded-3xl p-16 text-center">
        <i data-lucide="calendar-x" class="mx-auto text-slate-300 mb-4" style="width:56px;height:56px;"></i>
        <h3 class="font-bold text-slate-500 text-lg">لا توجد مواعيد</h3>
        <p class="text-slate-400 text-sm mt-2"><?php echo $userType === 'student' ? 'لم تقم بحجز أي مواعيد بعد' : 'لا توجد مواعيد محجوزة مع الطلاب'; ?></p>
        <?php if ($userType === 'student'): ?>
        <a href="book.php" class="mt-6 inline-flex items-center gap-2 btn-primary-nagah px-6 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
            <i data-lucide="calendar-plus" style="width:16px;height:16px;"></i> احجز موعدك الأول
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 gap-5">
        <?php foreach ($appointments as $app):
            $sc      = $statusConfig[$app['status']] ?? $statusConfig['pending'];
            $appDate = $app['appointment_date'] ?? $app['date_time'] ?? null;
        ?>
        <article class="glass rounded-2xl p-6 reveal flex flex-col gap-4">
            <!-- Header row -->
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                        <i data-lucide="<?php echo $userType === 'professor' ? 'graduation-cap' : 'user-round'; ?>" style="width:18px;height:18px;"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800 truncate"><?php echo htmlspecialchars($app['other_name'] ?? 'غير محدد'); ?></p>
                        <p class="text-xs text-slate-400 truncate"><?php echo htmlspecialchars($app['other_email'] ?? ''); ?></p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full shrink-0 <?php echo $sc['bg'] . ' ' . $sc['text']; ?>">
                    <i data-lucide="<?php echo $sc['icon']; ?>" style="width:12px;height:12px;"></i>
                    <?php echo $sc['label']; ?>
                </span>
            </div>

            <!-- Date/Time -->
            <?php if ($appDate): ?>
            <div class="flex items-center gap-4 text-sm text-slate-600 bg-slate-50 rounded-xl px-4 py-3">
                <span class="flex items-center gap-1.5 font-semibold">
                    <i data-lucide="calendar" style="width:15px;height:15px;color:#2563EB"></i>
                    <?php echo date('d/m/Y', strtotime($appDate)); ?>
                </span>
                <span class="w-px h-4 bg-slate-200"></span>
                <span class="flex items-center gap-1.5 font-semibold">
                    <i data-lucide="clock" style="width:15px;height:15px;color:#F59E0B"></i>
                    <?php echo date('H:i', strtotime($appDate)); ?>
                </span>
            </div>
            <?php endif; ?>

            <!-- Notes -->
            <?php if (!empty($app['notes'])): ?>
            <div class="text-xs text-slate-500 leading-relaxed bg-slate-50 rounded-xl px-4 py-3">
                <span class="font-semibold text-slate-600 block mb-1">ملاحظات:</span>
                <?php echo nl2br(htmlspecialchars($app['notes'])); ?>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <?php if ($app['status'] === 'pending'): ?>
            <div class="flex gap-2 pt-1">
                <?php if ($userType === 'professor'): ?>
                <button onclick="updateStatus(<?php echo $app['id']; ?>,'confirmed')"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 rounded-xl bg-green-100 text-green-700 font-bold text-xs hover:bg-green-200 transition">
                    <i data-lucide="check" style="width:13px;height:13px;"></i> تأكيد
                </button>
                <?php endif; ?>
                <button onclick="updateStatus(<?php echo $app['id']; ?>,'cancelled')"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 rounded-xl bg-red-100 text-red-700 font-bold text-xs hover:bg-red-200 transition">
                    <i data-lucide="x" style="width:13px;height:13px;"></i>
                    <?php echo $userType === 'professor' ? 'رفض' : 'إلغاء الحجز'; ?>
                </button>
            </div>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>

<script>
function updateStatus(id, status) {
    const labels = {confirmed: 'تأكيد', cancelled: 'إلغاء'};
    if (!confirm('هل تريد ' + (labels[status] || status) + ' هذا الموعد؟')) return;
    fetch('<?php echo $base; ?>/api/appointments/update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({appointment_id: id, status})
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); else alert(d.message || 'حدث خطأ'); })
    .catch(() => alert('حدث خطأ في الاتصال'));
}
</script>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
