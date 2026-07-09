<?php
require_once __DIR__ . '/_layout.php';

renderStudentPage('الشهادات', function () {
    ?>
    <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-certificate" style="color: var(--accent);"></i> الشهادات</h1>
    <p style="color: var(--text-secondary); margin-bottom: 2rem;">شهادات إتمام الكورسات</p>

    <div class="features-grid">
        <div class="card glass" style="padding: 2rem; text-align: center; border: 2px dashed var(--accent);">
            <i class="fas fa-certificate" style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;"></i>
            <h3>شهادة إتمام - الرياضيات</h3>
            <p style="font-size: 0.9rem;">تم إصدارها في 15/06/2026</p>
            <a href="#" class="btn btn-accent btn-sm" onclick="alert('جاري تحميل الشهادة...'); return false;">
                <i class="fas fa-download"></i> تحميل PDF
            </a>
        </div>
        <div class="card glass" style="padding: 2rem; text-align: center; opacity: 0.6;">
            <i class="fas fa-lock" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
            <h3>شهادة إتمام - الفيزياء</h3>
            <p style="font-size: 0.9rem;">أكمل 80% من الكورس للحصول على الشهادة</p>
            <div style="background: var(--bg-tertiary); border-radius: var(--radius-full); height: 8px; margin: 1rem 0;">
                <div style="background: var(--primary); width: 65%; height: 100%; border-radius: var(--radius-full);"></div>
            </div>
            <small>65% مكتمل</small>
        </div>
    </div>
    <?php
});
