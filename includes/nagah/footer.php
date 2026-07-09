<?php $base = nagahBaseUrl(); ?>

<!-- ===== FULL FOOTER ===== -->
<footer class="w-full bg-slate-900 text-slate-300">

    <!-- Top band -->
    <div class="max-w-7xl mx-auto px-5 pt-14 pb-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

            <!-- Brand col -->
            <div class="lg:col-span-1">
                <a href="<?php echo $base; ?>/index.php" class="flex items-center gap-2.5 mb-4">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-lg"
                          style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                        <i data-lucide="graduation-cap" style="width:20px;height:20px;"></i>
                    </span>
                    <div>
                        <span class="display font-semibold text-white text-base block">أكاديمية ماستر</span>
                        <span class="text-[10px] text-slate-400 block">ثانوية عامة · بكالوريا</span>
                    </div>
                </a>
                <p class="text-sm text-slate-400 leading-relaxed mb-5">
                    منصة تعليمية متكاملة لطلاب الثانوية العامة والبكالوريا في مصر — دروس مسجلة، حصص مباشرة، وامتحانات تفاعلية.
                </p>
                <!-- Social icons -->
                <div class="flex gap-3">
                    <?php foreach ([
                        ['https://facebook.com',  'facebook',   '#1877F2'],
                        ['https://youtube.com',   'youtube',    '#FF0000'],
                        ['https://wa.me/201001234567', 'message-circle', '#25D366'],
                        ['https://t.me',          'send',       '#0088CC'],
                        ['https://instagram.com', 'instagram',  '#E1306C'],
                    ] as [$url, $icon, $color]): ?>
                    <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer"
                       class="w-9 h-9 rounded-xl flex items-center justify-center transition hover:scale-110"
                       style="background:<?php echo $color; ?>22">
                        <i data-lucide="<?php echo $icon; ?>" style="width:16px;height:16px;color:<?php echo $color; ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick links -->
            <div>
                <h4 class="font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="link" style="width:14px;height:14px;color:#60A5FA"></i> روابط سريعة
                </h4>
                <ul class="space-y-2.5">
                    <?php foreach ([
                        ['/index.php',           'الصفحة الرئيسية'],
                        ['/courses/list.php',    'الكورسات الدراسية'],
                        ['/pages/teachers.php',  'فريق المعلمين'],
                        ['/pages/schedule.php',  'الجدول الأسبوعي'],
                        ['/pages/about.php',     'عن الأكاديمية'],
                        ['/pages/contact.php',   'تواصل معنا'],
                    ] as [$href, $label]): ?>
                    <li>
                        <a href="<?php echo $base . $href; ?>"
                           class="text-sm text-slate-400 hover:text-white transition flex items-center gap-2 group">
                            <i data-lucide="chevron-left" style="width:12px;height:12px;color:#60A5FA" class="group-hover:translate-x-[-2px] transition-transform"></i>
                            <?php echo $label; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Subjects -->
            <div>
                <h4 class="font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="book-open" style="width:14px;height:14px;color:#60A5FA"></i> المواد الدراسية
                </h4>
                <ul class="space-y-2.5">
                    <?php foreach ([
                        'الرياضيات', 'الفيزياء', 'الكيمياء', 'الأحياء',
                        'اللغة العربية', 'اللغة الإنجليزية', 'التاريخ', 'الجغرافيا',
                    ] as $subject): ?>
                    <li>
                        <a href="<?php echo $base; ?>/courses/list.php"
                           class="text-sm text-slate-400 hover:text-white transition flex items-center gap-2 group">
                            <i data-lucide="chevron-left" style="width:12px;height:12px;color:#60A5FA" class="group-hover:translate-x-[-2px] transition-transform"></i>
                            <?php echo $subject; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Contact info -->
            <div>
                <h4 class="font-bold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="phone" style="width:14px;height:14px;color:#60A5FA"></i> تواصل معنا
                </h4>
                <ul class="space-y-3">
                    <?php foreach ([
                        ['phone',   '+20 100 123 4567',        'tel:+201001234567'],
                        ['message-circle', '+20 100 123 4567 (واتساب)', 'https://wa.me/201001234567'],
                        ['mail',    'info@masteracademy.eg',   'mailto:info@masteracademy.eg'],
                        ['map-pin', 'القاهرة، مصر',            '#'],
                        ['clock',   'السبت – الخميس، 9ص – 9م','#'],
                    ] as [$icon, $text, $href]): ?>
                    <li>
                        <a href="<?php echo $href; ?>"
                           class="flex items-start gap-2.5 text-sm text-slate-400 hover:text-white transition group">
                            <i data-lucide="<?php echo $icon; ?>"
                               style="width:15px;height:15px;color:#60A5FA;flex-shrink:0;margin-top:1px"></i>
                            <?php echo $text; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <!-- CTA -->
                <a href="<?php echo $base; ?>/auth/register.php"
                   class="mt-6 inline-flex items-center gap-2 w-full justify-center py-3 rounded-full btn-primary-nagah font-bold text-sm shadow-lg hover:-translate-y-0.5 transition-all">
                    <i data-lucide="user-plus" style="width:15px;height:15px;"></i>
                    سجّل الآن مجاناً
                </a>
            </div>

        </div>
    </div>

    <!-- Bottom bar -->
    <div class="border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-5 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                &copy; <?php echo date('Y'); ?> أكاديمية ماستر. جميع الحقوق محفوظة.
            </p>
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <a href="#" class="hover:text-slate-300 transition">سياسة الخصوصية</a>
                <span class="w-px h-3 bg-slate-700"></span>
                <a href="#" class="hover:text-slate-300 transition">شروط الاستخدام</a>
                <span class="w-px h-3 bg-slate-700"></span>
                <a href="<?php echo $base; ?>/pages/contact.php" class="hover:text-slate-300 transition">الدعم الفني</a>
            </div>
        </div>
    </div>
</footer>

<script>
/* ── Stat counters ── */
function animateCounter(el) {
    const target = parseInt(el.dataset.target, 10);
    const suffix = el.dataset.suffix || '';
    const dur = 1600;
    const start = performance.now();
    (function tick(now) {
        const p = Math.min((now - start) / dur, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.floor(eased * target).toLocaleString('ar-EG') + suffix;
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = target.toLocaleString('ar-EG') + suffix;
    })(performance.now());
}

const statObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.querySelectorAll('.stat-num').forEach(animateCounter);
            statObserver.unobserve(e.target);
        }
    });
}, { threshold: 0.4 });

const statsSection = document.getElementById('stats-section');
if (statsSection) statObserver.observe(statsSection);

/* ── Reveal on scroll ── */
const revealObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.style.animationPlayState = 'running';
            revealObserver.unobserve(e.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.reveal').forEach(el => {
    el.style.animationPlayState = 'paused';
    revealObserver.observe(el);
});

/* ── Lucide icons ── */
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
</body>
</html>
