/**
 * OrderService - Order management service
 * Handles all order-related operations with validation
 */
const OrderService = {
    get baseURL() {
        return window.CONFIG ? `${window.CONFIG.API_BASE_URL}/orders` : '../backend/orders';
    },

    /**
     * Get all orders
     * @returns {Promise} Orders data
     */
    getAll: async () => {
        try {
            const response = await SecurityService.secureAjax({
                url: OrderService.baseURL,
                type: 'GET'
            });

            return { success: true, data: response.data || [] };
        } catch (error) {
            console.error('Error fetching orders:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to fetch orders' 
            };
        }
    },

    /**
     * Get order by ID
     * @param {number} id - Order ID
     * @returns {Promise} Order data
     */
    getById: async (id) => {
        if (!id || isNaN(id)) {
            return { success: false, error: 'Invalid order ID' };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${OrderService.baseURL}/${id}`,
                type: 'GET'
            });

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error fetching order:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to fetch order' 
            };
        }
    },

    /**
     * Create new order
     * @param {Object} orderData - Order data
     * @returns {Promise} Creation result
     */
    create: async (orderData) => {
        // Validate order data
        const validation = ValidationService.validateForm(orderData, {
            user_id: {
                label: 'User',
                rules: [
                    ValidationService.rules.required,
                    ValidationService.rules.positiveNumber
                ]
            },
            total_amount: {
                label: 'Total Amount',
                rules: [
                    ValidationService.rules.required,
                    ValidationService.rules.positiveNumber,
                    (value) => ValidationService.rules.min(value, 0.01, 'Total Amount')
                ]
            }
        });

        if (!validation.isValid) {
            return { success: false, errors: validation.errors };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: OrderService.baseURL,
                type: 'POST',
                data: {
                    user_id: parseInt(orderData.user_id),
                    total_amount: parseFloat(orderData.total_amount),
                    status: orderData.status || 'pending'
                }
            });

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error creating order:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to create order' 
            };
        }
    },

    /**
     * Update order
     * @param {number} id - Order ID
     * @param {Object} orderData - Updated order data
     * @returns {Promise} Update result
     */
    update: async (id, orderData) => {
        if (!id || isNaN(id)) {
            return { success: false, error: 'Invalid order ID' };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${OrderService.baseURL}/${id}`,
                type: 'PUT',
                data: orderData
            });

            return { success: true, data: response.data };
        } catch (error) {
            console.error('Error updating order:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to update order' 
            };
        }
    },

    /**
     * Delete order
     * @param {number} id - Order ID
     * @returns {Promise} Deletion result
     */
    delete: async (id) => {
        if (!id || isNaN(id)) {
            return { success: false, error: 'Invalid order ID' };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${OrderService.baseURL}/${id}`,
                type: 'DELETE'
            });

            return { success: true };
        } catch (error) {
            console.error('Error deleting order:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to delete order' 
            };
        }
    },

    /**
     * Get user's orders
     * @param {number} userId - User ID
     * @returns {Promise} User's orders
     */
    getByUser: async (userId) => {
        if (!userId || isNaN(userId)) {
            return { success: false, error: 'Invalid user ID' };
        }

        try {
            const response = await SecurityService.secureAjax({
                url: `${OrderService.baseURL}/user/${userId}`,
                type: 'GET'
            });

            return { success: true, data: response.data || [] };
        } catch (error) {
            console.error('Error fetching user orders:', error);
            return { 
                success: false, 
                error: error.responseJSON?.error || 'Failed to fetch user orders' 
            };
        }
    }
};
