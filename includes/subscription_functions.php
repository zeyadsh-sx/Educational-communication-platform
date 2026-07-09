<?php
/**
 * دوال نظام الاشتراكات والدفع والحضور والدرجات
 */

require_once __DIR__ . '/../config/database.php';

/* ══════════════════════════════════════════════
   SUBSCRIPTIONS
══════════════════════════════════════════════ */

function getStudentSubscriptions(int $studentId): array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT s.*, c.course_name, c.course_code,
               u.full_name AS professor_name,
               pr.status   AS payment_status,
               pr.id       AS receipt_id
        FROM   subscriptions s
        JOIN   courses c  ON s.course_id  = c.id
        JOIN   users   u  ON c.professor_id = u.id
        LEFT JOIN payment_receipts pr ON pr.subscription_id = s.id
            AND pr.id = (SELECT MAX(id) FROM payment_receipts WHERE subscription_id = s.id)
        WHERE  s.student_id = ?
        ORDER  BY s.created_at DESC
    ");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSubscriptionById(int $id): ?array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT s.*, c.course_name, c.course_code, u.full_name AS professor_name
        FROM   subscriptions s
        JOIN   courses c ON s.course_id   = c.id
        JOIN   users   u ON c.professor_id = u.id
        WHERE  s.id = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function createSubscription(int $studentId, int $courseId, string $plan): array
{
    $pdo = getDB();
    // منع التكرار
    $chk = $pdo->prepare("SELECT id, status FROM subscriptions WHERE student_id=? AND course_id=? AND status IN ('pending','active')");
    $chk->execute([$studentId, $courseId]);
    if ($row = $chk->fetch()) {
        return ['success' => false, 'message' => 'لديك اشتراك نشط أو قيد المراجعة لهذه المادة'];
    }

    // السعر
    $price = getCoursePlanPrice($courseId, $plan);

    $ins = $pdo->prepare("INSERT INTO subscriptions (student_id, course_id, plan, price, status) VALUES (?,?,?,?,'pending')");
    $ins->execute([$studentId, $courseId, $plan, $price]);
    return ['success' => true, 'id' => (int)$pdo->lastInsertId()];
}

function getCoursePlanPrice(int $courseId, string $plan): float
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT monthly_price, quarterly_price, yearly_price FROM course_pricing WHERE course_id=?");
    $stmt->execute([$courseId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return 0.0;
    return match($plan) {
        'quarterly' => (float)$row['quarterly_price'],
        'yearly'    => (float)$row['yearly_price'],
        default     => (float)$row['monthly_price'],
    };
}

function getCoursePricing(int $courseId): ?array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM course_pricing WHERE course_id=?");
    $stmt->execute([$courseId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function saveCoursePrice(int $courseId, float $monthly, float $quarterly, float $yearly): void
{
    $pdo = getDB();
    $pdo->prepare("
        INSERT INTO course_pricing (course_id, monthly_price, quarterly_price, yearly_price)
        VALUES (?,?,?,?)
        ON DUPLICATE KEY UPDATE monthly_price=VALUES(monthly_price),
            quarterly_price=VALUES(quarterly_price), yearly_price=VALUES(yearly_price)
    ")->execute([$courseId, $monthly, $quarterly, $yearly]);
}

function activateSubscription(int $subId, string $plan, int $adminId): bool
{
    $pdo = getDB();
    $months = match($plan) { 'quarterly' => 3, 'yearly' => 12, default => 1 };
    $start  = date('Y-m-d');
    $end    = date('Y-m-d', strtotime("+{$months} months"));
    $pdo->prepare("UPDATE subscriptions SET status='active', start_date=?, end_date=? WHERE id=?")->execute([$start, $end, $subId]);
    // موافقة الإيصال
    $pdo->prepare("UPDATE payment_receipts SET status='approved', reviewed_by=?, reviewed_at=NOW() WHERE subscription_id=? AND status='pending'")->execute([$adminId, $subId]);

    // enroll the student
    $sub = getSubscriptionById($subId);
    if ($sub) {
        $enroll = $pdo->prepare("INSERT IGNORE INTO course_enrollments (course_id, student_id, status) VALUES (?,?,'active')");
        $enroll->execute([$sub['course_id'], $sub['student_id']]);

        // إشعار الطالب
        sendSystemNotification($sub['student_id'], 'تم تفعيل اشتراكك في ' . $sub['course_name'] . ' بنجاح 🎉', 'general');
    }
    return true;
}

function rejectSubscription(int $subId, string $reason, int $adminId): bool
{
    $pdo = getDB();
    $pdo->prepare("UPDATE subscriptions SET status='rejected' WHERE id=?")->execute([$subId]);
    $pdo->prepare("UPDATE payment_receipts SET status='rejected', reviewed_by=?, reviewed_at=NOW(), reject_reason=? WHERE subscription_id=?")->execute([$adminId, $reason, $subId]);

    $sub = getSubscriptionById($subId);
    if ($sub) {
        sendSystemNotification($sub['student_id'], 'تم رفض طلب اشتراكك في ' . $sub['course_name'] . '. السبب: ' . $reason, 'general');
    }
    return true;
}

function getExpiringSubscriptions(int $days = 7): array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT s.*, c.course_name, u.full_name AS student_name, u.id AS uid
        FROM   subscriptions s
        JOIN   courses c ON s.course_id  = c.id
        JOIN   users   u ON s.student_id = u.id
        WHERE  s.status = 'active'
          AND  s.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
    ");
    $stmt->execute([$days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getActiveSubscriptionsCount(): int
{
    return (int)getDB()->query("SELECT COUNT(*) FROM subscriptions WHERE status='active'")->fetchColumn();
}

function getPendingSubscriptionsCount(): int
{
    return (int)getDB()->query("SELECT COUNT(*) FROM subscriptions WHERE status='pending'")->fetchColumn();
}

function getAllSubscriptionsAdmin(string $filter = 'all'): array
{
    $pdo = getDB();
    $where = match($filter) {
        'pending'  => "WHERE s.status = 'pending'",
        'active'   => "WHERE s.status = 'active'",
        'rejected' => "WHERE s.status = 'rejected'",
        'expired'  => "WHERE s.status = 'expired'",
        default    => '',
    };
    $stmt = $pdo->query("
        SELECT s.*, c.course_name, c.course_code,
               u.full_name  AS student_name, u.phone AS student_phone,
               p.full_name  AS professor_name,
               pr.id        AS receipt_id,
               pr.status    AS receipt_status,
               pr.receipt_image
        FROM   subscriptions s
        JOIN   courses c  ON s.course_id    = c.id
        JOIN   users   u  ON s.student_id   = u.id
        JOIN   users   p  ON c.professor_id = p.id
        LEFT JOIN payment_receipts pr ON pr.subscription_id = s.id
            AND pr.id = (SELECT MAX(id) FROM payment_receipts WHERE subscription_id = s.id)
        $where
        ORDER  BY s.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ══════════════════════════════════════════════
   PAYMENT RECEIPTS
══════════════════════════════════════════════ */

function uploadReceipt(int $subscriptionId, int $studentId, array $file, ?float $amount, ?string $notes): array
{
    // validate
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed)) {
        return ['success' => false, 'message' => 'نوع الملف غير مسموح — يُرجى رفع صورة فقط (JPG / PNG)'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'حجم الصورة يجب أن لا يتجاوز 5 MB'];
    }

    $dir = __DIR__ . '/../uploads/receipts';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $fileName = 'receipt_' . $subscriptionId . '_' . time() . '.' . strtolower($ext);
    $dest     = $dir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'message' => 'فشل رفع الملف — يُرجى المحاولة مجدداً'];
    }

    $pdo = getDB();
    $pdo->prepare("
        INSERT INTO payment_receipts (subscription_id, student_id, receipt_image, amount, notes, status)
        VALUES (?,?,?,?,?,'pending')
    ")->execute([$subscriptionId, $studentId, 'uploads/receipts/' . $fileName, $amount, $notes]);

    // تحديث حالة الاشتراك
    $pdo->prepare("UPDATE subscriptions SET status='pending' WHERE id=?")->execute([$subscriptionId]);

    return ['success' => true];
}

/* ══════════════════════════════════════════════
   ATTENDANCE
══════════════════════════════════════════════ */

function recordAttendance(int $courseId, int $studentId, string $date, string $status, int $profId, ?string $notes = null): void
{
    $pdo = getDB();
    $pdo->prepare("
        INSERT INTO attendance (course_id, student_id, lesson_date, status, recorded_by, notes)
        VALUES (?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE status=VALUES(status), notes=VALUES(notes), recorded_by=VALUES(recorded_by)
    ")->execute([$courseId, $studentId, $date, $status, $profId, $notes]);
}

function getCourseAttendance(int $courseId): array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT a.*, u.full_name AS student_name
        FROM   attendance a
        JOIN   users u ON a.student_id = u.id
        WHERE  a.course_id = ?
        ORDER  BY a.lesson_date DESC, u.full_name
    ");
    $stmt->execute([$courseId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStudentAttendanceRate(int $studentId, int $courseId): int
{
    $pdo   = getDB();
    $total = (int)$pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id=? AND course_id=?")->execute([$studentId,$courseId]) ? $pdo->query("SELECT COUNT(*) FROM attendance WHERE student_id=$studentId AND course_id=$courseId")->fetchColumn() : 0;
    if ($total === 0) return 0;
    $present = (int)$pdo->query("SELECT COUNT(*) FROM attendance WHERE student_id=$studentId AND course_id=$courseId AND status='present'")->fetchColumn();
    return (int)round($present / $total * 100);
}

/* ══════════════════════════════════════════════
   GRADES
══════════════════════════════════════════════ */

function addGrade(int $courseId, int $studentId, string $title, string $type, float $score, float $maxScore, int $gradedBy, ?string $notes = null): void
{
    getDB()->prepare("
        INSERT INTO grades (course_id, student_id, title, grade_type, score, max_score, notes, graded_by)
        VALUES (?,?,?,?,?,?,?,?)
    ")->execute([$courseId, $studentId, $title, $type, $score, $maxScore, $notes, $gradedBy]);
}

function getStudentGrades(int $studentId, int $courseId): array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM grades WHERE student_id=? AND course_id=? ORDER BY created_at DESC");
    $stmt->execute([$studentId, $courseId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCourseGrades(int $courseId): array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT g.*, u.full_name AS student_name
        FROM   grades g
        JOIN   users u ON g.student_id = u.id
        WHERE  g.course_id = ?
        ORDER  BY g.created_at DESC
    ");
    $stmt->execute([$courseId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ══════════════════════════════════════════════
   NOTIFICATIONS (4 types)
══════════════════════════════════════════════ */

function sendSystemNotification(int $userId, string $message, string $type = 'general'): void
{
    getDB()->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?,?,?)")
           ->execute([$userId, $message, $type]);
}

function notifyNewLesson(int $courseId, string $lessonTitle): void
{
    $pdo   = getDB();
    $stmnt = $pdo->prepare("SELECT student_id FROM course_enrollments WHERE course_id=? AND status='active'");
    $stmnt->execute([$courseId]);
    foreach ($stmnt->fetchAll(PDO::FETCH_COLUMN) as $sid) {
        sendSystemNotification($sid, "📚 درس جديد: $lessonTitle", 'new_lesson');
    }
}

function notifyNewHomework(int $courseId, string $hwTitle): void
{
    $pdo   = getDB();
    $stmnt = $pdo->prepare("SELECT student_id FROM course_enrollments WHERE course_id=? AND status='active'");
    $stmnt->execute([$courseId]);
    foreach ($stmnt->fetchAll(PDO::FETCH_COLUMN) as $sid) {
        sendSystemNotification($sid, "📝 واجب جديد: $hwTitle", 'new_homework');
    }
}

function notifyNewExam(int $courseId, string $examTitle): void
{
    $pdo   = getDB();
    $stmnt = $pdo->prepare("SELECT student_id FROM course_enrollments WHERE course_id=? AND status='active'");
    $stmnt->execute([$courseId]);
    foreach ($stmnt->fetchAll(PDO::FETCH_COLUMN) as $sid) {
        sendSystemNotification($sid, "📋 امتحان جديد: $examTitle", 'new_exam');
    }
}

function notifyExpiringSubscriptions(): void
{
    foreach (getExpiringSubscriptions(7) as $sub) {
        sendSystemNotification(
            $sub['uid'],
            "⚠️ اشتراكك في {$sub['course_name']} سينتهي في {$sub['end_date']} — جدّد الآن",
            'subscription_expiry'
        );
    }
}
