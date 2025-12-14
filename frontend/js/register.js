window.initRegisterPage = function () {
    $('#registerForm').off('submit').on('submit', function (e) {
        e.preventDefault();

        const firstName = $('#firstName').val();
        const lastName = $('#lastName').val();
        const email = $('#email').val();
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();

        if (password !== confirmPassword) {
            alert('Passwords do not match');
            return;
        }

        const username = firstName + ' ' + lastName;

        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating Account...');

        $.ajax({
            url: '../backend/users',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                username: username,
                email: email,
                password: password,
                role: 'user'
            }),
            success: function (response) {
                if (response.success) {
                    alert('Registration successful! Please login.');
                    window.location.hash = '#login';
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                alert('Registration failed: ' + (response ? response.error : 'Unknown error'));
            },
            complete: function () {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
};
