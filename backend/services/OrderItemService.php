<?php
require_once __DIR__ . '/../dao/OrderItemDao.php';

class OrderItemService {
    private $dao;

    public function __construct() {
        $this->dao = new OrderItemDao();
    }

    public function getAll() {
        try {
            return $this->dao->getAll();
        } catch (Exception $e) {
            throw new Exception("Error fetching order items: " . $e->getMessage());
        }
    }

    public function getById($id) {
        if (empty($id)) {
            throw new Exception("Order item ID is required");
        }
        try {
            $orderItem = $this->dao->getById($id);
            if (!$orderItem) {
                throw new Exception("Order item not found");
            }
            return $orderItem;
        } catch (Exception $e) {
            throw new Exception("Error fetching order item: " . $e->getMessage());
        }
    }

    public function create($data) {
        if (!isset($data['order_id']) || empty($data['order_id'])) {
            throw new Exception("Order ID is required");
        }
        if (!isset($data['product_id']) || empty($data['product_id'])) {
            throw new Exception("Product ID is required");
        }
        if (!isset($data['quantity']) || $data['quantity'] <= 0) {
            throw new Exception("Valid quantity is required");
        }
        if (!isset($data['price']) || $data['price'] < 0) {
            throw new Exception("Valid price is required");
        }

        try {
            $lastId = $this->dao->insert($data);
            if ($lastId) {
                return $this->dao->getById($lastId);
            }
            throw new Exception("Failed to create order item");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        if (empty($id)) {
            throw new Exception("Order item ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Order item not found");
        }

        unset($data['order_item_id']);

        if (isset($data['quantity']) && $data['quantity'] <= 0) {
            throw new Exception("Quantity must be a positive number");
        }

        if (isset($data['price']) && $data['price'] < 0) {
            throw new Exception("Price must be a non-negative number");
        }

        try {
            $result = $this->dao->update($id, $data);
            if ($result) {
                return $this->dao->getById($id);
            }
            throw new Exception("Failed to update order item");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function delete($id) {
        if (empty($id)) {
            throw new Exception("Order item ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Order item not found");
        }

        try {
            return $this->dao->delete($id);
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}
?>


