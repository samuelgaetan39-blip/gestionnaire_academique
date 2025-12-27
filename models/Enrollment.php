<?php
class Enrollment {
    private $conn;
    private $table_name = "enrollments";

    public $id;
    public $student_id;
    public $course_id;
    public $enrollment_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT e.*, 
                         s.matricule, s.first_name as student_first_name, s.last_name as student_last_name,
                         c.code, c.name as course_name 
                  FROM " . $this->table_name . " e 
                  INNER JOIN students s ON e.student_id = s.id 
                  INNER JOIN courses c ON e.course_id = c.id 
                  ORDER BY e.enrollment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function search($search_term) {
    $query = "SELECT e.*, 
                     s.matricule, s.first_name as student_first_name, s.last_name as student_last_name,
                     c.code, c.name as course_name 
              FROM " . $this->table_name . " e 
              INNER JOIN students s ON e.student_id = s.id 
              INNER JOIN courses c ON e.course_id = c.id 
              WHERE s.first_name LIKE :search 
                 OR s.last_name LIKE :search 
                 OR s.matricule LIKE :search 
                 OR c.name LIKE :search 
                 OR c.code LIKE :search 
                 OR e.enrollment_date LIKE :search 
              ORDER BY e.enrollment_date DESC";
    
    $stmt = $this->conn->prepare($query);
    $search_term = "%{$search_term}%";
    $stmt->bindParam(":search", $search_term);
    $stmt->execute();
    
    return $stmt;
}
    public function getByStudent($student_id) {
        $query = "SELECT e.*, c.code, c.name, c.credits 
                  FROM " . $this->table_name . " e 
                  INNER JOIN courses c ON e.course_id = c.id 
                  WHERE e.student_id = :student_id 
                  ORDER BY c.code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        return $stmt;
    }

    public function getByCourse($course_id) {
        $query = "SELECT e.*, s.matricule, s.first_name, s.last_name, s.email 
                  FROM " . $this->table_name . " e 
                  INNER JOIN students s ON e.student_id = s.id 
                  WHERE e.course_id = :course_id 
                  ORDER BY s.last_name, s.first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (student_id, course_id, enrollment_date) 
                  VALUES (:student_id, :course_id, :enrollment_date)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":enrollment_date", $this->enrollment_date);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function checkExists($student_id, $course_id) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE student_id = :student_id AND course_id = :course_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>