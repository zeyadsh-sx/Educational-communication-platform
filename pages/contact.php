<?php
require_once __DIR__ . '/../includes/nagah/layout.php';
require_once __DIR__ . '/../includes/security.php';

nagahPageStart('تواصل معنا | أكاديمية ماستر');

$base    = nagahBaseUrl();
$success = '';
$error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $error = 'توكن الأمان غير صحيح';
    } else {
        // Here you'd normally send an email / save to DB
        $success = 'تم إرسال رسالتك بنجاح! سنتواصل معك خلال 24 ساعة.';
    }
}
?>

<!-- Hero -->
<section class="relative w-full overflow-hidden py-16 sm:py-20">
    <span class="blob" style="width:360px;height:360px;background:#60A5FA;top:-100px;right:-80px;"></span>
    <span class="blob" style="width:300px;height:300px;background:#F59E0B;bottom:-80px;left:-60px;opacity:.3;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>
    <div class="relative z-10 max-w-3xl mx-auto px-5 text-center">
        <span class="reveal tag-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">
            <i data-lucide="mail" style="width:13px;height:13px;"></i> تواصل
        </span>
        <h1 class="display reveal mt-5 text-3xl sm:text-4xl font-semibold text-slate-900" style="animation-delay:.1s">تواصل معنا</h1>
        <p class="reveal mt-3 text-slate-500 max-w-md mx-auto" style="animation-delay:.2s">نحن هنا للإجابة على جميع استفساراتك — لا تتردد في التواصل</p>
    </div>
</section>

<main class="max-w-6xl mx-auto px-5 pb-20">

    <?php if ($success): ?>
    <div class="mb-8 p-5 rounded-2xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
        <i data-lucide="check-circle" style="width:20px;height:20px;color:#16a34a;shrink:0"></i>
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="mb-8 p-5 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
        <i data-lucide="alert-circle" style="width:20px;height:20px;color:#dc2626;shrink:0"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-5 gap-8 mb-10">

        <!-- Contact info cards -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ([
                ['phone',       '+20 100 123 4567',        'الهاتف',     '#2563EB','rgba(37,99,235,.1)',   'tel:+201001234567'],
                ['message-circle','+20 100 123 4567 (واتساب)','واتساب',   '#16a34a','rgba(22,163,74,.1)', 'https://wa.me/201001234567'],
                ['mail',        'info@masteracademy.eg',   'البريد',     '#F59E0B','rgba(245,158,11,.1)', 'mailto:info@masteracademy.eg'],
                ['map-pin',     'القاهرة، مصر',            'الموقع',     '#7c3aed','rgba(124,58,237,.1)', '#'],
                ['clock',       'السبت – الخميس، 9ص – 9م', 'ساعات العمل','#0ea5e9','rgba(14,165,233,.1)', '#'],
            ] as [$icon,$val,$label,$color,$bg,$href]): ?>
            <a href="<?php echo $href; ?>" class="glass rounded-2xl p-5 flex items-center gap-4 lift block hover:shadow-xl transition-all group">
                <span class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 group-hover:scale-110 transition" style="background:<?php echo $bg; ?>">
                    <i data-lucide="<?php echo $icon; ?>" style="width:20px;height:20px;color:<?php echo $color; ?>"></i>
                </span>
                <div>
                    <p class="text-xs text-slate-400 font-medium"><?php echo $label; ?></p>
                    <p class="font-semibold text-slate-800 text-sm mt-0.5"><?php echo $val; ?></p>
                </div>
            </a>
            <?php endforeach; ?>

            <!-- Social -->
            <div class="glass rounded-2xl p-5">
                <p class="text-xs text-slate-400 font-medium mb-3">تابعنا على</p>
                <div class="flex gap-3">
                    <?php foreach ([
                        ['facebook',  '#2563EB','https://facebook.com'],
                        ['youtube',   '#dc2626','https://youtube.com'],
                        ['instagram', '#e1306c','https://instagram.com'],
                        ['send',      '#0088cc','https://t.me'],
                    ] as [$icon,$color,$url]): ?>
                    <a href="<?php echo $url; ?>" target="_blank" class="w-10 h-10 rounded-xl flex items-center justify-center hover:scale-110 transition" style="background:<?php echo $color; ?>18">
                        <i data-lucide="<?php echo $icon; ?>" style="width:17px;height:17px;color:<?php echo $color; ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Contact form -->
        <div class="lg:col-span-3">
            <div class="glass rounded-3xl p-7 sm:p-9">
                <h2 class="display font-semibold text-xl mb-6">أرسل رسالتك</h2>
                <form method="POST" class="space-y-5" id="contact-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">الاسم الكامل</label>
                            <input name="name" required class="field-input" placeholder="أدخل اسمك">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1.5">رقم الهاتف</label>
                            <input name="phone" type="tel" class="field-input" placeholder="01xxxxxxxxx">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">البريد الإلكتروني</label>
                        <input type="email" name="email" required class="field-input" placeholder="example@email.com">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">الموضوع</label>
                        <select name="subject" class="field-input">
                            <option>استفسار عن كورس</option>
                            <option>مشكلة تقنية</option>
                            <option>طلب شراكة</option>
                            <option>أخرى</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">رسالتك</label>
                        <textarea name="message" required rows="5" class="field-input resize-none" placeholder="اكتب رسالتك هنا…"></textarea>
                    </div>
                    <button type="submit" class="w-full py-3.5 rounded-full font-bold btn-primary-nagah shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="send" style="width:16px;height:16px;"></i> إرسال الرسالة
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="glass rounded-3xl overflow-hidden" style="height:320px;">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3453.789!2d31.2357!3d30.0444!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzDCsDAyJzM5LjgiTiAzMcKwMTQnMDguNSJF!5e0!3m2!1sar!2seg" class="w-full h-full border-0" loading="lazy"></iframe>
    </div>

</main>

<?php nagahPageEnd(); ?>
