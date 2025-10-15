// Register Form Script
(function() {
    'use strict';

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
        
        // Validation
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }
        
        if (password.length < 6) {
            alert('Password must be at least 6 characters long!');
            return;
        }
        
        // Create username from email
        const username = email.split('@')[0];
        
        // Get existing users
        let users = JSON.parse(localStorage.getItem('users') || '[]');
        
        // Check if user already exists
        if (users.some(u => u.email === email)) {
            alert('User with this email already exists!');
            return;
        }
        
        // Create new user object
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
        
        // Add to users array
        users.push(newUser);
        localStorage.setItem('users', JSON.stringify(users));
        
        // Show success message
        alert(`Account created successfully as ${role.toUpperCase()}!`);
        
        // Redirect to login
        window.location.href = 'login.html';
    });
})();
