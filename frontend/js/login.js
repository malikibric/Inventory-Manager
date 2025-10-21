(function() {
    'use strict';
    const initUsers = () => {
        if (!localStorage.getItem('users')) {
            const defaultUsers = [
                {
                    id: 1,
                    username: 'admin',
                    email: 'admin@inventory.com',
                    password: 'admin123',
                    firstName: 'Admin',
                    lastName: 'User',
                    role: 'admin'
                },
                {
                    id: 2,
                    username: 'user',
                    email: 'user@inventory.com',
                    password: 'user123',
                    firstName: 'Regular',
                    lastName: 'User',
                    role: 'user'
                }
            ];
            localStorage.setItem('users', JSON.stringify(defaultUsers));
        }
    };

    initUsers();
    window.initLoginPage = function() {
        const currentUser = localStorage.getItem('currentUser');
        if (currentUser) {
            window.location.hash = '#dashboard';
            return;
        }

        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;

                const users = JSON.parse(localStorage.getItem('users') || '[]');

                const user = users.find(u => u.email === email && u.password === password);

                if (user) {
                    const userSession = {
                        id: user.id,
                        username: user.username,
                        email: user.email,
                        firstName: user.firstName,
                        lastName: user.lastName,
                        role: user.role
                    };
                    localStorage.setItem('currentUser', JSON.stringify(userSession));

                    alert('Login successful! Welcome ' + user.firstName + '!');
                    window.location.hash = '#dashboard';
                } else {
                    alert('Invalid email or password. Please try again.\n\nDemo accounts:\n- admin@inventory.com / admin123\n- user@inventory.com / user123');
                }
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('loginForm')) {
                window.initLoginPage();
            }
        });
    } else {
        if (document.getElementById('loginForm')) {
            window.initLoginPage();
        }
    }
})();
