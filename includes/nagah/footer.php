<?php $base = nagahBaseUrl(); ?>
<footer class="w-full py-10 border-t border-slate-100 bg-slate-50">
    <div class="max-w-7xl mx-auto px-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background:linear-gradient(135deg,#2563EB,#60A5FA)">
                <i data-lucide="graduation-cap" style="width:18px;height:18px;"></i>
            </span>
            <span class="display font-semibold text-slate-900">أكاديمية ماستر</span>
        </div>
        <p class="text-sm text-slate-500">&copy; <?php echo date('Y'); ?> أكاديمية ماستر. جميع الحقوق محفوظة.</p>
    </div>
</footer>

<script>
document.getElementById('nagahMobileBtn')?.addEventListener('click', () => {
    document.getElementById('nagahMobileMenu')?.classList.toggle('hidden');
});

function animateCounter(el) {
    const target = parseInt(el.dataset.target, 10);
    const suffix = el.dataset.suffix || '';
    const dur = 1600;
    const start = performance.now();
    function tick(now) {
        const p = Math.min((now - start) / dur, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.floor(eased * target).toLocaleString('ar-EG') + suffix;
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = target.toLocaleString('ar-EG') + suffix;
    }
    requestAnimationFrame(tick);
}

const statObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.querySelectorAll('.stat-num').forEach(animateCounter);
            statObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.4 });

const statsSection = document.getElementById('stats-section');
if (statsSection) statObserver.observe(statsSection);

if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
</body>
</html>
