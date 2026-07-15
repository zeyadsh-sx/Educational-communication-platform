/**
 * Educational Platform — Main JavaScript
 * يُحمَّل في كل الصفحات
 */

document.addEventListener('DOMContentLoaded', function () {
    initNavbar();
    initForms();
    initNotifications();
    initConfirmations();
    initEmoji();
    initPasswordToggles();
});

/* ══════════════════════════════════════════════
   Emoji (Twemoji)وز
══════════════════════════════════════════════ */
function initEmoji() {
    if (typeof twemoji === 'undefined') return;

    twemoji.parse(document.body);

    const observer = new MutationObserver(mutations => {
        const added = mutations.some(m => m.addedNodes.length > 0);
        if (added) twemoji.parse(document.body);
    });
    observer.observe(document.body, { childList: true, subtree: true });
}

/* ══════════════════════════════════════════════
   Navbar scroll effect
══════════════════════════════════════════════ */
function initNavbar() {
    const navbar = document.querySelector('.navbar, #main-nav');
    if (!navbar) return;

    window.addEventListener('scroll', () => {
        navbar.classList.toggle('nav-scrolled', window.scrollY > 50);
    }, { passive: true });
}

/* ══════════════════════════════════════════════
   Form validation (data-validate attribute)
══════════════════════════════════════════════ */
function initForms() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', e => {
            if (!validateForm(form)) e.preventDefault();
        });
    });
}

function validateForm(form) {
    let valid = true;

    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'هذا الحقل مطلوب');
            valid = false;
        } else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
            showFieldError(field, 'البريد الإلكتروني غير صحيح');
            valid = false;
        } else if (field.type === 'password' && field.dataset.minlength && field.value.length < +field.dataset.minlength) {
            showFieldError(field, `يجب أن تكون ${field.dataset.minlength} أحرف على الأقل`);
            valid = false;
        } else {
            clearFieldError(field);
        }
    });

    return valid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    const err = document.createElement('p');
    err.className = 'field-error text-red-500 text-xs mt-1';
    err.textContent = message;
    field.after(err);
    field.style.borderColor = '#ef4444';
}

function clearFieldError(field) {
    field.nextElementSibling?.classList.contains('field-error') &&
        field.nextElementSibling.remove();
    field.style.borderColor = '';
}

/* ══════════════════════════════════════════════
   Auto-dismiss alerts
══════════════════════════════════════════════ */
function initNotifications() {
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
        const delay = parseInt(alert.dataset.autoDismiss, 10) || 5000;
        setTimeout(() => fadeOut(alert), delay);
    });
}

function fadeOut(el) {
    if (!el) return;
    el.style.transition = 'opacity .35s ease';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 360);
}

/* ══════════════════════════════════════════════
   Confirmation dialogs (data-confirm attribute)
══════════════════════════════════════════════ */
function initConfirmations() {
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', e => {
            if (!confirm(btn.dataset.confirm)) e.preventDefault();
        });
    });
}

/* ══════════════════════════════════════════════
   Password visibility toggles
══════════════════════════════════════════════ */
function initPasswordToggles() {
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        const targetId = btn.dataset.togglePassword;
        const input    = document.getElementById(targetId);
        if (!input) return;

        btn.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            // Swap lucide icon if present
            const icon = btn.querySelector('[data-lucide]');
            if (icon) {
                icon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    });
}

/* ══════════════════════════════════════════════
   API helper — fetch wrapper
══════════════════════════════════════════════ */
async function apiRequest(url, options = {}) {
    const defaults = {
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
        credentials: 'same-origin', // تأكد من إرسال الـ session cookie
    };

    try {
        const res  = await fetch(url, { ...defaults, ...options,
            headers: { ...defaults.headers, ...(options.headers || {}) }
        });
        const json = await res.json();

        if (!res.ok) {
            throw new Error(json.message || json.error || 'حدث خطأ في الطلب');
        }

        return json;
    } catch (err) {
        console.error('[API]', url, err);
        showToast(err.message, 'error');
        throw err;
    }
}

/* ══════════════════════════════════════════════
   Toast notifications (آمنة — بدون innerHTML)
══════════════════════════════════════════════ */
function showToast(message, type = 'info', duration = 5000) {
    const colors = {
        success : { bg: '#16a34a', icon: '✓' },
        error   : { bg: '#dc2626', icon: '✕' },
        warning : { bg: '#d97706', icon: '⚠' },
        info    : { bg: '#2563EB', icon: 'ℹ' },
    };
    const cfg = colors[type] || colors.info;

    // Container
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:8px;align-items:center;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `
        background:${cfg.bg};color:#fff;padding:12px 20px;border-radius:999px;
        font-size:14px;font-weight:600;font-family:Cairo,sans-serif;
        box-shadow:0 4px 24px rgba(0,0,0,.18);
        display:flex;align-items:center;gap:8px;
        pointer-events:auto;opacity:0;
        transition:opacity .25s ease,transform .25s ease;
        transform:translateY(-8px);
    `;

    // Icon span (text node — no innerHTML)
    const icon = document.createElement('span');
    icon.textContent = cfg.icon;
    toast.appendChild(icon);

    const msg = document.createElement('span');
    msg.textContent = message;
    toast.appendChild(msg);

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });
    });

    // Auto dismiss
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-8px)';
        setTimeout(() => toast.remove(), 280);
    }, duration);
}

// Keep old name as alias for backward compat
function showNotification(message, type) { showToast(message, type); }

/* ══════════════════════════════════════════════
   Date formatter
══════════════════════════════════════════════ */
function formatDate(date) {
    return new Date(date).toLocaleDateString('ar-EG', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

/* ══════════════════════════════════════════════
   Debounce
══════════════════════════════════════════════ */
function debounce(fn, wait = 300) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
}

/* ══════════════════════════════════════════════
   Search init
══════════════════════════════════════════════ */
function initSearch(inputId, resultsId, searchFn) {
    const input   = document.getElementById(inputId);
    const results = document.getElementById(resultsId);
    if (!input || !results) return;

    const debouncedFn = debounce(searchFn, 300);

    input.addEventListener('input', () => {
        const q = input.value.trim();
        if (q.length >= 2) debouncedFn(q);
        else results.innerHTML = '';
    });
}

/* ══════════════════════════════════════════════
   File upload preview (XSS-safe — no innerHTML for name)
══════════════════════════════════════════════ */
function initFileUpload(inputId, previewId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;

        // Only show image previews
        if (!file.type.startsWith('image/')) {
            preview.textContent = '';
            const p = document.createElement('p');
            p.className = 'text-sm text-slate-500';
            p.textContent = `تم اختيار: ${file.name}`;
            preview.appendChild(p);
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            preview.textContent = '';
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'معاينة';
            img.style.cssText = 'max-width:100%;max-height:300px;border-radius:12px;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

/* ══════════════════════════════════════════════
   togglePassword — legacy API (data-attr approach preferred)
══════════════════════════════════════════════ */
function togglePassword(inputId, buttonId) {
    const input  = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    if (!input || !button) return;

    button.addEventListener('click', () => {
        input.type = input.type === 'password' ? 'text' : 'password';
        const icon = button.querySelector('[data-lucide]');
        if (icon) {
            icon.setAttribute('data-lucide', input.type === 'password' ? 'eye' : 'eye-off');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    });
}

/* ══════════════════════════════════════════════
   Public API
══════════════════════════════════════════════ */
window.EduPlatform = {
    apiRequest,
    showToast,
    showNotification,
    formatDate,
    debounce,
    initSearch,
    initFileUpload,
    togglePassword,
};
