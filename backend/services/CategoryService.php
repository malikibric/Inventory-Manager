<?php
require_once __DIR__ . '/../dao/CategoryDao.php';

class CategoryService {
    private $dao;

    public function __construct() {
        $this->dao = new CategoryDao();
    }

    public function getAll() {
        try {
            return $this->dao->getAll();
        } catch (Exception $e) {
            throw new Exception("Error fetching categories: " . $e->getMessage());
        }
    }

    public function getById($id) {
        if (empty($id)) {
            throw new Exception("Category ID is required");
        }
        try {
            $category = $this->dao->getById($id);
            if (!$category) {
                throw new Exception("Category not found");
            }
            return $category;
        } catch (Exception $e) {
            throw new Exception("Error fetching category: " . $e->getMessage());
        }
    }

    public function create($data) {
        if (!isset($data['name']) || trim($data['name']) === '') {
            throw new Exception("Category name is required");
        }

        try {
            $lastId = $this->dao->insert($data);
            if ($lastId) {
                return $this->dao->getById($lastId);
            }
            throw new Exception("Failed to create category");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        if (empty($id)) {
            throw new Exception("Category ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Category not found");
        }

        unset($data['category_id']);

        try {
            $result = $this->dao->update($id, $data);
            if ($result) {
                return $this->dao->getById($id);
            }
            throw new Exception("Failed to update category");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function delete($id) {
        if (empty($id)) {
            throw new Exception("Category ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Category not found");
        }

        try {
            return $this->dao->delete($id);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'foreign key') !== false) {
                throw new Exception("Cannot delete category: it is being used by products");
            }
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}
?>

