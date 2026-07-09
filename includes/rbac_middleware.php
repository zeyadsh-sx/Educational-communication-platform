<?php
/**
 * =====================================================
 * RBAC Middleware — يُضمَّن في أول سطر كل صفحة محمية
 * =====================================================
 *
 * مثال الاستخدام (سطر واحد فقط):
 *
 *   // لصفحة admin:
 *   require_once __DIR__ . '/../includes/rbac_middleware.php';
 *   rbacRequire('admin');
 *
 *   // لصفحة professor مع صلاحية محددة:
 *   require_once __DIR__ . '/../includes/rbac_middleware.php';
 *   rbacRequire('professor', 'add_grade');
 *
 *   // أو استخدم التحقق التلقائي بالمسار:
 *   require_once __DIR__ . '/../includes/rbac_middleware.php';
 *   rbacAuto();  // يقرأ المسار تلقائياً ويطبق الصلاحية المناسبة
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// تحميل المتطلبات
$__rbacRoot = dirname(__DIR__);
if (!function_exists('getDB'))        require_once $__rbacRoot . '/config/database.php';
if (!function_exists('isLoggedIn'))   require_once $__rbacRoot . '/includes/functions.php';
if (!function_exists('can'))          require_once $__rbacRoot . '/includes/rbac.php';

/**
 * rbacRequire — تحقق من role + صلاحية اختيارية
 *
 * @param string  $role       'admin' | 'professor' | 'student' | 'any'
 * @param string  $permission صلاحية إضافية (اختيارية)
 */
function rbacRequire(string $role, string $permission = ''): void
{
    // 1. تسجيل الدخول
    if (!isLoggedIn()) {
        $base = function_exists('getBaseUrl') ? getBaseUrl() : '';
        $back = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header("Location: {$base}/auth/login.php?redirect={$back}");
        exit;
    }

    // 2. فحص is_active
    try {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $active = $stmt->fetchColumn();
        if ($active === '0' || $active === 0) {
            session_destroy();
            $base = function_exists('getBaseUrl') ? getBaseUrl() : '';
            header("Location: {$base}/auth/login.php?error=account_disabled");
            exit;
        }
    } catch (Throwable $e) { /* جدول أو عمود غير موجود — تجاهل */ }

    // 3. role check
    $current = getCurrentUserType() ?? '';
    if ($role !== 'any' && $current !== $role) {
        accessDenied("role:{$role}");
    }

    // 4. permission check (اختيارية)
    if ($permission !== '' && cannot($permission)) {
        accessDenied($permission);
    }
}

/**
 * rbacAuto — يقرأ المسار ويطبق الصلاحية من ROUTE_PERMISSION_MAP
 */
function rbacAuto(): void
{
    if (!isLoggedIn()) {
        $base = function_exists('getBaseUrl') ? getBaseUrl() : '';
        $back = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header("Location: {$base}/auth/login.php?redirect={$back}");
        exit;
    }
    checkCurrentRoute();
}

/**
 * rbacJson — للـ API endpoints فقط
 * يُرجع JSON error ويوقف التنفيذ إذا لا توجد صلاحية
 */
function rbacJson(string ...$roles): void
{
    header('Content-Type: application/json; charset=utf-8');
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'غير مسجل الدخول']);
        exit;
    }
    $current = getCurrentUserType() ?? '';
    if (!in_array($current, $roles, true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'غير مصرح']);
        exit;
    }
}

/**
 * rbacOwnership — تحقق أن المورد يخص المستخدم الحالي (أو admin)
 */
function rbacOwnership(int $ownerId, string $msg = ''): void
{
    assertOwns($ownerId, $msg);
}
