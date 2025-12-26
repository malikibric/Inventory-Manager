window.initLoginPage = function () {
    // Setup real-time validation
    const validationRules = {
        email: {
            label: 'Email',
            rules: [
                ValidationService.rules.required,
                ValidationService.rules.email
            ]
        },
        password: {
            label: 'Password',
            rules: [
                ValidationService.rules.required,
                (value) => ValidationService.rules.minLength(value, 6, 'Password')
            ]
        }
    };

    ValidationService.setupRealTimeValidation('loginForm', validationRules);

    // Handle form submission
    $('#loginForm').off('submit').on('submit', async function (e) {
        e.preventDefault();

        // Clear previous validation
        ValidationService.clearValidation('loginForm');

        const email = $('#email').val().trim();
        const password = $('#password').val();
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();

        // Client-side validation
        const formData = { email, password };
        const validation = ValidationService.validateForm(formData, validationRules);

        if (!validation.isValid) {
            ValidationService.displayErrors(validation.errors);
            return;
        }

        // Disable button and show loading
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

        try {
            // Use AuthService for login
            const result = await AuthService.login(formData);

            if (result.success) {
                // Show success message
                const successAlert = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Login successful! Redirecting...
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#loginForm').before(successAlert);

                // Redirect to dashboard
                setTimeout(() => {
                    window.location.hash = '#dashboard';
                }, 1000);
            } else {
                // Display errors
                if (result.errors) {
                    ValidationService.displayErrors(result.errors);
                } else {
                    const errorAlert = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>${SecurityService.escapeHTML(result.error || 'Login failed')}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('#loginForm').before(errorAlert);
                }
            }
        } catch (error) {
            console.error('Login error:', error);
            const errorAlert = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>An unexpected error occurred. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#loginForm').before(errorAlert);
        } finally {
            // Re-enable button
            btn.prop('disabled', false).html(originalText);
        }
    });

    // Clear alerts when user starts typing
    $('#email, #password').on('input', function() {
        $('.alert').fadeOut(300, function() { $(this).remove(); });
    });
};
