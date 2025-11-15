<?php
require_once __DIR__ . '/../dao/SupplierDao.php';

class SupplierService {
    private $dao;

    public function __construct() {
        $this->dao = new SupplierDao();
    }

    public function getAll() {
        try {
            $suppliers = $this->dao->getAll();
            // Map database columns to Swagger field names for API response
            foreach ($suppliers as &$supplier) {
                if (isset($supplier['email'])) {
                    $supplier['contact_email'] = $supplier['email'];
                    unset($supplier['email']);
                }
                if (isset($supplier['phone'])) {
                    $supplier['contact_phone'] = $supplier['phone'];
                    unset($supplier['phone']);
                }
            }
            return $suppliers;
        } catch (Exception $e) {
            throw new Exception("Error fetching suppliers: " . $e->getMessage());
        }
    }

    public function getById($id) {
        if (empty($id)) {
            throw new Exception("Supplier ID is required");
        }
        try {
            $supplier = $this->dao->getById($id);
            if (!$supplier) {
                throw new Exception("Supplier not found");
            }
            // Map database columns to Swagger field names for API response
            if (isset($supplier['email'])) {
                $supplier['contact_email'] = $supplier['email'];
                unset($supplier['email']);
            }
            if (isset($supplier['phone'])) {
                $supplier['contact_phone'] = $supplier['phone'];
                unset($supplier['phone']);
            }
            return $supplier;
        } catch (Exception $e) {
            throw new Exception("Error fetching supplier: " . $e->getMessage());
        }
    }

    public function create($data) {
        if (!isset($data['name']) || trim($data['name']) === '') {
            throw new Exception("Supplier name is required");
        }
        if (!isset($data['contact_name']) || trim($data['contact_name']) === '') {
            throw new Exception("Contact name is required");
        }
        if (!isset($data['contact_email']) || trim($data['contact_email']) === '') {
            throw new Exception("Email is required");
        }
        if (!isset($data['contact_phone']) || trim($data['contact_phone']) === '') {
            throw new Exception("Phone is required");
        }

        // Map Swagger field names to database column names
        if (isset($data['contact_email'])) {
            $data['email'] = $data['contact_email'];
            unset($data['contact_email']);
        }
        if (isset($data['contact_phone'])) {
            $data['phone'] = $data['contact_phone'];
            unset($data['contact_phone']);
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        try {
            $lastId = $this->dao->insert($data);
            if ($lastId) {
                $supplier = $this->dao->getById($lastId);
                // Map database columns to Swagger field names for API response
                if (isset($supplier['email'])) {
                    $supplier['contact_email'] = $supplier['email'];
                    unset($supplier['email']);
                }
                if (isset($supplier['phone'])) {
                    $supplier['contact_phone'] = $supplier['phone'];
                    unset($supplier['phone']);
                }
                return $supplier;
            }
            throw new Exception("Failed to create supplier");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        if (empty($id)) {
            throw new Exception("Supplier ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Supplier not found");
        }

        unset($data['supplier_id']);

        // Map Swagger field names to database column names
        if (isset($data['contact_email'])) {
            $data['email'] = $data['contact_email'];
            unset($data['contact_email']);
        }
        if (isset($data['contact_phone'])) {
            $data['phone'] = $data['contact_phone'];
            unset($data['contact_phone']);
        }

        if (isset($data['email']) && trim($data['email']) !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        try {
            $result = $this->dao->update($id, $data);
            if ($result) {
                $supplier = $this->dao->getById($id);
                // Map database columns to Swagger field names for API response
                if (isset($supplier['email'])) {
                    $supplier['contact_email'] = $supplier['email'];
                    unset($supplier['email']);
                }
                if (isset($supplier['phone'])) {
                    $supplier['contact_phone'] = $supplier['phone'];
                    unset($supplier['phone']);
                }
                return $supplier;
            }
            throw new Exception("Failed to update supplier");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function delete($id) {
        if (empty($id)) {
            throw new Exception("Supplier ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("Supplier not found");
        }

        try {
            return $this->dao->delete($id);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'foreign key') !== false) {
                throw new Exception("Cannot delete supplier: it is being used by products or orders");
            }
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}
?>


