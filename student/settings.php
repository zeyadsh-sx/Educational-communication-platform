<?php
require_once __DIR__ . '/_layout.php';

renderStudentPage('الإعدادات', function () {
    ?>
    <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-cog" style="color: var(--primary);"></i> الإعدادات</h1>
    <p style="color: var(--text-secondary); margin-bottom: 2rem;">إدارة حسابك وتفضيلاتك</p>

    <div class="card glass" style="padding: 2rem; max-width: 600px;">
        <form>
            <div class="form-group">
                <label class="form-label">الإشعارات</label>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" checked> إشعارات الدروس الجديدة
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" checked> إشعارات الواجبات
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" checked> إشعارات الامتحانات
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" checked> إشعارات الحصص المباشرة
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">كلمة المرور الجديدة</label>
                <input type="password" class="form-control" placeholder="اتركها فارغة إن لم ترد التغيير">
            </div>
            <button type="button" class="btn btn-primary" onclick="alert('تم حفظ الإعدادات!')">
                <i class="fas fa-save"></i> حفظ التغييرات
            </button>
        </form>
    </div>
    <?php
});
