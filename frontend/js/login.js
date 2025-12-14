window.initLoginPage = function () {
    $('#loginForm').off('submit').on('submit', function (e) {
        e.preventDefault();

        const email = $('#email').val();
        const password = $('#password').val();
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

        $.ajax({
            url: '../backend/login',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email: email, password: password }),
            success: function (response) {
                if (response.success) {
                    Auth.setToken(response.token);
                    Auth.setUser(response.user);
                    Auth.updateUI();
                    window.location.hash = '#dashboard';
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                alert('Login failed: ' + (response ? response.error : 'Unknown error'));
            },
            complete: function () {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
};
