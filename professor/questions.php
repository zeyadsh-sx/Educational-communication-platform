<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/nagah_theme.php';

requireRole('professor');

$userId  = getCurrentUserId();
$base    = nagahBaseUrl();
$pdo     = getDB();
$message = '';
$msgKind = '';

// POST — answer a question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $qId    = (int)($_POST['question_id'] ?? 0);
    $answer = trim($_POST['answer_text']  ?? '');

    if ($qId && $answer) {
        // Verify the question belongs to this professor
        $check = $pdo->prepare("SELECT id FROM questions WHERE id=? AND professor_id=?");
        $check->execute([$qId, $userId]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE questions SET answer_text=?, status='answered', answered_at=NOW() WHERE id=?")
                ->execute([$answer, $qId]);

            // Notify student
            $qInfo = $pdo->prepare("SELECT student_id, question_text FROM questions WHERE id=?");
            $qInfo->execute([$qId]);
            $q = $qInfo->fetch();
            if ($q && function_exists('sendSystemNotification')) {
                require_once __DIR__ . '/../includes/subscription_functions.php';
                sendSystemNotification($q['student_id'], 'تم الرد على سؤالك: ' . mb_substr($q['question_text'], 0, 60) . '…', 'general');
            }

            $message = 'تم إرسال الإجابة بنجاح';
            $msgKind = 'success';
        }
    } else {
        $message = 'الإجابة مطلوبة';
        $msgKind = 'error';
    }
}

// Filters
$filter = in_array($_GET['filter'] ?? '', ['pending','answered']) ? $_GET['filter'] : 'all';
$courseId = (int)($_GET['course_id'] ?? 0);

// My courses
$coursesStmt = $pdo->prepare("SELECT id, course_name FROM courses WHERE professor_id=? ORDER BY course_name");
$coursesStmt->execute([$userId]);
$courses = $coursesStmt->fetchAll();

// Build query
$where  = "WHERE q.professor_id=?";
$params = [$userId];
if ($filter === 'pending')  { $where .= " AND q.status='pending'"; }
if ($filter === 'answered') { $where .= " AND q.status='answered'"; }
if ($courseId)              { $where .= " AND q.course_id=?"; $params[] = $courseId; }

$qStmt = $pdo->prepare("
    SELECT q.*,
           u.full_name  AS student_name,
           c.course_name
    FROM questions q
    JOIN users   u ON q.student_id = u.id
    JOIN courses c ON q.course_id  = c.id
    $where
    ORDER BY q.created_at DESC
    LIMIT 150
");
$qStmt->execute($params);
$questions = $qStmt->fetchAll();

$pendingCount  = (int)$pdo->prepare("SELECT COUNT(*) FROM questions WHERE professor_id=? AND status='pending'")->execute([$userId])
    ? $pdo->query("SELECT COUNT(*) FROM questions WHERE professor_id=$userId AND status='pending'")->fetchColumn()
    : 0;

// fix: use proper prepared statement
$pcStmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE professor_id=? AND status='pending'");
$pcStmt->execute([$userId]);
$pendingCount = (int)$pcStmt->fetchColumn();

$_activeSidebar = 'questions';
$pageTitle = 'الأسئلة | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>

<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_professor.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-4xl">

    <!-- Header -->
    <div class="mb-7">
        <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
            <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
                  style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="message-circle" style="width:17px;height:17px;"></i>
            </span>
            أسئلة الطلاب
            <?php if ($pendingCount > 0): ?>
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold">
                <?php echo $pendingCount; ?>
            </span>
            <?php endif; ?>
        </h1>
        <p class="text-slate-500 mt-1 text-sm">راجع وأجب على أسئلة طلابك</p>
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
    <div class="flex items-center gap-3 flex-wrap mb-6">
        <div class="flex gap-2">
            <?php foreach (['all'=>'الكل','pending'=>'معلقة','answered'=>'مجاوَبة'] as $f=>$lbl): ?>
            <a href="?filter=<?php echo $f; ?>&course_id=<?php echo $courseId; ?>"
               class="px-4 py-2 rounded-full text-sm font-bold transition
               <?php echo $filter===$f ? 'btn-primary-nagah shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                <?php echo $lbl; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($courses)): ?>
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
            <select name="course_id" onchange="this.form.submit()" class="field-input py-2 text-sm w-auto">
                <option value="0">— كل الكورسات —</option>
                <?php foreach ($courses as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo $courseId===(int)$c['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['course_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>
    </div>

    <!-- Questions list -->
    <?php if (empty($questions)): ?>
    <div class="glass rounded-3xl p-14 text-center text-slate-400">
        <i data-lucide="message-circle" style="width:48px;height:48px;" class="mx-auto mb-3 opacity-30"></i>
        <p class="text-sm">لا توجد أسئلة</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($questions as $q): ?>
        <div class="glass rounded-3xl p-5 reveal" id="q-<?php echo $q['id']; ?>">
            <!-- Question -->
            <div class="flex items-start gap-4 mb-4">
                <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                      style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                    <?php echo mb_substr($q['student_name'], 0, 1); ?>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <p class="font-bold text-sm text-slate-800"><?php echo htmlspecialchars($q['student_name']); ?></p>
                        <span class="tag-pill text-xs px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($q['course_name']); ?></span>
                        <?php if ($q['status'] === 'pending'): ?>
                        <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">
                            <i data-lucide="clock" style="width:10px;height:10px;"></i> معلق
                        </span>
                        <?php else: ?>
                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">
                            <i data-lucide="check-circle" style="width:10px;height:10px;"></i> مجاوَب
                        </span>
                        <?php endif; ?>
                        <span class="text-xs text-slate-400 mr-auto">
                            <?php echo date('d/m/Y H:i', strtotime($q['created_at'])); ?>
                        </span>
                    </div>
                    <p class="text-slate-700 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($q['question_text'])); ?></p>
                </div>
            </div>

            <!-- Existing answer -->
            <?php if ($q['answer_text']): ?>
            <div class="mr-12 p-4 rounded-2xl bg-green-50 border border-green-100">
                <p class="text-xs font-bold text-green-700 mb-1 flex items-center gap-1">
                    <i data-lucide="check-circle" style="width:12px;height:12px;"></i> إجابتك
                </p>
                <p class="text-sm text-slate-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($q['answer_text'])); ?></p>
                <?php if ($q['answered_at']): ?>
                <p class="text-xs text-slate-400 mt-1"><?php echo date('d/m/Y H:i', strtotime($q['answered_at'])); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Answer form (show for pending OR allow edit) -->
            <?php if ($q['status'] === 'pending'): ?>
            <div class="mr-12 mt-3">
                <form method="POST">
                    <input type="hidden" name="csrf_token"   value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="question_id"  value="<?php echo $q['id']; ?>">
                    <textarea name="answer_text" required rows="3"
                              class="field-input resize-none w-full mb-2 text-sm"
                              placeholder="اكتب إجابتك هنا…"></textarea>
                    <button type="submit"
                            class="btn-primary-nagah inline-flex items-center gap-2 px-5 py-2 rounded-full text-sm font-bold shadow hover:-translate-y-0.5 transition-all">
                        <i data-lucide="send" style="width:14px;height:14px;"></i> إرسال الإجابة
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</main>
</div>

<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
