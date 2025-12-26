/**
 * CategoryService - Category management service
 * Handles all category-related operations with validation
 */
const CategoryService = {
    baseURL: '../backend/categories',

    /**
     * Get all categories
     * @returns {Promise} Categories data
     */
    getAll: async () => {
        try {
            const response = await SecurityService.secureAjax({
                url: CategoryService.baseURL,
                type: 'GET'
            });

            return { success: true, data: response.data || [] };
        } catch (error) {
            console.error('Error fetching categories:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to fetch categories' 
            };
        }
    },

    /**
     * Get category by ID
     * @param {number} id - Category ID
     * @returns {Promise} Category data
     */
    getById: async (id) => {
        if (!id || isNaN(id)) {
            return { success: false, error: 'Invalid category ID' };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${CategoryService.baseURL}/${id}`,
                type: 'GET'
            });

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error fetching category:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to fetch category' 
            };
        }
    },

    /**
     * Create new category
     * @param {Object} categoryData - Category data
     * @returns {Promise} Creation result
     */
    create: async (categoryData) => {
        // Validate category data
        const validation = ValidationService.validateForm(categoryData, {
            name: {
                label: 'Category Name',
                rules: [
                    ValidationService.rules.required,
                    (value) => ValidationService.rules.minLength(value, 2, 'Category Name'),
                    (value) => ValidationService.rules.maxLength(value, 50, 'Category Name')
                ]
            }
        });

        if (!validation.isValid) {
            return { success: false, errors: validation.errors };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: CategoryService.baseURL,
                type: 'POST',
                data: {
                    name: SecurityService.sanitizeInput(categoryData.name),
                    description: SecurityService.sanitizeInput(categoryData.description || '')
                }
            });

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error creating category:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to create category' 
            };
        }
    },

    /**
     * Update category
     * @param {number} id - Category ID
     * @param {Object} categoryData - Updated category data
     * @returns {Promise} Update result
     */
    update: async (id, categoryData) => {
        if (!id || isNaN(id)) {
            return { success: false, error: 'Invalid category ID' };
        }

        const validation = ValidationService.validateForm(categoryData, {
            name: {
                label: 'Category Name',
                rules: [
                    ValidationService.rules.required,
                    (value) => ValidationService.rules.minLength(value, 2, 'Category Name'),
                    (value) => ValidationService.rules.maxLength(value, 50, 'Category Name')
                ]
            }
        });

        if (!validation.isValid) {
            return { success: false, errors: validation.errors };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${CategoryService.baseURL}/${id}`,
                type: 'PUT',
                data: {
                    name: SecurityService.sanitizeInput(categoryData.name),
                    description: SecurityService.sanitizeInput(categoryData.description || '')
                }
            });

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error updating category:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to update category' 
            };
        }
    },

    /**
     * Delete category
     * @param {number} id - Category ID
     * @returns {Promise} Deletion result
     */
    delete: async (id) => {
        if (!id || isNaN(id)) {
            return { success: false, error: 'Invalid category ID' };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${CategoryService.baseURL}/${id}`,
                type: 'DELETE'
            });

            return { success: true };
        } catch (error) {
            console.error('Error deleting category:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to delete category' 
            };
        }
    }
};
