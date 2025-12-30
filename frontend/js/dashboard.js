window.initDashboard = function () {
    // Check authentication first
    if (!Auth.isLoggedIn()) {
        window.location.hash = '#login';
        return;
    }

    // Validate session
    if (!AuthService.validateSession()) {
        return;
    }

    const user = Auth.getUser();
    
    // Safely update UI elements
    const welcomeUserName = $('#welcomeUserName');
    const userRoleBadge = $('#userRoleBadge');
    const addNewItemBtn = $('#addNewItemBtn');
    
    if (welcomeUserName.length) {
        welcomeUserName.text(SecurityService.escapeHTML(user.username || 'User'));
    }
    
    if (userRoleBadge.length) {
        userRoleBadge.text(user.role === 'admin' ? ' (Admin)' : ' (User)');
    }

    // Show/hide Add New Item button based on role
    if (Auth.isAdmin() && addNewItemBtn.length) {
        addNewItemBtn.show();
    } else if (addNewItemBtn.length) {
        addNewItemBtn.hide();
    }

    loadInventory();
    loadCategories();

    // Setup validation rules for add item form
    const addItemValidationRules = {
        itemName: {
            label: 'Product Name',
            rules: [
                ValidationService.rules.required,
                (value) => ValidationService.rules.minLength(value, 3, 'Product Name'),
                (value) => ValidationService.rules.maxLength(value, 100, 'Product Name')
            ]
        },
        itemCategory: {
            label: 'Category',
            rules: [
                ValidationService.rules.required
            ]
        },
        itemQuantity: {
            label: 'Quantity',
            rules: [
                ValidationService.rules.required,
                ValidationService.rules.positiveNumber,
                ValidationService.rules.integer
            ]
        },
        itemPrice: {
            label: 'Price',
            rules: [
                ValidationService.rules.required,
                ValidationService.rules.positiveNumber,
                (value) => ValidationService.rules.min(value, 0.01, 'Price')
            ]
        }
    };

    // Setup validation for edit item form
    const editItemValidationRules = {
        editItemName: {
            label: 'Product Name',
            rules: [
                ValidationService.rules.required,
                (value) => ValidationService.rules.minLength(value, 3, 'Product Name'),
                (value) => ValidationService.rules.maxLength(value, 100, 'Product Name')
            ]
        },
        editItemCategory: {
            label: 'Category',
            rules: [
                ValidationService.rules.required
            ]
        },
        editItemQuantity: {
            label: 'Quantity',
            rules: [
                ValidationService.rules.required,
                ValidationService.rules.positiveNumber,
                ValidationService.rules.integer
            ]
        },
        editItemPrice: {
            label: 'Price',
            rules: [
                ValidationService.rules.required,
                ValidationService.rules.positiveNumber,
                (value) => ValidationService.rules.min(value, 0.01, 'Price')
            ]
        }
    };

    // Setup real-time validation
    ValidationService.setupRealTimeValidation('addItemForm', addItemValidationRules);
    ValidationService.setupRealTimeValidation('editItemForm', editItemValidationRules);

    // Add Item
    $('#saveItemBtn').off('click').on('click', async function () {
        // Clear previous validation
        ValidationService.clearValidation('addItemForm');

        const itemName = $('#itemName').val().trim();
        const itemCategory = $('#itemCategory').val();
        const itemQuantity = $('#itemQuantity').val();
        const itemPrice = $('#itemPrice').val();
        
        const formData = {
            itemName,
            itemCategory,
            itemQuantity,
            itemPrice
        };

        // Validate form
        const validation = ValidationService.validateForm(formData, addItemValidationRules);
        if (!validation.isValid) {
            ValidationService.displayErrors(validation.errors);
            return;
        }

        const item = {
            name: itemName,
            category_id: itemCategory,
            quantity: itemQuantity,
            price: itemPrice,
            description: 'Added via Dashboard',
            supplier_id: 1
        };

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');

        try {
            const result = await ProductService.create(item);

            if (result.success) {
                // Hide modal
                const modalElement = document.getElementById('addItemModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();
                }

                // Reset form
                $('#addItemForm')[0].reset();
                ValidationService.clearValidation('addItemForm');
                
                // Reload inventory
                await loadInventory();

                // Show success message
                showNotification('success', 'Item added successfully!');
            } else {
                if (result.errors) {
                    ValidationService.displayErrors(result.errors);
                } else {
                    showNotification('error', result.error || 'Failed to add item');
                }
            }
        } catch (error) {
            console.error('Error adding item:', error);
            showNotification('error', 'An unexpected error occurred');
        } finally {
            btn.prop('disabled', false).html(originalText);
        }
    });

    // Update Item
    $('#updateItemBtn').off('click').on('click', async function () {
        // Clear previous validation
        ValidationService.clearValidation('editItemForm');

        const id = $('#editItemId').val();
        const editItemName = $('#editItemName').val().trim();
        const editItemCategory = $('#editItemCategory').val();
        const editItemQuantity = $('#editItemQuantity').val();
        const editItemPrice = $('#editItemPrice').val();

        const formData = {
            editItemName,
            editItemCategory,
            editItemQuantity,
            editItemPrice
        };

        // Validate form
        const validation = ValidationService.validateForm(formData, editItemValidationRules);
        if (!validation.isValid) {
            ValidationService.displayErrors(validation.errors);
            return;
        }

        const item = {
            name: editItemName,
            category_id: editItemCategory,
            quantity: editItemQuantity,
            price: editItemPrice,
            description: 'Updated via Dashboard',
            supplier_id: 1
        };

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Updating...');

        try {
            const result = await ProductService.update(id, item);

            if (result.success) {
                // Hide modal
                const modalElement = document.getElementById('editItemModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();
                }

                // Reload inventory
                await loadInventory();

                // Show success message
                showNotification('success', 'Item updated successfully!');
            } else {
                if (result.errors) {
                    ValidationService.displayErrors(result.errors);
                } else {
                    showNotification('error', result.error || 'Failed to update item');
                }
            }
        } catch (error) {
            console.error('Error updating item:', error);
            showNotification('error', 'An unexpected error occurred');
        } finally {
            btn.prop('disabled', false).html(originalText);
        }
    });
};

async function loadInventory() {
    try {
        const result = await ProductService.getAll();

        if (result.success) {
            let products = result.data;
            
            // Add sample data if no products exist
            if (!products || products.length === 0) {
                products = [
                    {id: 1, name: 'Laptop HP Pavilion', category_id: 'Electronics', quantity: 15, price: 899.99},
                    {id: 2, name: 'Office Chair', category_id: 'Furniture', quantity: 8, price: 249.50},
                    {id: 3, name: 'USB-C Cable', category_id: 'Electronics', quantity: 50, price: 12.99},
                    {id: 4, name: 'Desk Lamp', category_id: 'Furniture', quantity: 5, price: 34.99},
                    {id: 5, name: 'Wireless Mouse', category_id: 'Electronics', quantity: 22, price: 29.99},
                    {id: 6, name: 'Notebook A4', category_id: 'Stationery', quantity: 100, price: 3.50},
                    {id: 7, name: 'Ergonomic Keyboard', category_id: 'Electronics', quantity: 12, price: 79.99},
                    {id: 8, name: 'Monitor Stand', category_id: 'Furniture', quantity: 6, price: 45.00}
                ];
            }
            
            const tbody = $('#inventoryTableBody');
            tbody.empty();

            let totalItems = 0;
            let inStock = 0;
            let lowStock = 0;
            let totalValue = 0;

            products.forEach(product => {
                totalItems++;
                if (product.quantity > 10) inStock++;
                else lowStock++;
                totalValue += product.price * product.quantity;

                const statusClass = product.quantity > 10 ? 'in-stock' : 'low-stock';
                const statusText = product.quantity > 10 ? 'In Stock' : 'Low Stock';

                // Sanitize product data for display
                const safeName = SecurityService.escapeHTML(product.name);
                const safeCategory = SecurityService.escapeHTML(product.category_id);

                let actions = '';
                if (Auth.isAdmin()) {
                    // Safely encode product data for data attribute
                    const productJson = JSON.stringify(product).replace(/"/g, '&quot;');
                    actions = `
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${product.id}" data-product="${productJson}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${product.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }

                tbody.append(`
                    <tr>
                        <td>${product.id}</td>
                        <td>${safeName}</td>
                        <td>${safeCategory}</td>
                        <td>${product.quantity}</td>
                        <td>$${product.price}</td>
                        <td>$${(product.price * product.quantity).toFixed(2)}</td>
                        <td><span class="${statusClass}">${statusText}</span></td>
                        <td class="table-actions">${actions}</td>
                    </tr>
                `);
            });

            $('#totalItems').text(totalItems);
            $('#inStockItems').text(inStock);
            $('#lowStockItems').text(lowStock);
            $('#totalValue').text('$' + totalValue.toFixed(2));

            // Attach handlers
            $('.edit-btn').off('click').on('click', function () {
                const product = $(this).data('product');
                $('#editItemId').val(product.id);
                $('#editItemName').val(SecurityService.escapeHTML(product.name));
                $('#editItemCategory').val(product.category_id);
                $('#editItemQuantity').val(product.quantity);
                $('#editItemPrice').val(product.price);
                
                // Clear validation
                ValidationService.clearValidation('editItemForm');
                
                // Open modal
                const modalElement = document.getElementById('editItemModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });

            $('.delete-btn').off('click').on('click', async function () {
                const id = $(this).data('id');
                
                // Show confirmation modal instead of alert
                if (confirm('Are you sure you want to delete this item?')) {
                    try {
                        const result = await ProductService.delete(id);
                        
                        if (result.success) {
                            await loadInventory();
                            showNotification('success', 'Item deleted successfully!');
                        } else {
                            showNotification('error', result.error || 'Failed to delete item');
                        }
                    } catch (error) {
                        console.error('Error deleting item:', error);
                        showNotification('error', 'An unexpected error occurred');
                    }
                }
            });
        } else {
            showNotification('error', result.error || 'Failed to load inventory');
        }
    } catch (error) {
        console.error('Error loading inventory:', error);
        if (error.status === 401) {
            Auth.logout();
        }
    }
}

async function loadCategories() {
    try {
        const result = await CategoryService.getAll();

        if (result.success) {
            const categories = result.data;
            const options = '<option value="">Select category</option>' +
                categories.map(c => `<option value="${c.id}">${SecurityService.escapeHTML(c.name)}</option>`).join('');
            $('#itemCategory').html(options);
            $('#editItemCategory').html(options);
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Notification helper function
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
             style="z-index: 9999; min-width: 300px;" role="alert">
            <i class="fas ${icon} me-2"></i>${SecurityService.escapeHTML(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('body').append(notification);
    
    // Auto dismiss after 3 seconds
    setTimeout(() => {
        $('.alert').fadeOut(300, function() { $(this).remove(); });
    }, 3000);
}
