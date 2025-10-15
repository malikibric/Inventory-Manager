(function ($) {
    "use strict";

    // Check if user is logged in
    const currentUser = localStorage.getItem('currentUser');
    if (!currentUser) {
        window.location.href = 'views/login.html';
        return;
    }

    // Display user name and role
    const userData = JSON.parse(currentUser);
    $('#userNameDisplay').text(userData.firstName || 'User');
    $('#welcomeUserName').text(userData.firstName || 'User');
    
    // Display role badge
    const userRole = userData.role || 'user';
    const roleBadgeClass = userRole === 'admin' ? 'bg-danger' : 'bg-primary';
    const roleBadgeText = userRole === 'admin' ? 'ADMIN' : 'USER';
    $('#userRoleBadge').html(`<span class="badge ${roleBadgeClass} ms-2">${roleBadgeText}</span>`);

    // Logout functionality
    $('#logoutBtn').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
            localStorage.removeItem('currentUser');
            window.location.href = '../index.html';
        }
    });

    // Initialize inventory from localStorage or create empty array
    let inventory = JSON.parse(localStorage.getItem('inventory')) || [];

    // Sample data if inventory is empty
    if (inventory.length === 0) {
        inventory = [
            { id: 1, name: 'Dell XPS 15 Laptop', category: 'Electronics', quantity: 15, price: 1299.99 },
            { id: 2, name: 'Office Chair', category: 'Furniture', quantity: 8, price: 249.99 },
            { id: 3, name: 'Wireless Mouse', category: 'Electronics', quantity: 45, price: 29.99 },
            { id: 4, name: 'Work Jacket', category: 'Clothing', quantity: 3, price: 89.99 },
            { id: 5, name: 'Tool Set', category: 'Tools', quantity: 12, price: 159.99 }
        ];
        saveInventory();
    }

    // Save inventory to localStorage
    function saveInventory() {
        localStorage.setItem('inventory', JSON.stringify(inventory));
    }

    // Get next ID
    function getNextId() {
        if (inventory.length === 0) return 1;
        return Math.max(...inventory.map(item => item.id)) + 1;
    }

    // Update statistics
    function updateStats() {
        const totalItems = inventory.length;
        const inStockItems = inventory.filter(item => item.quantity > 10).length;
        const lowStockItems = inventory.filter(item => item.quantity <= 10 && item.quantity > 0).length;
        const totalValue = inventory.reduce((sum, item) => sum + (item.quantity * item.price), 0);

        $('#totalItems').text(totalItems);
        $('#inStockItems').text(inStockItems);
        $('#lowStockItems').text(lowStockItems);
        $('#totalValue').text('$' + totalValue.toFixed(2));
    }

    // Render inventory table
    function renderInventory() {
        const tbody = $('#inventoryTableBody');
        tbody.empty();

        if (inventory.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No items in inventory. Add your first item!</p>
                    </td>
                </tr>
            `);
            return;
        }

        inventory.forEach(item => {
            const total = (item.quantity * item.price).toFixed(2);
            const statusClass = item.quantity > 10 ? 'in-stock' : item.quantity > 0 ? 'low-stock' : 'text-danger';
            const statusText = item.quantity > 10 ? 'In Stock' : item.quantity > 0 ? 'Low Stock' : 'Out of Stock';

            // Admin can see price and delete, User can only view and edit quantity
            const priceColumn = userRole === 'admin' ? `<td>$${item.price.toFixed(2)}</td><td>$${total}</td>` : '';
            const deleteButton = userRole === 'admin' ? `
                <button class="btn btn-sm btn-danger delete-btn" data-id="${item.id}">
                    <i class="fas fa-trash"></i>
                </button>
            ` : '';
            
            const row = `
                <tr>
                    <td>${item.id}</td>
                    <td>${item.name}</td>
                    <td><span class="badge bg-secondary">${item.category}</span></td>
                    <td>${item.quantity}</td>
                    ${priceColumn}
                    <td><span class="${statusClass}">${statusText}</span></td>
                    <td class="table-actions">
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${item.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${deleteButton}
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        updateStats();
    }

    // Add new item (Admin only)
    $('#saveItemBtn').on('click', function() {
        // Check if user is admin
        if (userRole !== 'admin') {
            alert('Only administrators can add new items!');
            return;
        }
        
        const name = $('#itemName').val().trim();
        const category = $('#itemCategory').val();
        const quantity = parseInt($('#itemQuantity').val());
        const price = parseFloat($('#itemPrice').val());

        if (!name || !category || isNaN(quantity) || isNaN(price)) {
            alert('Please fill in all fields!');
            return;
        }

        const newItem = {
            id: getNextId(),
            name: name,
            category: category,
            quantity: quantity,
            price: price
        };

        inventory.push(newItem);
        saveInventory();
        renderInventory();

        // Reset form and close modal
        $('#addItemForm')[0].reset();
        $('#addItemModal').modal('hide');

        alert('Item added successfully!');
    });
    
    // Hide Add Item button for non-admin users
    if (userRole !== 'admin') {
        const addBtn = $('#addItemBtn');
        if (addBtn.length) {
            addBtn.hide();
            addBtn.after('<p class="text-muted mt-2"><i class="fas fa-info-circle me-2"></i>Only admins can add new items</p>');
        }
    }

    // Edit item - populate modal
    $(document).on('click', '.edit-btn', function() {
        const id = parseInt($(this).data('id'));
        const item = inventory.find(i => i.id === id);

        if (item) {
            $('#editItemId').val(item.id);
            $('#editItemName').val(item.name);
            $('#editItemCategory').val(item.category);
            $('#editItemQuantity').val(item.quantity);
            $('#editItemPrice').val(item.price);

            $('#editItemModal').modal('show');
        }
    });

    // Update item
    $('#updateItemBtn').on('click', function() {
        const id = parseInt($('#editItemId').val());
        const name = $('#editItemName').val().trim();
        const category = $('#editItemCategory').val();
        const quantity = parseInt($('#editItemQuantity').val());
        const price = parseFloat($('#editItemPrice').val());

        if (!name || !category || isNaN(quantity) || isNaN(price)) {
            alert('Please fill in all fields!');
            return;
        }

        const itemIndex = inventory.findIndex(i => i.id === id);
        if (itemIndex !== -1) {
            inventory[itemIndex] = {
                id: id,
                name: name,
                category: category,
                quantity: quantity,
                price: price
            };

            saveInventory();
            renderInventory();

            $('#editItemModal').modal('hide');
            alert('Item updated successfully!');
        }
    });

    // Delete item
    $(document).on('click', '.delete-btn', function() {
        const id = parseInt($(this).data('id'));
        const item = inventory.find(i => i.id === id);

        if (item && confirm(`Are you sure you want to delete "${item.name}"?`)) {
            inventory = inventory.filter(i => i.id !== id);
            saveInventory();
            renderInventory();
            alert('Item deleted successfully!');
        }
    });

    // Initial render
    renderInventory();

})(jQuery);
