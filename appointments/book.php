<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/gamification.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('/auth/login.php');
    exit;
}

$courseId    = getSafeGet('course_id', 0, 'int');
$base        = nagahBaseUrl();
$message     = '';
$messageKind = '';
$pdo         = getDB();
$userId      = getSafeUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $message = 'توكن الأمان غير صحيح. الرجاء تحديث الصفحة.';
        $messageKind = 'error';
    } else {
        $professorId     = getSafePost('professor_id', 0, 'int');
        $appointmentDate = getSafePost('appointment_date', '', 'string');
        $notes           = getSafePost('notes', '', 'string');

        if (!$professorId || $professorId <= 0) {
            $message = 'اختر معلماً صحيحاً'; $messageKind = 'error';
        } elseif (empty($appointmentDate)) {
            $message = 'التاريخ والوقت مطلوبان'; $messageKind = 'error';
        } else {
            try {
                $dt = DateTime::createFromFormat('Y-m-d\TH:i', $appointmentDate)
                   ?: DateTime::createFromFormat('Y-m-d H:i', $appointmentDate);
                if (!$dt) throw new Exception('صيغة التاريخ غير صحيحة');
                if ($dt <= new DateTime()) throw new Exception('يجب أن يكون الموعد في المستقبل');

                $dtStr = $dt->format('Y-m-d H:i:s');
                $profCheck = $pdo->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'professor'");
                $profCheck->execute([$professorId]);
                if (!$profCheck->fetch()) throw new Exception('المعلم غير موجود');

                $ins = $pdo->prepare("INSERT INTO appointments (student_id, professor_id, appointment_date, date_time, notes, status, created_at) VALUES (?,?,?,?,?,'pending',NOW())");
                $ins->execute([$userId, $professorId, $appointmentDate, $dtStr, $notes]);

                awardPoints($userId, 'book_appointment');

                if (file_exists(__DIR__ . '/../includes/notification_functions.php')) {
                    require_once __DIR__ . '/../includes/notification_functions.php';
                    $stName = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
                    $stName->execute([$userId]);
                    sendNotification($professorId, "طالب ({$stName->fetchColumn()}) حجز موعداً في {$dtStr}");
                }

                $message = 'تم حجز موعدك بنجاح! ستصلك رسالة تأكيد قريباً.';
                $messageKind = 'success';
            } catch (Exception $e) {
                $message = $e->getMessage(); $messageKind = 'error';
            }
        }
    }
}

$professors = [];
try {
    $ps = $pdo->prepare("SELECT id, full_name, email FROM users WHERE user_type = 'professor' ORDER BY full_name");
    $ps->execute();
    $professors = $ps->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$pageTitle = 'حجز موعد | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';

$minDate = (new DateTime('+1 hour'))->format('Y-m-d\TH:i');
?>

<section class="relative min-h-[calc(100vh-80px)] flex items-center justify-center py-16 overflow-hidden auth-bg">
    <span class="blob" style="width:380px;height:380px;background:#60A5FA;top:-80px;right:-80px;"></span>
    <span class="blob" style="width:300px;height:300px;background:#F59E0B;bottom:-80px;left:-60px;opacity:.35;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>

    <div class="relative z-10 w-full max-w-xl mx-auto px-5">
        <a href="view.php" class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-600 text-sm font-medium mb-6 transition">
            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i> مواعيدي
        </a>

        <div class="glass rounded-[28px] p-8 reveal">
            <div class="text-center mb-7">
                <span class="w-12 h-12 rounded-2xl flex items-center justify-center text-white mx-auto mb-4 shadow-lg" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <i data-lucide="calendar-check" style="width:22px;height:22px;"></i>
                </span>
                <h1 class="display font-semibold text-2xl text-slate-900">حجز موعد مع المعلم</h1>
                <p class="text-sm text-slate-500 mt-1.5">اختر المعلم والوقت المناسب لك</p>
            </div>

            <?php if ($message): ?>
            <div class="rounded-xl px-4 py-3 text-sm font-medium mb-6 <?php echo $messageKind === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($messageKind === 'success'): ?>
                    <a href="view.php" class="block mt-2 font-bold underline">عرض مواعيدي ←</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5" id="book-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">اختر المعلم <span class="text-red-500">*</span></label>
                    <select name="professor_id" class="field-input" required>
                        <option value="">— اختر معلماً —</option>
                        <?php foreach ($professors as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo (getSafePost('professor_id', 0, 'int') == $p['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['full_name']); ?> (<?php echo htmlspecialchars($p['email']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">التاريخ والوقت <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="appointment_date" class="field-input" required min="<?php echo $minDate; ?>">
                    <p class="text-xs text-slate-400 mt-1">اختر موعداً في المستقبل</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">ملاحظات (اختياري)</label>
                    <textarea name="notes" rows="4" class="field-input resize-none" placeholder="ما الموضوع الذي تريد مناقشته؟…"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary-nagah flex-1 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="calendar-check" style="width:16px;height:16px;"></i> حجز الموعد
                    </button>
                    <a href="view.php" class="px-6 py-3 rounded-full border-2 border-slate-300 text-slate-600 font-bold text-sm hover:bg-slate-50 transition text-center">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
