window.initDashboard = function () {
    // Check authentication first
    if (!Auth.isLoggedIn()) {
        window.location.hash = '#login';
        return;
    }

    const user = Auth.getUser();
    
    // Safely update UI elements
    const welcomeUserName = $('#welcomeUserName');
    const userRoleBadge = $('#userRoleBadge');
    const addNewItemBtn = $('#addNewItemBtn');
    
    if (welcomeUserName.length) {
        welcomeUserName.text(user.username || 'User');
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

    // Add Item
    $('#saveItemBtn').off('click').on('click', function () {
        const itemName = $('#itemName');
        const itemCategory = $('#itemCategory');
        const itemQuantity = $('#itemQuantity');
        const itemPrice = $('#itemPrice');
        
        // Validate fields exist and have values
        if (!itemName.length || !itemName.val()) {
            alert('⚠️ Please enter item name');
            return;
        }
        if (!itemCategory.length || !itemCategory.val()) {
            alert('⚠️ Please select a category');
            return;
        }
        
        const item = {
            name: itemName.val(),
            category_id: itemCategory.val(),
            quantity: itemQuantity.val() || 0,
            price: itemPrice.val() || 0,
            description: 'Added via Dashboard',
            supplier_id: 1 // Default supplier
        };

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');

        $.ajax({
            url: '../backend/products',
            type: 'POST',
            headers: { 'Authorization': 'Bearer ' + Auth.getToken() },
            contentType: 'application/json',
            data: JSON.stringify(item),
            success: function (response) {
                if (response.success) {
                    // Hide modal using Bootstrap 5 method
                    const modalElement = document.getElementById('addItemModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                    $('#addItemForm')[0].reset();
                    loadInventory();
                    alert('✅ Item added successfully!');
                }
            },
            error: function (xhr) {
                alert('❌ Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Unknown'));
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Update Item
    $('#updateItemBtn').off('click').on('click', function () {
        const id = $('#editItemId').val();
        const item = {
            name: $('#editItemName').val(),
            category_id: $('#editItemCategory').val(),
            quantity: $('#editItemQuantity').val(),
            price: $('#editItemPrice').val(),
            description: 'Updated via Dashboard',
            supplier_id: 1
        };

        $.ajax({
            url: '../backend/products/' + id,
            type: 'PUT',
            headers: { 'Authorization': 'Bearer ' + Auth.getToken() },
            contentType: 'application/json',
            data: JSON.stringify(item),
            success: function (response) {
                if (response.success) {
                    // Hide modal using Bootstrap 5 method
                    const modalElement = document.getElementById('editItemModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                    loadInventory();
                }
            },
            error: function (xhr) {
                alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Unknown'));
            }
        });
    });
};

function loadInventory() {
    $.ajax({
        url: '../backend/products',
        type: 'GET',
        headers: { 'Authorization': 'Bearer ' + Auth.getToken() },
        success: function (response) {
            if (response.success) {
                let products = response.data;
                
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

                    let actions = '';
                    if (Auth.isAdmin()) {
                        actions = `
                            <button class="btn btn-sm btn-primary edit-btn" data-id="${product.id}" data-product='${JSON.stringify(product).replace(/'/g, "&#39;")}'>
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
                            <td>${product.name}</td>
                            <td>${product.category_id}</td>
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
                    $('#editItemName').val(product.name);
                    $('#editItemCategory').val(product.category_id);
                    $('#editItemQuantity').val(product.quantity);
                    $('#editItemPrice').val(product.price);
                    
                    // Open modal using Bootstrap 5 method
                    const modalElement = document.getElementById('editItemModal');
                    if (modalElement) {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                    }
                });

                $('.delete-btn').click(function () {
                    if (confirm('Are you sure you want to delete this item?')) {
                        const id = $(this).data('id');
                        $.ajax({
                            url: '../backend/products/' + id,
                            type: 'DELETE',
                            headers: { 'Authorization': 'Bearer ' + Auth.getToken() },
                            success: function (response) {
                                if (response.success) loadInventory();
                            },
                            error: function (xhr) {
                                alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'Unknown'));
                            }
                        });
                    }
                });
            }
        },
        error: function (xhr) {
            if (xhr.status === 401) {
                Auth.logout();
            }
        }
    });
}

function loadCategories() {
    $.ajax({
        url: '../backend/categories',
        type: 'GET',
        headers: { 'Authorization': 'Bearer ' + Auth.getToken() },
        success: function (response) {
            if (response.success) {
                const categories = response.data;
                const options = '<option value="">Select category</option>' +
                    categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
                $('#itemCategory').html(options);
                $('#editItemCategory').html(options);
            }
        }
    });
}
