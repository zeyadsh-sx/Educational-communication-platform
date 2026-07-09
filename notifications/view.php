<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
    exit;
}

$userId = getCurrentUserId();
$base   = nagahBaseUrl();
$pdo    = getDB();

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

// Mark all as read
$pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE")->execute([$userId]);

// Notification type detection (naive, based on message keywords)
function notifIcon(string $msg): array {
    $m = mb_strtolower($msg);
    if (str_contains($m,'درس'))     return ['book-open',     '#2563EB', 'rgba(37,99,235,.1)'];
    if (str_contains($m,'واجب'))    return ['notebook-pen',  '#7c3aed', 'rgba(124,58,237,.1)'];
    if (str_contains($m,'امتحان'))  return ['file-check-2',  '#dc2626', 'rgba(220,38,38,.1)'];
    if (str_contains($m,'موعد'))    return ['calendar',      '#d97706', 'rgba(217,119,6,.1)'];
    if (str_contains($m,'تسجيل') || str_contains($m,'انضم')) return ['user-plus','#16a34a','rgba(22,163,74,.1)'];
    return ['bell', '#2563EB', 'rgba(37,99,235,.1)'];
}

$unreadCount = 0;
foreach ($notifications as $n) if (!$n['is_read']) $unreadCount++;

$dashboardUrl = isProfessor() ? $base . '/admin/dashboard.php' : $base . '/student/dashboard.php';
$pageTitle    = 'الإشعارات | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<!-- Banner -->
<section class="relative overflow-hidden py-12 sm:py-14" style="background:linear-gradient(135deg,#1e3a8a,#2563EB,#3b82f6)">
    <div class="absolute inset-0 grid-dots opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-5">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="display font-semibold text-3xl text-white flex items-center gap-3">
                    الإشعارات
                    <?php if ($unreadCount > 0): ?>
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400 text-slate-900 text-xs font-bold"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </h1>
                <p class="text-white/70 mt-1"><?php echo count($notifications); ?> إشعار إجمالاً</p>
            </div>
            <div class="flex gap-3">
                <a href="<?php echo $dashboardUrl; ?>" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 backdrop-blur text-white px-5 py-2.5 rounded-full text-sm font-bold transition">
                    <i data-lucide="layout-dashboard" style="width:15px;height:15px;"></i> لوحة التحكم
                </a>
                <?php if (!empty($notifications)): ?>
                <button onclick="clearAll()" class="inline-flex items-center gap-2 bg-red-500/20 hover:bg-red-500/30 backdrop-blur text-white px-5 py-2.5 rounded-full text-sm font-bold transition">
                    <i data-lucide="trash-2" style="width:15px;height:15px;"></i> حذف الكل
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<main class="max-w-3xl mx-auto px-5 py-10 pb-20">

    <?php if (empty($notifications)): ?>
    <div class="glass rounded-3xl p-16 text-center">
        <i data-lucide="bell-off" class="mx-auto text-slate-300 mb-4" style="width:56px;height:56px;"></i>
        <h3 class="font-bold text-slate-500 text-lg">لا توجد إشعارات</h3>
        <p class="text-slate-400 text-sm mt-2">ستظهر هنا إشعاراتك الجديدة</p>
    </div>
    <?php else: ?>
    <div class="space-y-3" id="notif-list">
        <?php foreach ($notifications as $n):
            [$icon, $iconColor, $iconBg] = notifIcon($n['message']);
            $isUnread = !$n['is_read'];
        ?>
        <div class="glass rounded-2xl p-5 reveal flex gap-4 <?php echo $isUnread ? 'border-r-4 border-blue-500' : ''; ?>" data-id="<?php echo $n['id']; ?>">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5" style="background:<?php echo $iconBg; ?>">
                <i data-lucide="<?php echo $icon; ?>" style="width:18px;height:18px;color:<?php echo $iconColor; ?>"></i>
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-slate-700 leading-relaxed <?php echo $isUnread ? 'font-semibold' : ''; ?>">
                    <?php echo htmlspecialchars($n['message']); ?>
                </p>
                <p class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                    <i data-lucide="clock" style="width:11px;height:11px;"></i>
                    <?php echo date('d/m/Y H:i', strtotime($n['created_at'])); ?>
                    <?php if ($isUnread): ?>
                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500 mr-1"></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>

<script>
function clearAll() {
    if (!confirm('هل تريد حذف جميع الإشعارات؟')) return;
    fetch('<?php echo $base; ?>/api/notifications/clear.php', {method:'POST', headers:{'Content-Type':'application/json'}})
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); else alert('حدث خطأ'); })
        .catch(() => alert('حدث خطأ في الاتصال'));
}
</script>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
