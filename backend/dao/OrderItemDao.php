<?php
require_once 'BaseDao.php';

class OrderItemDao extends BaseDao {
    public function __construct() {
        parent::__construct("order_items", "order_item_id");
    }

    public function getByOrder($order_id) {
        $stmt = $this->connection->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByProduct($product_id) {
        $stmt = $this->connection->prepare("SELECT * FROM order_items WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
