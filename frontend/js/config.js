/**
 * API Configuration
 * 
 * Switch between local development and production by changing the environment
 */

const CONFIG = {
    // Environment: 'development' or 'production'
    environment: 'production',
    
    // API Base URLs
    api: {
        development: 'http://localhost:8080',
        production: 'https://inventory-manager-2.onrender.com/backend'
    },
    
    // Get current API base URL
    get API_BASE_URL() {
        return this.api[this.environment];
    },
    
    // Helper method to build endpoint URLs
    buildURL: function(endpoint) {
        // Remove leading slash if present to avoid double slashes
        endpoint = endpoint.replace(/^\/+/, '');
        return `${this.API_BASE_URL}/${endpoint}`;
    }
};

// For backwards compatibility, export as window variable
window.API_BASE_URL = CONFIG.API_BASE_URL;
window.CONFIG = CONFIG;
