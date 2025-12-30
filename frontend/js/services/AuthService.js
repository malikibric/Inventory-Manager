/**
 * AuthService - Authentication service with validation and security
 * Handles user authentication, token management, and authorization
 */
const AuthService = {
    /**
     * API base URL - uses CONFIG.API_BASE_URL from config.js
     */
    get baseURL() {
        return window.CONFIG ? window.CONFIG.API_BASE_URL : '../backend';
    },

    /**
     * Login user
     * @param {Object} credentials - User credentials {email, password}
     * @returns {Promise} Login result
     */
    login: async (credentials) => {
        // Validate credentials
        const validation = ValidationService.validateForm(credentials, {
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
        });

        if (!validation.isValid) {
            return { success: false, errors: validation.errors };
        }

        // Check rate limiting
        if (!SecurityService.rateLimiter.isAllowed('login', 5, 60000)) {
            return { 
                success: false, 
                error: 'Too many login attempts. Please try again later.' 
            };
        }

        try {
            const response = await $.ajax({
                url: `${AuthService.baseURL}/login`,
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                cache: false,
                data: JSON.stringify({
                    email: SecurityService.sanitizeInput(credentials.email),
                    password: credentials.password // Don't sanitize password
                })
            });

            console.log('Login response:', response);
            console.log('Response type:', typeof response);
            console.log('Has success?', response.hasOwnProperty('success'), response.success);
            console.log('Has token?', response.hasOwnProperty('token'), response.token);

            if (response.success && response.token) {
                // Store token and user info
                Auth.setToken(response.token);
                Auth.setUser(response.user);
                Auth.updateUI();
                console.log('Token stored:', localStorage.getItem('token'));
                return { success: true, user: response.user };
            }

            console.log('Login failed - no token in response');
            return { success: false, error: 'Invalid credentials' };
        } catch (error) {
            console.error('Login error:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Login failed. Please try again.' 
            };
        }
    },

    /**
     * Register new user
     * @param {Object} userData - User registration data
     * @returns {Promise} Registration result
     */
    register: async (userData) => {
        // Validate user data
        const validation = ValidationService.validateForm(userData, {
            firstName: {
                label: 'First Name',
                rules: [
                    ValidationService.rules.required,
                    (value) => ValidationService.rules.minLength(value, 2, 'First Name'),
                    (value) => ValidationService.rules.maxLength(value, 50, 'First Name')
                ]
            },
            lastName: {
                label: 'Last Name',
                rules: [
                    ValidationService.rules.required,
                    (value) => ValidationService.rules.minLength(value, 2, 'Last Name'),
                    (value) => ValidationService.rules.maxLength(value, 50, 'Last Name')
                ]
            },
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
                    ValidationService.rules.password
                ]
            },
            confirmPassword: {
                label: 'Confirm Password',
                rules: [
                    ValidationService.rules.required,
                    (value) => ValidationService.rules.passwordMatch(userData.password, value)
                ]
            }
        });

        if (!validation.isValid) {
            return { success: false, errors: validation.errors };
        }

        // Check SQL injection
        if (!SecurityService.validateSQLInjection(userData.email) ||
            !SecurityService.validateSQLInjection(userData.firstName) ||
            !SecurityService.validateSQLInjection(userData.lastName)) {
            return { success: false, error: 'Invalid input detected' };
        }

        try {
            const response = await $.ajax({
                url: `${AuthService.baseURL}/users`,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    username: SecurityService.sanitizeInput(`${userData.firstName} ${userData.lastName}`),
                    email: SecurityService.sanitizeInput(userData.email),
                    password: userData.password,
                    role: 'user'
                })
            });

            return { success: true, data: response };
        } catch (error) {
            console.error('Registration error:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Registration failed. Please try again.' 
            };
        }
    },

    /**
     * Logout user
     */
    logout: () => {
        Auth.removeToken();
        Auth.removeUser();
        Auth.updateUI();
        window.location.hash = '#login';
    },

    /**
     * Validate session
     * @returns {boolean} True if session is valid
     */
    validateSession: () => {
        const token = Auth.getToken();
        console.log('Validating session, token:', token ? 'exists' : 'missing');
        
        if (!token) return false;

        // Check token expiration (if JWT)
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const now = Math.floor(Date.now() / 1000);
            
            console.log('Token expiration:', new Date(payload.exp * 1000));
            console.log('Current time:', new Date(now * 1000));
            console.log('Token expired?', payload.exp < now);
            
            if (payload.exp && payload.exp < now) {
                // Token expired
                console.log('Token expired, logging out');
                AuthService.logout();
                return false;
            }
            
            console.log('Session valid');
            return true;
        } catch (e) {
            console.error('Token parsing error:', e);
            // If token parsing fails, keep the session
            return true;
        }
    },

    /**
     * Refresh authentication token
     * @returns {Promise} Refresh result
     */
    refreshToken: async () => {
        const token = Auth.getToken();
        if (!token) return { success: false };

        try {
            const response = await $.ajax({
                url: `${AuthService.baseURL}/refresh-token`,
                type: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (response.success && response.token) {
                Auth.setToken(response.token);
                return { success: true };
            }

            return { success: false };
        } catch (error) {
            console.error('Token refresh error:', error);
            return { success: false };
        }
    },

    /**
     * Check if user has specific role
     * @param {string} role - Role to check
     * @returns {boolean} True if user has role
     */
    hasRole: (role) => {
        const user = Auth.getUser();
        return user && user.role === role;
    },

    /**
     * Get current user
     * @returns {Object|null} User object or null
     */
    getCurrentUser: () => {
        return Auth.getUser();
    }
};
