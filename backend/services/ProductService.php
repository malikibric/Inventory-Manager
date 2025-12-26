<?php
require_once __DIR__ . '/../dao/ProductDao.php';

class ProductService {
    private $dao;

    public function __construct() {
        $this->dao = new ProductDao();
    }

    public function getAll() {
        try {
            return $this->dao->getAll();
        } catch (Exception $e) {
            throw new Exception("Error fetching products: " . $e->getMessage());
        }
    }

    public function getById($id) {
        if (empty($id)) {
            throw new Exception("Product ID is required");
        }
        try {
            $product = $this->dao->getById($id);
            if (!$product) {
                throw new Exception("Product not found");
            }
            return $product;
        } catch (Exception $e) {
            throw new Exception("Error fetching product: " . $e->getMessage());
        }
    }

    public function create($data) {
        if (!isset($data['name']) || trim($data['name']) === '') {
            throw new Exception("Product name is required");
        }
        if (!isset($data['price']) || $data['price'] < 0) {
            throw new Exception("Valid price is required");
        }
        if (!isset($data['quantity']) || $data['quantity'] < 0) {
            throw new Exception("Valid quantity is required");
        }
        if (!isset($data['category_id']) || empty($data['category_id'])) {
            throw new Exception("Category ID is required");
        }
        if (!isset($data['supplier_id']) || empty($data['supplier_id'])) {
            throw new Exception("Supplier ID is required");
        }

        // SKU is optional, but if provided, check for duplicates
        if (isset($data['sku']) && trim($data['sku']) !== '') {
            $existingSku = $this->dao->getBySku($data['sku']);
            if ($existingSku) {
                throw new Exception("Product with this SKU already exists");
            }
        }

        if (isset($data['quantity']) && isset($data['price'])) {
            $data['total_value'] = $data['quantity'] * $data['price'];
        }

        try {
            $lastId = $this->dao->insert($data);
            if ($lastId) {
                return $this->dao->getById($lastId);
            }
            throw new Exception("Failed to create product");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        if (empty($id)) {
            throw new Exception("Product ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Product not found");
        }

        unset($data['product_id']);

        if (isset($data['sku']) && $data['sku'] !== $existing['sku']) {
            $existingSku = $this->dao->getBySku($data['sku']);
            if ($existingSku) {
                throw new Exception("Product with this SKU already exists");
            }
        }

        if (isset($data['quantity']) && $data['quantity'] < 0) {
            throw new Exception("Product quantity must be a non-negative number");
        }
        if (isset($data['price']) && $data['price'] < 0) {
            throw new Exception("Product price must be a non-negative number");
        }

        $quantity = isset($data['quantity']) ? $data['quantity'] : $existing['quantity'];
        $price = isset($data['price']) ? $data['price'] : $existing['price'];
        $data['total_value'] = $quantity * $price;

        try {
            $result = $this->dao->update($id, $data);
            if ($result) {
                return $this->dao->getById($id);
            }
            throw new Exception("Failed to update product");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function delete($id) {
        if (empty($id)) {
            throw new Exception("Product ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Product not found");
        }

        try {
            return $this->dao->delete($id);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'foreign key') !== false) {
                throw new Exception("Cannot delete product: it is being used in orders");
            }
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}
?>


