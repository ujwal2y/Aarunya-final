/**
 * Aarunya Healthcare - Centralized Validation Library
 * Provides comprehensive form validation with real-time feedback
 */

const AarunyaValidator = {
    // Validation Patterns
    patterns: {
        email: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,  // Standard email format
        phone: /^[0-9]{10}$/,
        name: /^[a-zA-Z\s]+$/,
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/
    },

    // Error Messages
    messages: {
        email: {
            required: 'Email address is required',
            invalid: 'Please enter a valid email address'
        },
        phone: {
            required: 'Phone number is required',
            invalid: 'Phone number must be exactly 10 digits',
            numeric: 'Only numeric values are allowed'
        },
        password: {
            required: 'Password is required',
            minLength: 'Password must be at least 8 characters long',
            weak: 'Password must contain uppercase, lowercase, number, and special character'
        },
        name: {
            required: 'Name is required',
            invalid: 'Please enter a valid name (letters and spaces only)'
        }
    },

    /**
     * Validate Email
     */
    validateEmail(email) {
        if (!email || email.trim() === '') {
            return { valid: false, message: this.messages.email.required };
        }
        if (!this.patterns.email.test(email.trim())) {
            return { valid: false, message: this.messages.email.invalid };
        }
        return { valid: true, message: '' };
    },

    /**
     * Validate Phone Number
     */
    validatePhone(phone) {
        if (!phone || phone.trim() === '') {
            return { valid: false, message: this.messages.phone.required };
        }
        
        // Remove any spaces or special characters
        const cleanPhone = phone.replace(/\s+/g, '');
        
        // Check if contains only numbers
        if (!/^\d+$/.test(cleanPhone)) {
            return { valid: false, message: this.messages.phone.numeric };
        }
        
        // Check if exactly 10 digits
        if (!this.patterns.phone.test(cleanPhone)) {
            return { valid: false, message: this.messages.phone.invalid };
        }
        
        return { valid: true, message: '' };
    },

    /**
     * Validate Password
     */
    validatePassword(password) {
        if (!password || password.trim() === '') {
            return { valid: false, message: this.messages.password.required, strength: 0 };
        }
        
        if (password.length < 8) {
            return { valid: false, message: this.messages.password.minLength, strength: 1 };
        }
        
        if (!this.patterns.password.test(password)) {
            return { valid: false, message: this.messages.password.weak, strength: 2 };
        }
        
        // Calculate password strength
        let strength = 3; // Base strength for meeting minimum requirements
        if (password.length >= 12) strength++;
        if (/[A-Z].*[A-Z]/.test(password)) strength++; // Multiple uppercase
        if (/\d.*\d/.test(password)) strength++; // Multiple numbers
        if (/[@$!%*?&#].*[@$!%*?&#]/.test(password)) strength++; // Multiple special chars
        
        return { valid: true, message: '', strength: Math.min(strength, 5) };
    },

    /**
     * Validate Name
     */
    validateName(name) {
        if (!name || name.trim() === '') {
            return { valid: false, message: this.messages.name.required };
        }
        
        if (!this.patterns.name.test(name.trim())) {
            return { valid: false, message: this.messages.name.invalid };
        }
        
        return { valid: true, message: '' };
    },

    /**
     * Capitalize Name Properly
     */
    capitalizeName(name) {
        return name
            .trim()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    },

    /**
     * Show Field Error
     */
    showError(inputElement, message) {
        const wrapper = inputElement.closest('.input-wrapper') || inputElement.parentElement;
        const formGroup = inputElement.closest('.form-group');
        
        // Remove existing error
        this.clearError(inputElement);
        
        // Add error class to input
        inputElement.classList.add('input-error');
        inputElement.classList.remove('input-success');
        
        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        // Insert error message after input wrapper
        if (wrapper.nextSibling) {
            wrapper.parentNode.insertBefore(errorDiv, wrapper.nextSibling);
        } else {
            wrapper.parentNode.appendChild(errorDiv);
        }
        
        // Update icon color if exists
        const icon = wrapper.querySelector('.input-icon');
        if (icon) {
            icon.style.color = '#EF4444';
        }
    },

    /**
     * Show Field Success
     */
    showSuccess(inputElement) {
        const wrapper = inputElement.closest('.input-wrapper') || inputElement.parentElement;
        
        // Remove error
        this.clearError(inputElement);
        
        // Add success class
        inputElement.classList.add('input-success');
        inputElement.classList.remove('input-error');
        
        // Update icon color if exists
        const icon = wrapper.querySelector('.input-icon');
        if (icon) {
            icon.style.color = '#10B981';
        }
    },

    /**
     * Clear Field Error/Success
     */
    clearError(inputElement) {
        const wrapper = inputElement.closest('.input-wrapper') || inputElement.parentElement;
        const formGroup = inputElement.closest('.form-group');
        
        // Remove error/success classes
        inputElement.classList.remove('input-error', 'input-success');
        
        // Remove error message
        const errorMsg = formGroup.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
        
        // Reset icon color
        const icon = wrapper.querySelector('.input-icon');
        if (icon) {
            icon.style.color = '#78909c';
        }
    },

    /**
     * Update Password Strength Indicator
     */
    updatePasswordStrength(inputElement, strength) {
        let strengthIndicator = inputElement.closest('.form-group').querySelector('.password-strength');
        
        if (!strengthIndicator) {
            strengthIndicator = document.createElement('div');
            strengthIndicator.className = 'password-strength';
            strengthIndicator.innerHTML = `
                <div class="strength-bars">
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                </div>
                <span class="strength-text">Password Strength</span>
            `;
            
            const wrapper = inputElement.closest('.input-wrapper') || inputElement.parentElement;
            if (wrapper.nextSibling) {
                wrapper.parentNode.insertBefore(strengthIndicator, wrapper.nextSibling);
            } else {
                wrapper.parentNode.appendChild(strengthIndicator);
            }
        }
        
        const bars = strengthIndicator.querySelectorAll('.strength-bar');
        const strengthText = strengthIndicator.querySelector('.strength-text');
        
        // Reset all bars
        bars.forEach(bar => {
            bar.className = 'strength-bar';
        });
        
        // Update based on strength
        const strengthLabels = ['', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
        const strengthClasses = ['', 'weak', 'fair', 'good', 'strong', 'very-strong'];
        
        for (let i = 0; i < strength; i++) {
            bars[i].classList.add(strengthClasses[strength]);
        }
        
        strengthText.textContent = strengthLabels[strength] || 'Password Strength';
        strengthIndicator.setAttribute('data-strength', strength);
    },

    /**
     * Setup Real-time Validation for Form
     */
    setupFormValidation(formElement) {
        const inputs = formElement.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="password"]');
        
        inputs.forEach(input => {
            // Real-time validation on input
            input.addEventListener('input', (e) => {
                this.validateField(e.target);
            });
            
            // Validation on blur
            input.addEventListener('blur', (e) => {
                this.validateField(e.target);
            });
            
            // Auto-capitalize name fields
            if (input.name === 'name' || input.name === 'full_name' || input.id === 'name') {
                input.addEventListener('blur', (e) => {
                    if (e.target.value) {
                        e.target.value = this.capitalizeName(e.target.value);
                    }
                });
            }
            
            // Clean phone number input
            if (input.type === 'tel' || input.name === 'phone' || input.name === 'phone_number') {
                input.addEventListener('input', (e) => {
                    // Remove non-numeric characters
                    e.target.value = e.target.value.replace(/\D/g, '');
                    // Limit to 10 digits
                    if (e.target.value.length > 10) {
                        e.target.value = e.target.value.slice(0, 10);
                    }
                });
            }
        });
        
        // Form submission validation
        formElement.addEventListener('submit', (e) => {
            if (!this.validateForm(formElement)) {
                e.preventDefault();
                
                // Focus on first error field
                const firstError = formElement.querySelector('.input-error');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    },

    /**
     * Validate Single Field
     */
    validateField(inputElement) {
        const value = inputElement.value;
        const type = inputElement.type;
        const name = inputElement.name;
        let result;
        
        // Determine validation type
        if (type === 'email' || name === 'email') {
            result = this.validateEmail(value);
        } else if (type === 'tel' || name === 'phone' || name === 'phone_number') {
            result = this.validatePhone(value);
        } else if (type === 'password') {
            result = this.validatePassword(value);
            if (result.strength !== undefined) {
                this.updatePasswordStrength(inputElement, result.strength);
            }
        } else if (name === 'name' || name === 'full_name') {
            result = this.validateName(value);
        } else {
            // Generic required validation
            if (inputElement.hasAttribute('required') && !value.trim()) {
                result = { valid: false, message: 'This field is required' };
            } else {
                result = { valid: true, message: '' };
            }
        }
        
        // Show validation result
        if (!result.valid) {
            this.showError(inputElement, result.message);
        } else if (value.trim() !== '') {
            this.showSuccess(inputElement);
        } else {
            this.clearError(inputElement);
        }
        
        return result.valid;
    },

    /**
     * Validate Entire Form
     */
    validateForm(formElement) {
        const inputs = formElement.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="password"]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (input.hasAttribute('required') || input.value.trim() !== '') {
                if (!this.validateField(input)) {
                    isValid = false;
                }
            }
        });
        
        return isValid;
    },

    /**
     * Setup Password Toggle
     */
    setupPasswordToggle(passwordInput) {
        const wrapper = passwordInput.closest('.input-wrapper');
        if (!wrapper) return;
        
        let toggleBtn = wrapper.querySelector('.password-toggle');
        
        // If toggle button already exists, just add the event listener
        if (toggleBtn) {
            // Remove any existing listeners by cloning
            const newToggleBtn = toggleBtn.cloneNode(true);
            toggleBtn.parentNode.replaceChild(newToggleBtn, toggleBtn);
            toggleBtn = newToggleBtn;
            
            toggleBtn.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                toggleBtn.classList.toggle('fa-eye');
                toggleBtn.classList.toggle('fa-eye-slash');
            });
        } else {
            // Create toggle button if it doesn't exist
            toggleBtn = document.createElement('i');
            toggleBtn.className = 'fas fa-eye password-toggle';
            wrapper.appendChild(toggleBtn);
            
            toggleBtn.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                toggleBtn.classList.toggle('fa-eye');
                toggleBtn.classList.toggle('fa-eye-slash');
            });
        }
    },

    /**
     * Initialize Validation for All Forms
     */
    init() {
        // Add validation styles
        this.injectStyles();
        
        // Setup all forms
        document.querySelectorAll('form').forEach(form => {
            this.setupFormValidation(form);
        });
        
        // Setup password toggles
        document.querySelectorAll('input[type="password"]').forEach(input => {
            this.setupPasswordToggle(input);
        });
    },

    /**
     * Inject Validation Styles
     */
    injectStyles() {
        if (document.getElementById('aarunya-validation-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'aarunya-validation-styles';
        style.textContent = `
            /* Validation Styles */
            .input-error {
                border-color: #EF4444 !important;
                background: rgba(239, 68, 68, 0.05) !important;
            }
            
            .input-error:focus {
                border-color: #EF4444 !important;
                box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15) !important;
            }
            
            .input-success {
                border-color: #10B981 !important;
                background: rgba(16, 185, 129, 0.05) !important;
            }
            
            .input-success:focus {
                border-color: #10B981 !important;
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15) !important;
            }
            
            .error-message {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-top: 6px;
                font-size: 13px;
                color: #EF4444;
                animation: slideDown 0.2s ease;
            }
            
            .error-message i {
                font-size: 12px;
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-5px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            /* Password Strength Indicator */
            .password-strength {
                margin-top: 8px;
            }
            
            .strength-bars {
                display: flex;
                gap: 4px;
                margin-bottom: 4px;
            }
            
            .strength-bar {
                flex: 1;
                height: 4px;
                background: rgba(100, 116, 139, 0.3);
                border-radius: 2px;
                transition: all 0.3s ease;
            }
            
            .strength-bar.weak {
                background: #EF4444;
            }
            
            .strength-bar.fair {
                background: #F59E0B;
            }
            
            .strength-bar.good {
                background: #3B82F6;
            }
            
            .strength-bar.strong {
                background: #10B981;
            }
            
            .strength-bar.very-strong {
                background: #059669;
            }
            
            .strength-text {
                font-size: 12px;
                color: #78909c;
                font-weight: 500;
            }
            
            .password-strength[data-strength="1"] .strength-text {
                color: #EF4444;
            }
            
            .password-strength[data-strength="2"] .strength-text {
                color: #F59E0B;
            }
            
            .password-strength[data-strength="3"] .strength-text {
                color: #3B82F6;
            }
            
            .password-strength[data-strength="4"] .strength-text {
                color: #10B981;
            }
            
            .password-strength[data-strength="5"] .strength-text {
                color: #059669;
            }
            
            /* Password Toggle */
            .password-toggle {
                cursor: pointer;
                user-select: none;
            }
        `;
        
        document.head.appendChild(style);
    }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AarunyaValidator.init());
} else {
    AarunyaValidator.init();
}
