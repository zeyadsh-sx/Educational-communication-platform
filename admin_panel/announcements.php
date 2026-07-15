<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

requireRole('admin');

$userId  = getCurrentUserId();
$base    = nagahBaseUrl();
$pdo     = getDB();
$message = '';
$msgKind = '';

// POST — add or delete announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title    = trim($_POST['title']    ?? '');
        $content  = trim($_POST['content']  ?? '');
        $priority = in_array($_POST['priority'] ?? '', ['high','medium','low']) ? $_POST['priority'] : 'medium';
        $courseId = (int)($_POST['course_id'] ?? 0) ?: null;

        if ($title && $content) {
            $pdo->prepare("INSERT INTO announcements (title, content, professor_id, course_id, priority, created_by) VALUES (?,?,?,?,?,?)")
                ->execute([$title, $content, $userId, $courseId, $priority, $userId]);
            $message = 'تم نشر الإعلان بنجاح';
            $msgKind = 'success';
        } else {
            $message = 'العنوان والمحتوى مطلوبان';
            $msgKind = 'error';
        }
    } elseif ($action === 'delete') {
        $annId = (int)($_POST['ann_id'] ?? 0);
        if ($annId) {
            $pdo->prepare("DELETE FROM announcements WHERE id=?")->execute([$annId]);
            $message = 'تم حذف الإعلان';
            $msgKind = 'success';
        }
    }
}

$announcements = $pdo->query("
    SELECT a.*, u.full_name AS author_name, c.course_name
    FROM announcements a
    LEFT JOIN users u ON a.created_by = u.id
    LEFT JOIN courses c ON a.course_id = c.id
    ORDER BY a.created_at DESC
    LIMIT 100
")->fetchAll();

$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name")->fetchAll();

$priorityCfg = [
    'high'   => ['bg-red-100',   'text-red-700',   'عاجل'],
    'medium' => ['bg-blue-100',  'text-blue-700',  'متوسط'],
    'low'    => ['bg-slate-100', 'text-slate-600', 'عادي'],
];

$_activeSidebar = 'announcements';
$pageTitle = 'الإعلانات | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>
<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_admin.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-5xl">

    <div class="mb-7 flex items-start justify-between flex-wrap gap-4">
        <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
            <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="megaphone" style="width:17px;height:17px;"></i>
            </span>
            الإعلانات
        </h1>
        <button onclick="document.getElementById('addModal').classList.remove('hidden');document.getElementById('addModal').classList.add('flex')"
                class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold shadow-lg hover:-translate-y-0.5 transition-all">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> إعلان جديد
        </button>
    </div>

    <?php if ($message): ?>
    <div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-6 flex items-center gap-3
        <?php echo $msgKind==='success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i data-lucide="<?php echo $msgKind==='success'?'check-circle':'alert-circle'; ?>" style="width:16px;height:16px;flex-shrink:0"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Announcements list -->
    <?php if (empty($announcements)): ?>
    <div class="glass rounded-3xl p-14 text-center text-slate-400">
        <i data-lucide="megaphone" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-30"></i>
        <p class="text-sm">لا توجد إعلانات بعد</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($announcements as $ann):
            [$pbg,$ptxt,$plbl] = $priorityCfg[$ann['priority']] ?? $priorityCfg['low'];
        ?>
        <div class="glass rounded-3xl p-5 reveal">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <h3 class="font-bold text-slate-800"><?php echo htmlspecialchars($ann['title']); ?></h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?php echo $pbg . ' ' . $ptxt; ?>"><?php echo $plbl; ?></span>
                        <?php if ($ann['course_name']): ?>
                        <span class="tag-pill px-2.5 py-0.5 rounded-full text-xs font-bold"><?php echo htmlspecialchars($ann['course_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($ann['content'])); ?></p>
                </div>
                <form method="POST" class="shrink-0" onsubmit="return confirm('حذف هذا الإعلان؟')">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action"    value="delete">
                    <input type="hidden" name="ann_id"    value="<?php echo $ann['id']; ?>">
                    <button type="submit" class="p-1.5 rounded-xl bg-red-50 text-red-500 hover:bg-red-100 transition">
                        <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                    </button>
                </form>
            </div>
            <p class="text-xs text-slate-400 flex items-center gap-2">
                <i data-lucide="user-round" style="width:11px;height:11px;"></i>
                <?php echo htmlspecialchars($ann['author_name'] ?? 'النظام'); ?>
                <span>·</span>
                <?php echo date('d/m/Y H:i', strtotime($ann['created_at'])); ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</main>
</div>

<!-- Add Announcement Modal -->
<div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,.6);backdrop-filter:blur(6px)">
    <div class="glass rounded-3xl w-full max-w-lg p-7 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="megaphone" style="width:17px;height:17px;color:#2563EB"></i> إعلان جديد
            </h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden');document.getElementById('addModal').classList.remove('flex')"
                    class="p-1.5 rounded-xl hover:bg-slate-100"><i data-lucide="x" style="width:16px;height:16px;"></i></button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="add">
            <div>
                <label class="block text-sm font-semibold mb-1.5">العنوان *</label>
                <input name="title" required class="field-input" placeholder="عنوان الإعلان">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5">المحتوى *</label>
                <textarea name="content" required rows="4" class="field-input resize-none" placeholder="نص الإعلان…"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">الأولوية</label>
                    <select name="priority" class="field-input">
                        <option value="medium">متوسط</option>
                        <option value="high">عاجل</option>
                        <option value="low">عادي</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">الكورس (اختياري)</label>
                    <select name="course_id" class="field-input">
                        <option value="0">— للجميع —</option>
                        <?php foreach ($courses as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 py-2.5 rounded-full btn-primary-nagah font-bold text-sm">نشر الإعلان</button>
                <button type="button"
                        onclick="document.getElementById('addModal').classList.add('hidden');document.getElementById('addModal').classList.remove('flex')"
                        class="flex-1 py-2.5 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">إلغاء</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
