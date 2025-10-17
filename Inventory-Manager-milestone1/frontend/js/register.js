(function() {
    'use strict';
    window.initRegisterPage = function() {
        const registerForm = document.getElementById('registerForm');
        if (!registerForm) return;

        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const company = document.getElementById('company').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const role = document.getElementById('role').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters long!');
                return;
            }
            
            const username = email.split('@')[0];
            
            let users = JSON.parse(localStorage.getItem('users') || '[]');
            
            if (users.some(u => u.email === email)) {
                alert('User with this email already exists!');
                return;
            }
            
            const newUser = {
                id: Date.now(),
                username: username,
                firstName: firstName,
                lastName: lastName,
                email: email,
                phone: phone,
                company: company,
                password: password,
                role: role,
                createdAt: new Date().toISOString()
            };
            
            users.push(newUser);
            localStorage.setItem('users', JSON.stringify(users));
            
            alert(`Account created successfully as ${role.toUpperCase()}!`);
            window.location.hash = '#login';
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('registerForm')) {
                window.initRegisterPage();
            }
        });
    } else {
        if (document.getElementById('registerForm')) {
            window.initRegisterPage();
        }
    }
})();
