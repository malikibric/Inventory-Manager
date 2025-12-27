window.initRegisterPage = function () {
    // Setup real-time validation (EXCLUDE password fields - they have custom validation)
    const validationRules = {
        firstName: {
            label: 'First Name',
            rules: [
                ValidationService.rules.required,
                (value) => ValidationService.rules.minLength(value, 2, 'First Name'),
                (value) => ValidationService.rules.maxLength(value, 50, 'First Name'),
                (value) => ValidationService.rules.alphanumeric(value, 'First Name')
            ]
        },
        lastName: {
            label: 'Last Name',
            rules: [
                ValidationService.rules.required,
                (value) => ValidationService.rules.minLength(value, 2, 'Last Name'),
                (value) => ValidationService.rules.maxLength(value, 50, 'Last Name'),
                (value) => ValidationService.rules.alphanumeric(value, 'Last Name')
            ]
        },
        email: {
            label: 'Email',
            rules: [
                ValidationService.rules.required,
                ValidationService.rules.email
            ]
        }
        // PASSWORD FIELDS EXCLUDED - they have custom strength indicator and match validation
    };

    ValidationService.setupRealTimeValidation('registerForm', validationRules);

    // Add password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        
        // Remove any old password strength messages that might exist
        $('.password-strength').remove();
        
        const $strengthContainer = $('#passwordStrength');
        const $strengthText = $strengthContainer.find('.password-strength-text');
        
        // Hide if password is empty
        if (password.length === 0) {
            $strengthContainer.hide();
            $strengthText.text('').removeClass('text-danger text-warning text-success');
            return;
        }
        
        let strength = 0;
        let strengthText = '';
        let strengthClass = '';

        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[@$!%*?&#]/)) strength++;

        // Remove all previous classes
        $strengthText.removeClass('text-danger text-warning text-success');
        
        switch(strength) {
            case 0:
            case 1:
                strengthText = 'Weak';
                strengthClass = 'text-danger';
                break;
            case 2:
            case 3:
                strengthText = 'Medium';
                strengthClass = 'text-warning';
                break;
            case 4:
            case 5:
                strengthText = 'Strong';
                strengthClass = 'text-success';
                break;
        }

        // Update text and class, then show
        $strengthText.addClass(strengthClass).text(`Password Strength: ${strengthText}`);
        $strengthContainer.show();
    });

    // Handle form submission
    $('#registerForm').off('submit').on('submit', async function (e) {
        e.preventDefault();

        // Clear previous validation
        ValidationService.clearValidation('registerForm');
        $('.alert').remove();

        const firstName = $('#firstName').val().trim();
        const lastName = $('#lastName').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val(); // Don't trim passwords!
        const confirmPassword = $('#confirmPassword').val(); // Don't trim passwords!

        // Prepare form data (only validate non-password fields with ValidationService)
        const formData = {
            firstName,
            lastName,
            email,
            password,
            confirmPassword
        };

        // Client-side validation for non-password fields
        const validation = ValidationService.validateForm(formData, validationRules);
        if (!validation.errors) validation.errors = {};

        // Manual password validation (not real-time, only on submit)
        if (!password || password.trim() === '') {
            validation.isValid = false;
            validation.errors.password = 'Password is required';
        } else {
            const passwordError = ValidationService.rules.password(password);
            if (passwordError) {
                validation.isValid = false;
                validation.errors.password = passwordError;
            }
        }

        // Confirm password validation
        if (!confirmPassword || confirmPassword.trim() === '') {
            validation.isValid = false;
            validation.errors.confirmPassword = 'Confirm Password is required';
        } else if (password !== confirmPassword) {
            validation.isValid = false;
            validation.errors.confirmPassword = 'Passwords do not match';
        }

        if (!validation.isValid) {
            ValidationService.displayErrors(validation.errors);
            return;
        }

        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();

        // Disable button and show loading
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating Account...');

        try {
            // Use AuthService for registration
            const result = await AuthService.register(formData);

            if (result.success) {
                // Show success message
                const successAlert = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Registration successful!</strong> You can now login with your credentials.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#registerForm').before(successAlert);

                // Reset form
                $('#registerForm')[0].reset();
                ValidationService.clearValidation('registerForm');

                // Redirect to login after 2 seconds
                setTimeout(() => {
                    window.location.hash = '#login';
                }, 2000);
            } else {
                // Display errors
                if (result.errors) {
                    ValidationService.displayErrors(result.errors);
                } else {
                    const errorAlert = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>${SecurityService.escapeHTML(result.error || 'Registration failed')}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('#registerForm').before(errorAlert);
                }
            }
        } catch (error) {
            console.error('Registration error:', error);
            const errorAlert = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>An unexpected error occurred. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#registerForm').before(errorAlert);
        } finally {
            // Re-enable button
            btn.prop('disabled', false).html(originalText);
        }
    });

    // Real-time password match validation
    $('#confirmPassword').on('input', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        const $feedback = $('#confirmPassword').siblings('.invalid-feedback');
        
        if (confirmPassword.length > 0) {
            if (password !== confirmPassword) {
                $('#confirmPassword').addClass('is-invalid').removeClass('is-valid');
                if ($feedback.length === 0) {
                    $('#confirmPassword').after('<div class="invalid-feedback">Passwords do not match</div>');
                }
            } else {
                $('#confirmPassword').removeClass('is-invalid').addClass('is-valid');
                $feedback.remove();
            }
        } else {
            $('#confirmPassword').removeClass('is-invalid is-valid');
            $feedback.remove();
        }
    });

    // Clear password field validation errors when typing (remove red border and error text)
    $('#password').on('input', function() {
        $(this).removeClass('is-invalid is-valid');
        $(this).siblings('.invalid-feedback').remove();
        $('.alert').fadeOut(300, function() { $(this).remove(); });
    });

    // Clear alerts when user starts typing
    $('#firstName, #lastName, #email, #password, #confirmPassword').on('input', function() {
        $('.alert').fadeOut(300, function() { $(this).remove(); });
    });
};
