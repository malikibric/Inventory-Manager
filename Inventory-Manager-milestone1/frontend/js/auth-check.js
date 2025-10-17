// Auth Check Script - Checks if user is logged in and updates UI
(function() {
    'use strict';

    // Check if user is logged in
    function checkAuthStatus() {
        const currentUser = localStorage.getItem('currentUser');
        const loginBtn = document.getElementById('loginBtn');
        const logoutBtn = document.getElementById('logoutBtn');
        const userInfo = document.getElementById('userInfo');
        const navUsername = document.getElementById('navUsername');
        const dashboardLink = document.getElementById('dashboardLink');
        
        if (currentUser) {
            const user = JSON.parse(currentUser);
            // Hide login button, show logout and user info
            if (loginBtn) loginBtn.style.display = 'none';
            if (logoutBtn) {
                logoutBtn.style.display = 'block';
                logoutBtn.classList.add('d-lg-block');
            }
            if (userInfo) {
                userInfo.style.display = 'flex';
                userInfo.classList.add('d-lg-flex');
            }
            if (navUsername) navUsername.textContent = user.username;
            
            // Display role badge
            const navUserRole = document.getElementById('navUserRole');
            if (navUserRole && user.role) {
                const roleClass = user.role === 'admin' ? 'bg-danger' : 'bg-primary';
                const roleText = user.role === 'admin' ? 'ADMIN' : 'USER';
                navUserRole.innerHTML = `<span class="badge ${roleClass}">${roleText}</span>`;
            }
            
            if (dashboardLink) dashboardLink.style.display = 'block';
        } else {
            // Show login button, hide logout and user info
            if (loginBtn) loginBtn.style.display = 'block';
            if (logoutBtn) logoutBtn.style.display = 'none';
            if (userInfo) userInfo.style.display = 'none';
            if (dashboardLink) dashboardLink.style.display = 'none';
        }
    }
    
    // Logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            localStorage.removeItem('currentUser');
            
            // Check if we're in views folder or root
            const currentPath = window.location.pathname;
            if (currentPath.includes('/views/')) {
                window.location.href = '../index.html';
            } else {
                window.location.href = 'index.html';
            }
        });
    }
    
    // Run on page load
    checkAuthStatus();
})();
