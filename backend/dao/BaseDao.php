<?php
require_once __DIR__ . '/../config/config.php';

class BaseDao {
   protected $table;
   protected $primaryKey;
   protected $connection;


   public function __construct($table, $primaryKey = null) {
       $this->table = $table;
       $this->primaryKey = $primaryKey ?: $table . '_id';
       $this->connection = Database::connect();
   }


   public function getAll() {
       $stmt = $this->connection->prepare("SELECT * FROM " . $this->table);
       $stmt->execute();
       return $stmt->fetchAll();
   }


   public function getById($id) {
       $stmt = $this->connection->prepare("SELECT * FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id");
       $stmt->bindParam(':id', $id);
       $stmt->execute();
       return $stmt->fetch();
   }


   public function insert($data) {
       if (isset($data[$this->primaryKey])) {
           unset($data[$this->primaryKey]);
       }
       
       foreach ($data as $key => $value) {
           if (strtolower($key) === strtolower($this->primaryKey)) {
               unset($data[$key]);
               continue;
           }
           if (preg_match('/_id$/', $key) && ($value === '' || $value === null)) {
               unset($data[$key]);
           }
       }
       
       if (empty($data)) {
           throw new Exception("No data provided for insert");
       }
       
       $columns = implode(", ", array_keys($data));
       $placeholders = ":" . implode(", :", array_keys($data));
       $sql = "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
       $stmt = $this->connection->prepare($sql);
       $result = $stmt->execute($data);
       if ($result) {
           return $this->connection->lastInsertId();
       }
       return false;
   }

   public function getLastInsertId() {
       return $this->connection->lastInsertId();
   }


   public function update($id, $data) {
       if (isset($data[$this->primaryKey])) {
           unset($data[$this->primaryKey]);
       }
       
       if (empty($data)) {
           throw new Exception("No data provided for update");
       }
       
       $fields = "";
       foreach ($data as $key => $value) {
           $fields .= "$key = :$key, ";
       }
       $fields = rtrim($fields, ", ");
       $sql = "UPDATE " . $this->table . " SET $fields WHERE " . $this->primaryKey . " = :id";
       $stmt = $this->connection->prepare($sql);
       $data['id'] = $id;
       return $stmt->execute($data);
   }


   public function delete($id) {
       $stmt = $this->connection->prepare("DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = :id");
       $stmt->bindParam(':id', $id);
       return $stmt->execute();
   }
}
?>
