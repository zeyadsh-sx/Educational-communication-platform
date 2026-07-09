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

// -- settings table helper --
function getSetting(PDO $pdo, string $key, string $default = ''): string {
    try {
        $s = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key=?");
        $s->execute([$key]);
        $v = $s->fetchColumn();
        return $v !== false ? (string)$v : $default;
    } catch (PDOException $e) { return $default; }
}

function saveSetting(PDO $pdo, string $key, string $value): void {
    $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value)
                   VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")
        ->execute([$key, $value]);
}

// -- ensure table exists --
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id            INT PRIMARY KEY AUTO_INCREMENT,
        setting_key   VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// POST — save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $keys = [
        'site_name','site_tagline','site_email','site_phone',
        'site_whatsapp','site_address','working_hours',
        'facebook_url','youtube_url','instagram_url','telegram_url',
        'bank_name','bank_account','bank_holder','vodafone_cash','orange_cash',
        'about_text','maintenance_mode',
    ];

    // Logo upload
    if (!empty($_FILES['site_logo']['name']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/svg+xml','image/webp'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $_FILES['site_logo']['tmp_name']);
        finfo_close($finfo);
        if (in_array($mime, $allowed)) {
            $ext   = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
            $fname = 'logo_' . time() . '.' . strtolower($ext);
            $dir   = __DIR__ . '/../uploads/settings';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $dir . '/' . $fname)) {
                saveSetting($pdo, 'site_logo', 'uploads/settings/' . $fname);
            }
        }
    }

    foreach ($keys as $k) {
        if (isset($_POST[$k])) {
            saveSetting($pdo, $k, trim($_POST[$k]));
        }
    }

    $message = 'تم حفظ الإعدادات بنجاح ✅';
    $msgKind = 'success';
}

// Load current settings
$s = [];
foreach ([
    'site_name'        => 'أكاديمية ماستر',
    'site_tagline'     => 'منصة الثانوية العامة والبكالوريا',
    'site_email'       => 'info@masteracademy.eg',
    'site_phone'       => '+20 100 123 4567',
    'site_whatsapp'    => '01001234567',
    'site_address'     => 'القاهرة، مصر',
    'working_hours'    => 'السبت – الخميس، 9ص – 9م',
    'facebook_url'     => 'https://facebook.com',
    'youtube_url'      => 'https://youtube.com',
    'instagram_url'    => 'https://instagram.com',
    'telegram_url'     => 'https://t.me',
    'bank_name'        => 'بنك مصر',
    'bank_account'     => '1234567890',
    'bank_holder'      => 'أكاديمية ماستر',
    'vodafone_cash'    => '01001234567',
    'orange_cash'      => '01201234567',
    'about_text'       => 'أكاديمية ماستر — منصة تعليمية رائدة للثانوية العامة والبكالوريا.',
    'maintenance_mode' => '0',
    'site_logo'        => '',
] as $key => $def) {
    $s[$key] = getSetting($pdo, $key, $def);
}

$tabs = [
    ['general',  'عام',         'settings'],
    ['contact',  'التواصل',     'phone'],
    ['social',   'السوشيال',    'share-2'],
    ['payment',  'بيانات الدفع','credit-card'],
    ['advanced', 'متقدم',       'sliders'],
];
$activeTab = in_array($_GET['tab'] ?? '', array_column($tabs, 0)) ? $_GET['tab'] : 'general';

$_activeSidebar = 'settings';
$pageTitle = 'إعدادات الموقع | أكاديمية ماستر';
require __DIR__ . '/../includes/nagah/head.php';
require __DIR__ . '/../includes/nagah/nav.php';
?>
<div class="flex min-h-[calc(100vh-64px)]">
<?php require __DIR__ . '/../includes/sidebars/sidebar_admin.php'; ?>

<main class="flex-1 min-w-0 py-8 px-5 sm:px-8 overflow-y-auto">
<div class="max-w-3xl">

    <div class="mb-7">
        <h1 class="display font-semibold text-2xl text-slate-900 flex items-center gap-3">
            <span class="w-9 h-9 rounded-2xl flex items-center justify-center text-white"
                  style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                <i data-lucide="settings" style="width:17px;height:17px;"></i>
            </span>
            إعدادات الموقع
        </h1>
        <p class="text-slate-500 mt-1 text-sm">إدارة بيانات السنتر ووسائل التواصل وطرق الدفع</p>
    </div>

    <?php if ($message): ?>
    <div class="rounded-2xl px-5 py-3.5 text-sm font-medium mb-6 flex items-center gap-3
        <?php echo $msgKind==='success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="flex gap-1 mb-7 bg-slate-100 p-1 rounded-2xl overflow-x-auto">
        <?php foreach ($tabs as [$t,$lbl,$icon]): ?>
        <a href="?tab=<?php echo $t; ?>"
           class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition-all
           <?php echo $activeTab===$t ? 'bg-white shadow text-purple-700' : 'text-slate-500 hover:text-slate-700'; ?>">
            <i data-lucide="<?php echo $icon; ?>" style="width:13px;height:13px;"></i>
            <?php echo $lbl; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

    <?php
    // ── helper ──
    function field(string $name, string $label, string $val, string $type='text', string $placeholder='', bool $full=false): void {
        $col = $full ? 'sm:col-span-2' : '';
        echo "<div class='$col'>";
        echo "<label class='block text-sm font-semibold mb-1.5 text-slate-800'>$label</label>";
        echo "<input type='$type' name='$name' value='".htmlspecialchars($val)."' class='field-input' placeholder='".htmlspecialchars($placeholder)."'>";
        echo "</div>";
    }
    ?>

        <!-- TAB: General -->
        <?php if ($activeTab === 'general'): ?>
        <div class="glass rounded-3xl p-6 space-y-5">
            <h2 class="font-bold text-slate-700 flex items-center gap-2">
                <i data-lucide="building-2" style="width:15px;height:15px;color:#7c3aed"></i> بيانات السنتر
            </h2>
            <div class="grid sm:grid-cols-2 gap-5">
                <?php field('site_name',    'اسم الأكاديمية',  $s['site_name'],    'text', 'أكاديمية ماستر'); ?>
                <?php field('site_tagline', 'الشعار الفرعي',   $s['site_tagline'], 'text', 'منصة الثانوية العامة'); ?>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">عن الأكاديمية</label>
                    <textarea name="about_text" rows="3" class="field-input resize-none"><?php echo htmlspecialchars($s['about_text']); ?></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800">شعار الأكاديمية</label>
                    <?php if ($s['site_logo']): ?>
                    <img src="<?php echo $base.'/'.$s['site_logo']; ?>" class="h-14 mb-2 object-contain" alt="الشعار">
                    <?php endif; ?>
                    <input type="file" name="site_logo" accept="image/*" class="field-input py-2 text-sm">
                </div>
            </div>
        </div>

        <!-- TAB: Contact -->
        <?php elseif ($activeTab === 'contact'): ?>
        <div class="glass rounded-3xl p-6 space-y-5">
            <h2 class="font-bold text-slate-700 flex items-center gap-2">
                <i data-lucide="phone" style="width:15px;height:15px;color:#2563EB"></i> بيانات التواصل
            </h2>
            <div class="grid sm:grid-cols-2 gap-5">
                <?php field('site_email',    'البريد الإلكتروني', $s['site_email'],    'email', 'info@example.com'); ?>
                <?php field('site_phone',    'رقم الهاتف',        $s['site_phone'],    'text',  '+20 100 000 0000'); ?>
                <?php field('site_whatsapp', 'رقم واتساب',        $s['site_whatsapp'], 'text',  '01xxxxxxxxx'); ?>
                <?php field('site_address',  'العنوان',           $s['site_address'],  'text',  'القاهرة، مصر'); ?>
                <?php field('working_hours', 'ساعات العمل',       $s['working_hours'], 'text',  'السبت – الخميس'); ?>
            </div>
        </div>

        <!-- TAB: Social -->
        <?php elseif ($activeTab === 'social'): ?>
        <div class="glass rounded-3xl p-6 space-y-5">
            <h2 class="font-bold text-slate-700 flex items-center gap-2">
                <i data-lucide="share-2" style="width:15px;height:15px;color:#2563EB"></i> وسائل التواصل
            </h2>
            <div class="grid sm:grid-cols-2 gap-5">
                <?php foreach ([
                    ['facebook_url',  'Facebook',  'https://facebook.com/...'],
                    ['youtube_url',   'YouTube',   'https://youtube.com/...'],
                    ['instagram_url', 'Instagram', 'https://instagram.com/...'],
                    ['telegram_url',  'Telegram',  'https://t.me/...'],
                ] as [$key,$lbl,$ph]): ?>
                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-slate-800"><?php echo $lbl; ?></label>
                    <input type="url" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($s[$key]); ?>"
                           class="field-input" placeholder="<?php echo $ph; ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TAB: Payment -->
        <?php elseif ($activeTab === 'payment'): ?>
        <div class="glass rounded-3xl p-6 space-y-5">
            <h2 class="font-bold text-slate-700 flex items-center gap-2">
                <i data-lucide="credit-card" style="width:15px;height:15px;color:#16a34a"></i> بيانات الدفع
            </h2>
            <div class="grid sm:grid-cols-2 gap-5">
                <?php field('bank_name',    'اسم البنك',       $s['bank_name'],    'text', 'بنك مصر'); ?>
                <?php field('bank_account', 'رقم الحساب',      $s['bank_account'], 'text'); ?>
                <?php field('bank_holder',  'اسم صاحب الحساب', $s['bank_holder'],  'text'); ?>
                <?php field('vodafone_cash','فودافون كاش',     $s['vodafone_cash'],'text', '01xxxxxxxxx'); ?>
                <?php field('orange_cash',  'أورنج كاش',       $s['orange_cash'],  'text', '01xxxxxxxxx'); ?>
            </div>
            <div class="rounded-2xl p-4 bg-blue-50 border border-blue-100 text-sm text-blue-800 flex items-start gap-2">
                <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;margin-top:1px;color:#2563EB"></i>
                هذه البيانات تظهر للطلاب عند رفع إيصال الدفع.
            </div>
        </div>

        <!-- TAB: Advanced -->
        <?php elseif ($activeTab === 'advanced'): ?>
        <div class="glass rounded-3xl p-6 space-y-5">
            <h2 class="font-bold text-slate-700 flex items-center gap-2">
                <i data-lucide="sliders" style="width:15px;height:15px;color:#dc2626"></i> إعدادات متقدمة
            </h2>
            <div class="flex items-center justify-between p-4 rounded-2xl border-2 border-slate-200">
                <div>
                    <p class="font-semibold text-slate-800">وضع الصيانة</p>
                    <p class="text-xs text-slate-500 mt-0.5">عند التفعيل يرى الزوار صفحة صيانة</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer"
                           <?php echo $s['maintenance_mode']==='1' ? 'checked' : ''; ?>>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer
                                peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px]
                                after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5
                                after:transition-all peer-checked:after:translate-x-full"></div>
                </label>
            </div>
            <div class="rounded-2xl p-4 bg-amber-50 border border-amber-200 text-sm text-amber-800 flex items-start gap-2">
                <i data-lucide="alert-triangle" style="width:15px;height:15px;flex-shrink:0;margin-top:1px;color:#d97706"></i>
                لا تُفعّل وضع الصيانة إلا عند الضرورة.
            </div>
        </div>
        <?php endif; ?>

        <!-- Save button -->
        <div class="mt-6">
            <button type="submit"
                    class="btn-primary-nagah inline-flex items-center gap-2 px-8 py-3 rounded-full font-bold shadow-lg hover:-translate-y-0.5 transition-all">
                <i data-lucide="save" style="width:16px;height:16px;"></i> حفظ الإعدادات
            </button>
        </div>
    </form>

</div>
</main>
</div>
<?php require __DIR__ . '/../includes/nagah/footer.php'; ?>
