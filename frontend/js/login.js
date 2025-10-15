// Login functionality
(function() {
    'use strict';

    // Sample users database (stored in localStorage)
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

    // Initialize users
    initUsers();

    // Check if already logged in
    const currentUser = localStorage.getItem('currentUser');
    if (currentUser) {
        window.location.href = 'dashboard.html';
        return;
    }

    // Handle login form submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            // Get users from localStorage
            const users = JSON.parse(localStorage.getItem('users') || '[]');

            // Find user by email and password
            const user = users.find(u => u.email === email && u.password === password);

            if (user) {
                // Store current user (without password)
                const userSession = {
                    id: user.id,
                    username: user.username,
                    email: user.email,
                    firstName: user.firstName,
                    lastName: user.lastName,
                    role: user.role
                };
                localStorage.setItem('currentUser', JSON.stringify(userSession));

                // Show success message
                alert('Login successful! Welcome ' + user.firstName + '!');

                // Redirect to dashboard
                window.location.href = 'dashboard.html';
            } else {
                alert('Invalid email or password. Please try again.\n\nDemo accounts:\n- admin@inventory.com / admin123\n- user@inventory.com / user123');
            }
        });
    }
})();
