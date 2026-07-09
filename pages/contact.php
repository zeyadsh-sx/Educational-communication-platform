<?php
require_once __DIR__ . '/../includes/nagah/layout.php';
require_once __DIR__ . '/../includes/security.php';

nagahPageStart('تواصل معنا | أكاديمية ماستر');

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken(getSafePost('csrf_token', ''))) {
        $error = 'توكن الأمان غير صحيح';
    } else {
        $success = 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.';
    }
}
?>

<section class="w-full py-20">
    <div class="max-w-5xl mx-auto px-5">
        <div class="text-center mb-12">
            <span class="tag-pill inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">تواصل</span>
            <h1 class="display font-semibold text-3xl sm:text-4xl mt-4">تواصل معنا</h1>
            <p class="mt-3 text-slate-500">نحن هنا للإجابة على جميع استفساراتك</p>
        </div>

        <?php if ($success): ?><div class="mb-6 p-4 rounded-2xl bg-green-50 text-green-800 border border-green-200"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="mb-6 p-4 rounded-2xl bg-red-50 text-red-800 border border-red-200"><?php echo $error; ?></div><?php endif; ?>

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="space-y-4">
                <div class="glass rounded-2xl p-5 flex gap-4 items-start lift">
                    <span class="w-12 h-12 rounded-xl flex items-center justify-center text-white shrink-0" style="background:#2563EB"><i data-lucide="phone"></i></span>
                    <div><h4 class="font-bold">الهاتف</h4><p class="text-slate-500 text-sm">+20 100 123 4567</p></div>
                </div>
                <div class="glass rounded-2xl p-5 flex gap-4 items-start lift">
                    <span class="w-12 h-12 rounded-xl flex items-center justify-center text-white shrink-0" style="background:#F59E0B"><i data-lucide="mail"></i></span>
                    <div><h4 class="font-bold">البريد</h4><p class="text-slate-500 text-sm">info@masteracademy.eg</p></div>
                </div>
                <div class="glass rounded-2xl p-5 flex gap-4 items-start lift">
                    <span class="w-12 h-12 rounded-xl flex items-center justify-center text-white shrink-0" style="background:#0ea5e9"><i data-lucide="map-pin"></i></span>
                    <div><h4 class="font-bold">الموقع</h4><p class="text-slate-500 text-sm">القاهرة، مصر</p></div>
                </div>
            </div>
            <form method="POST" class="glass rounded-3xl p-8 space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div><label class="block text-sm font-semibold mb-1">الاسم</label><input name="name" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none"></div>
                <div><label class="block text-sm font-semibold mb-1">البريد</label><input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none"></div>
                <div><label class="block text-sm font-semibold mb-1">الرسالة</label><textarea name="message" required rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:outline-none"></textarea></div>
                <button type="submit" class="w-full py-3.5 rounded-full font-bold btn-primary-nagah shadow-lg hover:-translate-y-0.5 transition-all">إرسال الرسالة</button>
            </form>
        </div>
        <div class="mt-10 rounded-3xl overflow-hidden h-72 border border-slate-200">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3453.789!2d31.2357!3d30.0444!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzDCsDAyJzM5LjgiTiAzMcKwMTQnMDguNSJF!5e0!3m2!1sar!2seg" class="w-full h-full border-0" loading="lazy"></iframe>
        </div>
    </div>
</section>

<?php nagahPageEnd(); ?>
