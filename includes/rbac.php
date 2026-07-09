<?php
/**
 * =====================================================
 * RBAC — Role-Based Access Control
 * أكاديمية ماستر
 * =====================================================
 *
 * الاستخدام:
 *   require_once __DIR__ . '/rbac.php';
 *
 *   // التحقق من صلاحية واحدة:
 *   if (can('manage_users')) { ... }
 *
 *   // إيقاف التنفيذ إذا لا توجد صلاحية:
 *   gate('manage_subscriptions');
 *
 *   // التحقق من ملكية مورد (course / grade):
 *   assertOwns($course['professor_id']);
 */

if (session_status() === PHP_SESSION_NONE) session_start();

/* ══════════════════════════════════════════════════════
   1. خريطة الصلاحيات لكل role
══════════════════════════════════════════════════════ */
const RBAC_PERMISSIONS = [

    /* ───── مدير النظام ───── */
    'admin' => [
        // المستخدمون
        'view_users', 'add_users', 'edit_users', 'delete_users',
        'toggle_user_active', 'reset_user_password',
        // المواد والكورسات
        'view_all_courses', 'create_course', 'edit_any_course', 'delete_any_course',
        'assign_professor',
        // الصفوف
        'manage_grades_system',
        // الاشتراكات
        'view_all_subscriptions', 'approve_subscription', 'reject_subscription',
        'cancel_subscription',
        // المدفوعات
        'view_all_payments', 'approve_payment', 'reject_payment',
        // المحتوى
        'create_any_announcement', 'delete_any_announcement', 'manage_banners',
        // الإحصائيات
        'view_analytics',
        // الإعدادات
        'manage_settings',
        // الدرجات والحضور (كل شيء)
        'view_all_grades', 'view_all_attendance',
        // لوحة التحكم
        'access_admin_panel',
    ],

    /* ───── معلم ───── */
    'professor' => [
        // كورساته فقط
        'view_own_courses', 'create_course', 'edit_own_course', 'delete_own_course',
        // المواد الدراسية
        'upload_material', 'delete_own_material',
        // طلاب مواده
        'view_enrolled_students', 'track_student_progress',
        // الواجبات
        'create_homework', 'edit_own_homework', 'delete_own_homework',
        'grade_homework', 'add_homework_feedback',
        // الامتحانات
        'create_exam', 'edit_own_exam', 'delete_own_exam',
        'add_exam_questions', 'view_exam_results',
        // الحضور
        'record_attendance', 'edit_attendance', 'view_course_attendance',
        // الإعلانات (لطلاب مادته فقط)
        'create_own_announcement',
        // الدرجات
        'add_grade', 'edit_own_grade', 'export_grades',
        // الملف الشخصي
        'edit_own_profile', 'change_own_password', 'change_own_photo',
        // لوحة التحكم
        'access_professor_panel',
    ],

    /* ───── طالب ───── */
    'student' => [
        // الكورسات
        'view_enrolled_courses', 'watch_videos', 'download_materials',
        'track_own_progress',
        // الواجبات
        'view_homework', 'submit_homework', 'view_homework_grade',
        // الامتحانات
        'take_exam', 'view_exam_result', 'review_exam_answers',
        // الدرجات
        'view_own_grades',
        // الحضور
        'view_own_attendance',
        // الإعلانات
        'view_own_announcements',
        // الاشتراكات
        'view_own_subscriptions', 'create_subscription',
        'upload_payment_receipt', 'renew_subscription',
        // الملف الشخصي
        'edit_own_profile', 'change_own_password', 'change_own_photo',
        // لوحة التحكم
        'access_student_panel',
    ],
];

/* ══════════════════════════════════════════════════════
   2. can() — هل لدى المستخدم الحالي هذه الصلاحية؟
══════════════════════════════════════════════════════ */
function can(string $permission): bool
{
    $role = $_SESSION['user_type'] ?? '';
    return in_array($permission, RBAC_PERMISSIONS[$role] ?? [], true);
}

/* ══════════════════════════════════════════════════════
   3. cannot() — عكس can()
══════════════════════════════════════════════════════ */
function cannot(string $permission): bool
{
    return !can($permission);
}

/* ══════════════════════════════════════════════════════
   4. gate() — أوقف التنفيذ إذا لا توجد صلاحية
══════════════════════════════════════════════════════ */
function gate(string $permission, ?string $redirectTo = null): void
{
    if (!isLoggedIn()) {
        $base = function_exists('getBaseUrl') ? getBaseUrl() : '';
        $back = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header("Location: {$base}/auth/login.php?redirect={$back}");
        exit;
    }
    if (cannot($permission)) {
        accessDenied($permission);
    }
}

/* ══════════════════════════════════════════════════════
   5. assertOwns() — تحقق من ملكية مورد
      $ownerId = المعرف المسجل كـ owner في الـ DB
══════════════════════════════════════════════════════ */
function assertOwns(int $ownerId, string $msg = ''): void
{
    $uid = (int)($_SESSION['user_id'] ?? 0);
    // الأدمن يملك كل شيء
    if (isAdmin()) return;
    if ($uid !== $ownerId) {
        accessDenied('ownership', $msg ?: 'ليس لديك صلاحية التعديل على هذا المورد');
    }
}

/* ══════════════════════════════════════════════════════
   6. canAccessRoute() — خريطة مسارات ← صلاحية مطلوبة
══════════════════════════════════════════════════════ */
const ROUTE_PERMISSION_MAP = [
    // Admin panel
    'admin_panel/dashboard'     => 'access_admin_panel',
    'admin_panel/users'         => 'view_users',
    'admin_panel/subscriptions' => 'view_all_subscriptions',
    'admin_panel/payments'      => 'view_all_payments',
    'admin_panel/settings'      => 'manage_settings',
    'admin_panel/analytics'     => 'view_analytics',
    // Professor panel
    'professor/dashboard'       => 'access_professor_panel',
    'professor/students'        => 'view_enrolled_students',
    'professor/attendance'      => 'record_attendance',
    'professor/grades'          => 'add_grade',
    // Student panel
    'student/dashboard'         => 'access_student_panel',
    'student/grades'            => 'view_own_grades',
    'student/attendance'        => 'view_own_attendance',
    'student/homework'          => 'view_homework',
    'student/exams'             => 'take_exam',
    // Shared
    'subscriptions/subscribe'   => 'create_subscription',
    'subscriptions/my'          => 'view_own_subscriptions',
    'payments/upload'           => 'upload_payment_receipt',
    'materials/upload'          => 'upload_material',
    'announcements/create'      => 'create_own_announcement',
    'courses/create'            => 'create_course',
    'admin/manage_professors'   => 'view_users',
    'admin/dashboard'           => 'access_professor_panel',
];

function checkCurrentRoute(): void
{
    $uri  = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = str_replace('\\', '/', function_exists('getBaseUrl') ? getBaseUrl() : '');
    $path = ltrim(str_replace($base . '/', '', $uri), '/');
    $path = preg_replace('/\.php$/', '', $path);

    foreach (ROUTE_PERMISSION_MAP as $route => $perm) {
        if (str_starts_with($path, $route)) {
            gate($perm);
            return;
        }
    }
}

/* ══════════════════════════════════════════════════════
   7. accessDenied() — عرض صفحة 403
══════════════════════════════════════════════════════ */
function accessDenied(string $permission = '', string $customMsg = ''): never
{
    http_response_code(403);

    // إذا كان طلب API — أرجع JSON
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (str_contains($accept, 'application/json') ||
        str_starts_with($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'غير مصرح — ' . $permission]);
        exit;
    }

    // صفحة HTML
    $base = function_exists('getBaseUrl') ? getBaseUrl() : '';
    $role = $_SESSION['user_type'] ?? '';
    $dashMap = [
        'admin'     => $base . '/admin_panel/dashboard.php',
        'professor' => $base . '/professor/dashboard.php',
        'student'   => $base . '/student/dashboard.php',
    ];
    $dashUrl  = $dashMap[$role] ?? $base . '/index.php';
    $userName = htmlspecialchars($_SESSION['full_name'] ?? 'المستخدم');

    require_once __DIR__ . '/access_denied.php';
    renderAccessDenied($userName, $dashUrl, $customMsg);
    exit;
}
