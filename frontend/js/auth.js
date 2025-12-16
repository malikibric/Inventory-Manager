const Auth = {
    getToken: () => localStorage.getItem('token'),
    setToken: (token) => localStorage.setItem('token', token),
    removeToken: () => localStorage.removeItem('token'),
    getUser: () => JSON.parse(localStorage.getItem('user')),
    setUser: (user) => localStorage.setItem('user', JSON.stringify(user)),
    removeUser: () => localStorage.removeItem('user'),
    
    isLoggedIn: () => !!localStorage.getItem('token'),
    isAdmin: () => {
        const user = Auth.getUser();
        return user && user.role === 'admin';
    },
    
    logout: () => {
        Auth.removeToken();
        Auth.removeUser();
        window.location.hash = '#login';
        Auth.updateUI();
    },
    
    updateUI: () => {
        if (Auth.isLoggedIn()) {
            $('#loginBtn').hide();
            $('#logoutBtn').show();
            $('#dashboardLink').show();
            
            const user = Auth.getUser();
            if (user) {
                $('#navUsername').text(user.username);
                $('#navUserRole').text('(' + user.role + ')');
                $('#userInfo').removeClass('d-none').addClass('d-flex');
            }
        } else {
            $('#loginBtn').show();
            $('#logoutBtn').hide();
            $('#dashboardLink').hide();
            $('#userInfo').removeClass('d-flex').addClass('d-none');
        }
    }
};

$(document).ready(function() {
    Auth.updateUI();
    
    $('#logoutBtn').click(function(e) {
        e.preventDefault();
        Auth.logout();
    });
});
