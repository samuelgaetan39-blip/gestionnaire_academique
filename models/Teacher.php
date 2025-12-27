<?php
class Teacher {
    private $conn;
    private $table_name = "teachers";

    public $id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $email;
    public $specialization;
    public $phone;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " 
              WHERE email NOT LIKE 'admin.%ga@gmail.com' 
              ORDER BY last_name, first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function search($search_term) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE first_name LIKE :search 
                  OR last_name LIKE :search 
                  OR email LIKE :search 
                  OR specialization LIKE :search 
                  AND email NOT LIKE 'admin.%ga@gmail.com'
                  ORDER BY last_name, first_name";
        
        $stmt = $this->conn->prepare($query);
        $search_term = "%{$search_term}%";
        $stmt->bindParam(":search", $search_term);
        $stmt->execute();
        return $stmt;
    }

    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, first_name, last_name, email, specialization, phone) 
                  VALUES (:user_id, :first_name, :last_name, :email, :specialization, :phone)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":specialization", $this->specialization);
        $stmt->bindParam(":phone", $this->phone);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name = :first_name, 
                      last_name = :last_name, 
                      email = :email, 
                      specialization = :specialization, 
                      phone = :phone 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":specialization", $this->specialization);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
?>