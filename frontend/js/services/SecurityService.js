/**
 * SecurityService - Frontend security utilities
 * Provides XSS prevention, input sanitization, CSRF protection
 */
const SecurityService = {
    /**
     * Sanitize user input to prevent XSS attacks
     * @param {string} input - The input string to sanitize
     * @returns {string} Sanitized string
     */
    sanitizeInput: (input) => {
        if (typeof input !== 'string') return input;
        
        const div = document.createElement('div');
        div.textContent = input;
        return div.innerHTML;
    },

    /**
     * Sanitize HTML to allow only safe tags
     * @param {string} html - The HTML string to sanitize
     * @returns {string} Sanitized HTML
     */
    sanitizeHTML: (html) => {
        const allowedTags = ['b', 'i', 'em', 'strong', 'a', 'p', 'br'];
        const div = document.createElement('div');
        div.innerHTML = html;
        
        // Remove script tags and event handlers
        const scripts = div.querySelectorAll('script');
        scripts.forEach(script => script.remove());
        
        // Remove all event attributes (onclick, onerror, etc.)
        const allElements = div.querySelectorAll('*');
        allElements.forEach(element => {
            // Remove event attributes
            Array.from(element.attributes).forEach(attr => {
                if (attr.name.startsWith('on')) {
                    element.removeAttribute(attr.name);
                }
            });
            
            // Remove disallowed tags
            if (!allowedTags.includes(element.tagName.toLowerCase())) {
                element.replaceWith(...element.childNodes);
            }
        });
        
        return div.innerHTML;
    },

    /**
     * Escape HTML special characters
     * @param {string} text - Text to escape
     * @returns {string} Escaped text
     */
    escapeHTML: (text) => {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#x27;',
            "/": '&#x2F;'
        };
        return String(text).replace(/[&<>"'/]/g, (s) => map[s]);
    },

    /**
     * Generate CSRF token
     * @returns {string} CSRF token
     */
    generateCSRFToken: () => {
        const token = Array.from(crypto.getRandomValues(new Uint8Array(32)))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
        sessionStorage.setItem('csrf_token', token);
        return token;
    },

    /**
     * Get CSRF token
     * @returns {string|null} CSRF token
     */
    getCSRFToken: () => {
        let token = sessionStorage.getItem('csrf_token');
        if (!token) {
            token = SecurityService.generateCSRFToken();
        }
        return token;
    },

    /**
     * Validate CSRF token
     * @param {string} token - Token to validate
     * @returns {boolean} True if valid
     */
    validateCSRFToken: (token) => {
        const storedToken = sessionStorage.getItem('csrf_token');
        return token === storedToken;
    },

    /**
     * Add CSRF token to form
     * @param {string} formId - The ID of the form
     */
    addCSRFTokenToForm: (formId) => {
        const form = $(`#${formId}`);
        if (!form.length) return;

        // Remove existing CSRF token field
        form.find('input[name="csrf_token"]').remove();

        // Add new CSRF token field
        const token = SecurityService.getCSRFToken();
        form.append(`<input type="hidden" name="csrf_token" value="${token}">`);
    },

    /**
     * Validate input against SQL injection patterns
     * @param {string} input - Input to validate
     * @returns {boolean} True if safe
     */
    validateSQLInjection: (input) => {
        const sqlPatterns = [
            /(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE)\b)/gi,
            /(--|#|\/\*|\*\/)/g,
            /(\bOR\b.*=.*)/gi,
            /(\bAND\b.*=.*)/gi,
            /(';|";)/g
        ];

        for (let pattern of sqlPatterns) {
            if (pattern.test(input)) {
                return false;
            }
        }
        return true;
    },

    /**
     * Validate file upload
     * @param {File} file - The file to validate
     * @param {Object} options - Validation options
     * @returns {Object} Validation result
     */
    validateFileUpload: (file, options = {}) => {
        const {
            maxSize = 5 * 1024 * 1024, // 5MB default
            allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'],
            allowedExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.pdf']
        } = options;

        if (!file) {
            return { valid: false, error: 'No file selected' };
        }

        // Check file size
        if (file.size > maxSize) {
            return { valid: false, error: `File size must not exceed ${maxSize / 1024 / 1024}MB` };
        }

        // Check file type
        if (!allowedTypes.includes(file.type)) {
            return { valid: false, error: 'File type not allowed' };
        }

        // Check file extension
        const fileName = file.name.toLowerCase();
        const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
        if (!hasValidExtension) {
            return { valid: false, error: 'File extension not allowed' };
        }

        return { valid: true };
    },

    /**
     * Encode data for URL
     * @param {Object} data - Data to encode
     * @returns {string} Encoded URL parameters
     */
    encodeURLParams: (data) => {
        return Object.keys(data)
            .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(data[key]))
            .join('&');
    },

    /**
     * Secure AJAX request wrapper
     * @param {Object} options - AJAX options
     * @returns {Promise} AJAX promise
     */
    secureAjax: (options) => {
        // Add CSRF token to headers
        const csrfToken = SecurityService.getCSRFToken();
        options.headers = options.headers || {};
        options.headers['X-CSRF-Token'] = csrfToken;

        // Add authentication token if available
        const authToken = Auth.getToken();
        if (authToken) {
            options.headers['Authorization'] = 'Bearer ' + authToken;
        }

        // Sanitize data if it's an object
        if (options.data && typeof options.data === 'object') {
            const sanitizedData = {};
            for (let key in options.data) {
                sanitizedData[key] = typeof options.data[key] === 'string' 
                    ? SecurityService.sanitizeInput(options.data[key])
                    : options.data[key];
            }
            options.data = JSON.stringify(sanitizedData);
        }

        // Set content type
        options.contentType = options.contentType || 'application/json';

        return $.ajax(options);
    },

    /**
     * Rate limiting for API calls
     */
    rateLimiter: {
        requests: {},
        
        /**
         * Check if request should be allowed
         * @param {string} key - Unique key for the request type
         * @param {number} maxRequests - Maximum requests allowed
         * @param {number} timeWindow - Time window in milliseconds
         * @returns {boolean} True if allowed
         */
        isAllowed: function(key, maxRequests = 5, timeWindow = 60000) {
            const now = Date.now();
            
            if (!this.requests[key]) {
                this.requests[key] = [];
            }
            
            // Remove old requests outside time window
            this.requests[key] = this.requests[key].filter(time => now - time < timeWindow);
            
            // Check if limit exceeded
            if (this.requests[key].length >= maxRequests) {
                return false;
            }
            
            // Add current request
            this.requests[key].push(now);
            return true;
        }
    },

    /**
     * Prevent clickjacking
     */
    preventClickjacking: () => {
        if (window.top !== window.self) {
            window.top.location = window.self.location;
        }
    },

    /**
     * Secure local storage wrapper
     */
    secureStorage: {
        /**
         * Set item in storage with encryption (basic)
         * @param {string} key - Storage key
         * @param {*} value - Value to store
         */
        setItem: (key, value) => {
            try {
                const data = JSON.stringify(value);
                const encoded = btoa(data); // Basic encoding (not true encryption)
                localStorage.setItem(key, encoded);
            } catch (e) {
                console.error('Error storing data:', e);
            }
        },

        /**
         * Get item from storage with decryption
         * @param {string} key - Storage key
         * @returns {*} Stored value
         */
        getItem: (key) => {
            try {
                const encoded = localStorage.getItem(key);
                if (!encoded) return null;
                const data = atob(encoded);
                return JSON.parse(data);
            } catch (e) {
                console.error('Error retrieving data:', e);
                return null;
            }
        },

        /**
         * Remove item from storage
         * @param {string} key - Storage key
         */
        removeItem: (key) => {
            localStorage.removeItem(key);
        }
    },

    /**
     * Content Security Policy headers (for information)
     */
    getCSPHeaders: () => {
        return {
            'Content-Security-Policy': "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:;",
            'X-Content-Type-Options': 'nosniff',
            'X-Frame-Options': 'DENY',
            'X-XSS-Protection': '1; mode=block',
            'Referrer-Policy': 'strict-origin-when-cross-origin'
        };
    }
};

// Initialize security features on page load
$(document).ready(function() {
    // Prevent clickjacking
    SecurityService.preventClickjacking();
    
    // Generate CSRF token
    SecurityService.generateCSRFToken();
});
