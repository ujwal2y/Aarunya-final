// ============================================================================
// DOCTOR REGISTRATION - JAVASCRIPT
// Multi-step form with validation, auto-save, and file uploads
// ============================================================================

// State Management
let currentStep = 1;
const totalSteps = 6;
const formData = new FormData();

// DOM Elements
const form = document.getElementById('doctorRegistrationForm');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const submitBtn = document.getElementById('submitBtn');
const saveDraftBtn = document.getElementById('saveDraftBtn');
const progressFill = document.getElementById('progressFill');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    setupEventListeners();
    loadDraft();
});

// Initialize Form
function initializeForm() {
    showStep(currentStep);
    updateProgress();
}

// Setup Event Listeners
function setupEventListeners() {
    // Navigation buttons
    prevBtn.addEventListener('click', () => navigateStep(-1));
    nextBtn.addEventListener('click', () => navigateStep(1));
    submitBtn.addEventListener('click', handleSubmit);
    saveDraftBtn.addEventListener('click', saveDraft);

    // Password toggle
    setupPasswordToggle('togglePassword', 'password');
    setupPasswordToggle('toggleConfirmPassword', 'confirmPassword');

    // Password strength
    document.getElementById('password').addEventListener('input', checkPasswordStrength);
    document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);

    // File uploads
    setupFileUpload('profilePhoto', 'profilePreview', true);
    setupFileUpload('licenseCert', 'licensePreview', false);
    setupFileUpload('degreeCert', 'degreePreview', false);
    setupFileUpload('govId', 'govIdPreview', false);
    setupFileUpload('expCert', 'expPreview', false);

    // Auto-save on input change
    form.addEventListener('change', debounce(saveDraft, 2000));
}

// Show Step
function showStep(step) {
    const steps = document.querySelectorAll('.form-step');
    const stepIndicators = document.querySelectorAll('.step');

    steps.forEach((s, index) => {
        s.classList.toggle('active', index + 1 === step);
    });

    stepIndicators.forEach((s, index) => {
        s.classList.toggle('active', index + 1 === step);
        s.classList.toggle('completed', index + 1 < step);
    });

    // Update buttons
    prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
    nextBtn.style.display = step === totalSteps ? 'none' : 'inline-flex';
    submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Navigate Step
function navigateStep(direction) {
    if (direction === 1 && !validateCurrentStep()) {
        return;
    }

    currentStep += direction;
    if (currentStep < 1) currentStep = 1;
    if (currentStep > totalSteps) currentStep = totalSteps;

    showStep(currentStep);
    updateProgress();
}

// Update Progress
function updateProgress() {
    const progress = (currentStep / totalSteps) * 100;
    progressFill.style.width = progress + '%';
}

// Validate Current Step
function validateCurrentStep() {
    const currentStepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#EF4444';
            setTimeout(() => {
                field.style.borderColor = '';
            }, 2000);
        }

        // Special validation for checkboxes (available days)
        if (field.type === 'checkbox' && field.name === 'available_days[]') {
            const checkedDays = document.querySelectorAll('input[name="available_days[]"]:checked');
            if (checkedDays.length === 0) {
                isValid = false;
                showNotification('Please select at least one available day', 'error');
            }
        }
    });

    if (!isValid) {
        showNotification('Please fill in all required fields', 'error');
    }

    return isValid;
}

// Password Toggle
function setupPasswordToggle(toggleId, inputId) {
    const toggle = document.getElementById(toggleId);
    const input = document.getElementById(inputId);

    if (toggle && input) {
        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
}

// Check Password Strength
function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.querySelector('.strength-bar');
    const strengthText = document.querySelector('.strength-text');

    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;

    strengthBar.className = 'strength-bar';
    if (strength === 1 || strength === 2) {
        strengthBar.classList.add('weak');
        strengthText.textContent = 'Weak password';
        strengthText.style.color = '#EF4444';
    } else if (strength === 3) {
        strengthBar.classList.add('medium');
        strengthText.textContent = 'Medium password';
        strengthText.style.color = '#F59E0B';
    } else if (strength === 4) {
        strengthBar.classList.add('strong');
        strengthText.textContent = 'Strong password';
        strengthText.style.color = '#10b981';
    } else {
        strengthText.textContent = '';
    }
}

// Check Password Match
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const matchIndicator = document.getElementById('passwordMatch');

    if (confirmPassword.length === 0) {
        matchIndicator.textContent = '';
        return;
    }

    if (password === confirmPassword) {
        matchIndicator.textContent = '✓ Passwords match';
        matchIndicator.className = 'password-match match';
    } else {
        matchIndicator.textContent = '✗ Passwords do not match';
        matchIndicator.className = 'password-match no-match';
    }
}

// Setup File Upload
function setupFileUpload(inputId, previewId, isImage) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!input || !preview) return;

    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            showNotification('File size must be less than 5MB', 'error');
            input.value = '';
            return;
        }

        // Show preview
        preview.classList.add('active');

        if (isImage) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        } else {
            const fileSize = (file.size / 1024).toFixed(2);
            preview.innerHTML = `
                <i class="fas fa-file-check"></i>
                <div class="file-info">
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${fileSize} KB</span>
                </div>
            `;
        }
    });

    // Drag and drop
    const dropZone = input.closest('.file-drop-zone') || input.closest('.file-upload-container');
    if (dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('drag-over');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('drag-over');
            }, false);
        });

        dropZone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            input.files = files;
            input.dispatchEvent(new Event('change'));
        }, false);
    }
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Save Draft
async function saveDraft() {
    const formDataObj = new FormData(form);
    const data = {};

    // Convert FormData to object
    for (let [key, value] of formDataObj.entries()) {
        if (key.includes('[]')) {
            const arrayKey = key.replace('[]', '');
            if (!data[arrayKey]) data[arrayKey] = [];
            data[arrayKey].push(value);
        } else {
            data[key] = value;
        }
    }

    try {
        const response = await fetch('actions/save_draft.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                step: currentStep,
                data: data
            })
        });

        if (response.ok) {
            showNotification('Draft saved successfully', 'success');
        }
    } catch (error) {
        console.error('Error saving draft:', error);
    }
}

// Load Draft
async function loadDraft() {
    try {
        const response = await fetch('actions/load_draft.php');
        if (response.ok) {
            const draft = await response.json();
            if (draft && draft.data) {
                // Populate form fields
                Object.keys(draft.data).forEach(key => {
                    const field = form.elements[key];
                    if (field) {
                        if (field.type === 'checkbox') {
                            field.checked = draft.data[key];
                        } else {
                            field.value = draft.data[key];
                        }
                    }
                });

                // Restore step
                if (draft.step) {
                    currentStep = parseInt(draft.step);
                    showStep(currentStep);
                    updateProgress();
                }

                showNotification('Draft loaded', 'info');
            }
        }
    } catch (error) {
        console.error('Error loading draft:', error);
    }
}

// Handle Submit
async function handleSubmit(e) {
    e.preventDefault();

    if (!validateCurrentStep()) {
        return;
    }

    // Validate password match
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    if (password !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return;
    }

    // Show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    const formDataObj = new FormData(form);

    try {
        const response = await fetch('actions/submit_registration.php', {
            method: 'POST',
            body: formDataObj
        });

        const result = await response.json();

        if (result.success) {
            // Show success modal
            document.getElementById('successModal').style.display = 'flex';
            
            // Clear draft
            await fetch('actions/clear_draft.php', { method: 'POST' });
        } else {
            showNotification(result.message || 'Registration failed. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Submit Registration';
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        showNotification('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Submit Registration';
    }
}

// Show Notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 24px;
        right: 24px;
        padding: 16px 24px;
        background: ${type === 'success' ? 'rgba(16, 185, 129, 0.9)' : type === 'error' ? 'rgba(239, 68, 68, 0.9)' : 'rgba(59, 130, 246, 0.9)'};
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 600;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    `;

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Debounce Function
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

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100px);
        }
    }

    .drag-over {
        border-color: #C4A7FF !important;
        background: rgba(244, 114, 182, 0.1) !important;
    }
`;
document.head.appendChild(style);
