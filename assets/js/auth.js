/**
 * JourneyHub - Authentication JavaScript
 * Frontend validation for auth forms
 */

const AuthValidator = {
    /**
     * Initialize signup form validation
     */
    initSignup: function() {
        const form = document.getElementById('signup-form');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            if (!AuthValidator.validateSignup()) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (nameInput) {
            nameInput.addEventListener('blur', function() {
                AuthValidator.validateName(this.value);
            });
        }
        
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                AuthValidator.validateEmail(this.value);
            });
        }
        
        if (passwordInput) {
            passwordInput.addEventListener('blur', function() {
                AuthValidator.validatePassword(this.value);
            });
            
            passwordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value) {
                    AuthValidator.validatePasswordMatch(this.value, confirmPasswordInput.value);
                }
            });
        }
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('blur', function() {
                AuthValidator.validatePasswordMatch(passwordInput.value, this.value);
            });
        }
    },
    
    /**
     * Initialize login form validation
     */
    initLogin: function() {
        const form = document.getElementById('login-form');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            if (!AuthValidator.validateLogin()) {
                e.preventDefault();
            }
        });
    },
    
    /**
     * Initialize reset password form validation
     */
    initResetPassword: function() {
        const form = document.getElementById('reset-password-form');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            if (!AuthValidator.validateResetPassword()) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value) {
                    AuthValidator.validatePasswordMatch(this.value, confirmPasswordInput.value);
                }
            });
        }
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('blur', function() {
                AuthValidator.validatePasswordMatch(passwordInput.value, this.value);
            });
        }
    },
    
    /**
     * Validate signup form
     */
    validateSignup: function() {
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        let isValid = true;
        
        if (!this.validateName(name)) isValid = false;
        if (!this.validateEmail(email)) isValid = false;
        if (!this.validatePassword(password)) isValid = false;
        if (!this.validatePasswordMatch(password, confirmPassword)) isValid = false;
        
        return isValid;
    },
    
    /**
     * Validate login form
     */
    validateLogin: function() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        let isValid = true;
        
        if (!email.trim()) {
            this.showError('email', 'Email is required');
            isValid = false;
        } else {
            this.clearError('email');
        }
        
        if (!password) {
            this.showError('password', 'Password is required');
            isValid = false;
        } else {
            this.clearError('password');
        }
        
        return isValid;
    },
    
    /**
     * Validate reset password form
     */
    validateResetPassword: function() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        let isValid = true;
        
        if (!this.validatePassword(password)) isValid = false;
        if (!this.validatePasswordMatch(password, confirmPassword)) isValid = false;
        
        return isValid;
    },
    
    /**
     * Validate name field
     */
    validateName: function(name) {
        const errorId = 'name';
        
        if (!name.trim()) {
            this.showError(errorId, 'Name is required');
            return false;
        }
        
        if (name.trim().length < 2) {
            this.showError(errorId, 'Name must be at least 2 characters');
            return false;
        }
        
        this.clearError(errorId);
        return true;
    },
    
    /**
     * Validate email field
     */
    validateEmail: function(email) {
        const errorId = 'email';
        
        if (!email.trim()) {
            this.showError(errorId, 'Email is required');
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            this.showError(errorId, 'Please enter a valid email address');
            return false;
        }
        
        this.clearError(errorId);
        return true;
    },
    
    /**
     * Validate password field
     */
    validatePassword: function(password) {
        const errorId = 'password';
        
        if (!password) {
            this.showError(errorId, 'Password is required');
            return false;
        }
        
        if (password.length < 8) {
            this.showError(errorId, 'Password must be at least 8 characters');
            return false;
        }
        
        this.clearError(errorId);
        return true;
    },
    
    /**
     * Validate password match
     */
    validatePasswordMatch: function(password, confirmPassword) {
        const errorId = 'confirm-password';
        
        if (!confirmPassword) {
            this.showError(errorId, 'Please confirm your password');
            return false;
        }
        
        if (password !== confirmPassword) {
            this.showError(errorId, 'Passwords do not match');
            return false;
        }
        
        this.clearError(errorId);
        return true;
    },
    
    /**
     * Show error message
     */
    showError: function(fieldId, message) {
        const errorElement = document.getElementById(fieldId + '-error');
        const inputElement = document.getElementById(fieldId);
        
        if (errorElement) {
            errorElement.textContent = message;
        }
        
        if (inputElement) {
            inputElement.classList.add('error');
        }
    },
    
    /**
     * Clear error message
     */
    clearError: function(fieldId) {
        const errorElement = document.getElementById(fieldId + '-error');
        const inputElement = document.getElementById(fieldId);
        
        if (errorElement) {
            errorElement.textContent = '';
        }
        
        if (inputElement) {
            inputElement.classList.remove('error');
        }
    }
};

// Auto-initialize based on page
document.addEventListener('DOMContentLoaded', function() {
    // Check which form exists and initialize appropriately
    if (document.getElementById('signup-form')) {
        AuthValidator.initSignup();
    }
    
    if (document.getElementById('login-form')) {
        AuthValidator.initLogin();
    }
    
    if (document.getElementById('reset-password-form')) {
        AuthValidator.initResetPassword();
    }
});
