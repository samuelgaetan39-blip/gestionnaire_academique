<?php
class Course {
    private $conn;
    private $table_name = "courses";

    public $id;
    public $code;
    public $name;
    public $description;
    public $credits;
    public $teacher_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT c.*, t.first_name, t.last_name 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN teachers t ON c.teacher_id = t.id 
                  ORDER BY c.code";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function search($search_term) {
        $query = "SELECT c.*, t.first_name, t.last_name 
                  FROM " . $this->table_name . " c 
                  LEFT JOIN teachers t ON c.teacher_id = t.id 
                  WHERE c.code LIKE :search 
                  OR c.name LIKE :search 
                  ORDER BY c.code";
        
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

    public function getByTeacher($teacher_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE teacher_id = :teacher_id ORDER BY code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":teacher_id", $teacher_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (code, name, description, credits, teacher_id) 
                  VALUES (:code, :name, :description, :credits, :teacher_id)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":credits", $this->credits);
        $stmt->bindParam(":teacher_id", $this->teacher_id);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET code = :code, 
                      name = :name, 
                      description = :description, 
                      credits = :credits, 
                      teacher_id = :teacher_id 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":credits", $this->credits);
        $stmt->bindParam(":teacher_id", $this->teacher_id);
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