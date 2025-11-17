<?php
require_once __DIR__ . '/../dao/OrderDao.php';

class OrderService {
    private $dao;

    public function __construct() {
        $this->dao = new OrderDao();
    }

    public function getAll() {
        try {
            return $this->dao->getAll();
        } catch (Exception $e) {
            throw new Exception("Error fetching orders: " . $e->getMessage());
        }
    }

    public function getById($id) {
        if (empty($id)) {
            throw new Exception("Order ID is required");
        }
        try {
            $order = $this->dao->getById($id);
            if (!$order) {
                throw new Exception("Order not found");
            }
            return $order;
        } catch (Exception $e) {
            throw new Exception("Error fetching order: " . $e->getMessage());
        }
    }

    public function create($data) {
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            throw new Exception("User ID is required");
        }
        if (!isset($data['status']) || trim($data['status']) === '') {
            throw new Exception("Status is required");
        }

        // order_date can be optional - default to current date if not provided
        if (!isset($data['order_date'])) {
            $data['order_date'] = date('Y-m-d');
        }

        // Validate date format if provided
        if (isset($data['order_date'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['order_date']);
            if (!$date || $date->format('Y-m-d') !== $data['order_date']) {
                throw new Exception("Invalid date format. Expected YYYY-MM-DD");
            }
        }

        // Validate status
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array(strtolower($data['status']), $validStatuses)) {
            throw new Exception("Invalid status. Must be one of: " . implode(', ', $validStatuses));
        }

        // Validate total_amount if provided
        if (isset($data['total_amount']) && $data['total_amount'] < 0) {
            throw new Exception("Total amount must be a non-negative number");
        }

        try {
            $lastId = $this->dao->insert($data);
            if ($lastId) {
                return $this->dao->getById($lastId);
            }
            throw new Exception("Failed to create order");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        if (empty($id)) {
            throw new Exception("Order ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Order not found");
        }

        unset($data['order_id']);

        if (isset($data['order_date'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['order_date']);
            if (!$date || $date->format('Y-m-d') !== $data['order_date']) {
                throw new Exception("Invalid date format. Expected YYYY-MM-DD");
            }
        }

        if (isset($data['status'])) {
            $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
            if (!in_array(strtolower($data['status']), $validStatuses)) {
                throw new Exception("Invalid status. Must be one of: " . implode(', ', $validStatuses));
            }
        }

        if (isset($data['total_amount']) && $data['total_amount'] < 0) {
            throw new Exception("Total amount must be a non-negative number");
        }

        try {
            $result = $this->dao->update($id, $data);
            if ($result) {
                return $this->dao->getById($id);
            }
            throw new Exception("Failed to update order");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function delete($id) {
        if (empty($id)) {
            throw new Exception("Order ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Order not found");
        }

        try {
            return $this->dao->delete($id);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'foreign key') !== false) {
                throw new Exception("Cannot delete order: it has associated order items");
            }
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}
?>

