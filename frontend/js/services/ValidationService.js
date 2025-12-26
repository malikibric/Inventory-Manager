/**
 * ValidationService - Comprehensive client-side validation utility
 * Provides reusable validation rules and form validation logic
 */
const ValidationService = {
    /**
     * Validation rules
     */
    rules: {
        required: (value, fieldName = 'This field') => {
            if (!value || value.toString().trim() === '') {
                return `${fieldName} is required`;
            }
            return null;
        },

        email: (value) => {
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(value)) {
                return 'Please enter a valid email address';
            }
            return null;
        },

        minLength: (value, min, fieldName = 'This field') => {
            if (value.length < min) {
                return `${fieldName} must be at least ${min} characters`;
            }
            return null;
        },

        maxLength: (value, max, fieldName = 'This field') => {
            if (value.length > max) {
                return `${fieldName} must not exceed ${max} characters`;
            }
            return null;
        },

        password: (value) => {
            // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/;
            if (!passwordRegex.test(value)) {
                return 'Password must be at least 8 characters and contain uppercase, lowercase, number, and special character';
            }
            return null;
        },

        passwordMatch: (password, confirmPassword) => {
            if (password !== confirmPassword) {
                return 'Passwords do not match';
            }
            return null;
        },

        numeric: (value, fieldName = 'This field') => {
            if (isNaN(value) || value === '') {
                return `${fieldName} must be a number`;
            }
            return null;
        },

        positiveNumber: (value, fieldName = 'This field') => {
            const num = parseFloat(value);
            if (isNaN(num) || num < 0) {
                return `${fieldName} must be a positive number`;
            }
            return null;
        },

        integer: (value, fieldName = 'This field') => {
            if (!Number.isInteger(Number(value))) {
                return `${fieldName} must be a whole number`;
            }
            return null;
        },

        min: (value, min, fieldName = 'This field') => {
            if (parseFloat(value) < min) {
                return `${fieldName} must be at least ${min}`;
            }
            return null;
        },

        max: (value, max, fieldName = 'This field') => {
            if (parseFloat(value) > max) {
                return `${fieldName} must not exceed ${max}`;
            }
            return null;
        },

        alphanumeric: (value, fieldName = 'This field') => {
            const alphanumericRegex = /^[a-zA-Z0-9\s]+$/;
            if (!alphanumericRegex.test(value)) {
                return `${fieldName} can only contain letters and numbers`;
            }
            return null;
        },

        url: (value) => {
            try {
                new URL(value);
                return null;
            } catch (e) {
                return 'Please enter a valid URL';
            }
        },

        phone: (value) => {
            const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
            if (!phoneRegex.test(value)) {
                return 'Please enter a valid phone number';
            }
            return null;
        }
    },

    /**
     * Validate a single field with multiple rules
     * @param {*} value - The value to validate
     * @param {Array} rules - Array of validation rules to apply
     * @param {string} fieldName - Display name of the field
     * @returns {string|null} Error message or null if valid
     */
    validateField: (value, rules, fieldName = 'This field') => {
        for (let rule of rules) {
            let error = null;
            
            if (typeof rule === 'function') {
                error = rule(value, fieldName);
            } else if (typeof rule === 'object') {
                const { type, ...params } = rule;
                if (ValidationService.rules[type]) {
                    error = ValidationService.rules[type](value, ...Object.values(params), fieldName);
                }
            }
            
            if (error) {
                return error;
            }
        }
        return null;
    },

    /**
     * Validate an entire form
     * @param {Object} formData - Object containing field values
     * @param {Object} validationRules - Object mapping field names to validation rules
     * @returns {Object} Object containing isValid flag and errors object
     */
    validateForm: (formData, validationRules) => {
        const errors = {};
        let isValid = true;

        for (let field in validationRules) {
            const value = formData[field];
            const rules = validationRules[field];
            const fieldLabel = rules.label || field;
            
            const error = ValidationService.validateField(value, rules.rules || [], fieldLabel);
            if (error) {
                errors[field] = error;
                isValid = false;
            }
        }

        return { isValid, errors };
    },

    /**
     * Display error message for a field
     * @param {string} fieldId - The ID of the input field
     * @param {string} errorMessage - The error message to display
     */
    showError: (fieldId, errorMessage) => {
        const field = $(`#${fieldId}`);
        if (!field.length) return;

        // Remove existing error
        field.removeClass('is-invalid is-valid');
        field.next('.invalid-feedback').remove();

        if (errorMessage) {
            field.addClass('is-invalid');
            field.after(`<div class="invalid-feedback d-block">${errorMessage}</div>`);
        }
    },

    /**
     * Show success state for a field
     * @param {string} fieldId - The ID of the input field
     */
    showSuccess: (fieldId) => {
        const field = $(`#${fieldId}`);
        if (!field.length) return;

        field.removeClass('is-invalid');
        field.addClass('is-valid');
        field.next('.invalid-feedback').remove();
    },

    /**
     * Clear all validation messages from a form
     * @param {string} formId - The ID of the form
     */
    clearValidation: (formId) => {
        $(`#${formId} .is-invalid, #${formId} .is-valid`).removeClass('is-invalid is-valid');
        $(`#${formId} .invalid-feedback`).remove();
    },

    /**
     * Display all form errors
     * @param {Object} errors - Object mapping field IDs to error messages
     */
    displayErrors: (errors) => {
        for (let field in errors) {
            ValidationService.showError(field, errors[field]);
        }
    },

    /**
     * Setup real-time validation for a form
     * @param {string} formId - The ID of the form
     * @param {Object} validationRules - Validation rules for each field
     */
    setupRealTimeValidation: (formId, validationRules) => {
        for (let field in validationRules) {
            $(`#${field}`).on('blur change', function() {
                const value = $(this).val();
                const rules = validationRules[field];
                const fieldLabel = rules.label || field;
                
                const error = ValidationService.validateField(value, rules.rules || [], fieldLabel);
                if (error) {
                    ValidationService.showError(field, error);
                } else {
                    ValidationService.showSuccess(field);
                }
            });
        }
    }
};
