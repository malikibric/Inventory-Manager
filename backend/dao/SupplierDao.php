<?php
require_once 'BaseDao.php';

class SupplierDao extends BaseDao {
    public function __construct() {
        parent::__construct("suppliers");
    }
}
public function getSupplierCount() {
    $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM suppliers");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}
?>
