// Main JavaScript for Educational Platform

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initNavbar();
    initForms();
    initNotifications();
    initConfirmations();
    initEmoji();
});

// Initialize Twemoji for realistic emoji rendering
function initEmoji() {
    if (typeof twemoji !== 'undefined') {
        twemoji.parse(document.body);
        
        // Watch for dynamically added content
        const observer = new MutationObserver((mutations) => {
            let shouldParse = false;
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length > 0) {
                    shouldParse = true;
                }
            });
            if (shouldParse) {
                twemoji.parse(document.body);
            }
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
    }
}

// Navbar functionality
function initNavbar() {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        // Add scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            }
        });
    }
}

// Form validation and submission
function initForms() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'هذا الحقل مطلوب');
            isValid = false;
        } else {
            clearFieldError(field);
        }
        
        // Email validation
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                showFieldError(field, 'البريد الإلكتروني غير صحيح');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('field-error')) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    } else {
        const error = document.createElement('div');
        error.className = 'field-error';
        error.style.color = '#e74c3c';
        error.style.fontSize = '12px';
        error.style.marginTop = '5px';
        error.textContent = message;
        field.parentNode.insertBefore(error, field.nextSibling);
    }
    field.style.borderColor = '#e74c3c';
}

function clearFieldError(field) {
    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('field-error')) {
        errorDiv.style.display = 'none';
    }
    field.style.borderColor = '#ddd';
}

// Notification system
function initNotifications() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
}

// Confirmation dialogs
function initConfirmations() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

// API helper functions
async function apiRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
        },
    };
    
    const mergedOptions = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(url, mergedOptions);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Something went wrong');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        showNotification(error.message, 'error');
        throw error;
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Format date
function formatDate(date) {
    const d = new Date(date);
    return d.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search functionality
function initSearch(searchInputId, resultsContainerId, searchFunction) {
    const searchInput = document.getElementById(searchInputId);
    const resultsContainer = document.getElementById(resultsContainerId);
    
    if (searchInput && resultsContainer) {
        const debouncedSearch = debounce(searchFunction, 300);
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                debouncedSearch(query);
            } else {
                resultsContainer.innerHTML = '';
            }
        });
    }
}

// File upload preview
function initFileUpload(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    if (input && preview) {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" 
                             style="max-width: 100%; max-height: 300px; border-radius: 10px;">
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

// Toggle password visibility
function togglePassword(inputId, buttonId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    
    if (input && button) {
        button.addEventListener('click', function() {
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            
            const icon = button.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
}

// Export functions for use in other scripts
window.EduPlatform = {
    apiRequest,
    showNotification,
    formatDate,
    initSearch,
    initFileUpload,
    togglePassword
};
