/**
 * ProductService - Product management service
 * Handles all product-related operations with validation
 */
const ProductService = {
    get baseURL() {
        return window.CONFIG ? `${window.CONFIG.API_BASE_URL}/products` : '../backend/products';
    },

    /**
     * Get all products
     * @returns {Promise} Products data
     */
    getAll: async () => {
        try {
            const response = await SecurityService.secureAjax({
                url: ProductService.baseURL,
                type: 'GET'
            });

            return { success: true, data: response.data || [] };
        } catch (error) {
            console.error('Error fetching products:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to fetch products' 
            };
        }
    },

    /**
     * Get product by ID
     * @param {number} id - Product ID
     * @returns {Promise} Product data
     */
    getById: async (id) => {
        if (!id || isNaN(id)) {
            return { success: false, error: 'Invalid product ID' };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${ProductService.baseURL}/${id}`,
                type: 'GET'
            });

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error fetching product:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to fetch product' 
            };
        }
    },

    /**
     * Create new product
     * @param {Object} productData - Product data
     * @returns {Promise} Creation result
     */
    create: async (productData) => {
        // Validate product data
        const validation = ValidationService.validateForm(productData, {
            name: {
                label: 'Product Name',
                rules: [
                    ValidationService.rules.required,
                    (value) => ValidationService.rules.minLength(value, 3, 'Product Name'),
                    (value) => ValidationService.rules.maxLength(value, 100, 'Product Name')
                ]
            },
            category_id: {
                label: 'Category',
                rules: [
                    ValidationService.rules.required,
                    ValidationService.rules.positiveNumber
                ]
            },
            quantity: {
                label: 'Quantity',
                rules: [
                    ValidationService.rules.required,
                    ValidationService.rules.positiveNumber,
                    ValidationService.rules.integer
                ]
            },
            price: {
                label: 'Price',
                rules: [
                    ValidationService.rules.required,
                    ValidationService.rules.positiveNumber,
                    (value) => ValidationService.rules.min(value, 0.01, 'Price')
                ]
            }
        });

        if (!validation.isValid) {
            return { success: false, errors: validation.errors };
        }

        // Check SQL injection
        if (!SecurityService.validateSQLInjection(productData.name)) {
            return { success: false, error: 'Invalid product name' };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: ProductService.baseURL,
                type: 'POST',
                data: {
                    name: SecurityService.sanitizeInput(productData.name),
                    category_id: parseInt(productData.category_id),
                    quantity: parseInt(productData.quantity),
                    price: parseFloat(productData.price),
                    description: SecurityService.sanitizeInput(productData.description || ''),
                    supplier_id: parseInt(productData.supplier_id || 1)
                }
            });

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error creating product:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to create product' 
            };
        }
    },

    /**
     * Update product
     * @param {number} id - Product ID
     * @param {Object} productData - Updated product data
     * @returns {Promise} Update result
     */
    update: async (id, productData) => {
        if (!id || isNaN(id)) {
            return { success: false, error: 'Invalid product ID' };
        }

        // Validate product data
        const validation = ValidationService.validateForm(productData, {
            name: {
                label: 'Product Name',
                rules: [
                    ValidationService.rules.required,
                    (value) => ValidationService.rules.minLength(value, 3, 'Product Name'),
                    (value) => ValidationService.rules.maxLength(value, 100, 'Product Name')
                ]
            },
            category_id: {
                label: 'Category',
                rules: [
                    ValidationService.rules.required,
                    ValidationService.rules.positiveNumber
                ]
            },
            quantity: {
                label: 'Quantity',
                rules: [
                    ValidationService.rules.required,
                    ValidationService.rules.positiveNumber,
                    ValidationService.rules.integer
                ]
            },
            price: {
                label: 'Price',
                rules: [
                    ValidationService.rules.required,
                    ValidationService.rules.positiveNumber
                ]
            }
        });

        if (!validation.isValid) {
            return { success: false, errors: validation.errors };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${ProductService.baseURL}/${id}`,
                type: 'PUT',
                data: {
                    name: SecurityService.sanitizeInput(productData.name),
                    category_id: parseInt(productData.category_id),
                    quantity: parseInt(productData.quantity),
                    price: parseFloat(productData.price),
                    description: SecurityService.sanitizeInput(productData.description || ''),
                    supplier_id: parseInt(productData.supplier_id || 1)
                }
            });

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error updating product:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to update product' 
            };
        }
    },

    /**
     * Delete product
     * @param {number} id - Product ID
     * @returns {Promise} Deletion result
     */
    delete: async (id) => {
        if (!id || isNaN(id)) {
            return { success: false, error: 'Invalid product ID' };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${ProductService.baseURL}/${id}`,
                type: 'DELETE'
            });

            return { success: true };
        } catch (error) {
            console.error('Error deleting product:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to delete product' 
            };
        }
    },

    /**
     * Search products
     * @param {string} query - Search query
     * @returns {Promise} Search results
     */
    search: async (query) => {
        if (!query || query.trim() === '') {
            return ProductService.getAll();
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${ProductService.baseURL}/search?q=${encodeURIComponent(query)}`,
                type: 'GET'
            });

            return { success: true, data: response.data || [] };
        } catch (error) {
            console.error('Error searching products:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Search failed' 
            };
        }
    },

    /**
     * Get low stock products
     * @param {number} threshold - Stock threshold
     * @returns {Promise} Low stock products
     */
    getLowStock: async (threshold = 10) => {
        try {
            const result = await ProductService.getAll();
            if (!result.success) return result;

            const lowStockProducts = result.data.filter(p => p.quantity <= threshold);
            return { success: true, data: lowStockProducts };
        } catch (error) {
            console.error('Error getting low stock products:', error);
            return { success: false, error: 'Failed to get low stock products' };
        }
    }
};
