<?php
class Grade {
    private $conn;
    private $table_name = "grades";

    public $id;
    public $enrollment_id;
    public $grade;
    public $exam_type;
    public $date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT g.*, 
                         e.student_id, e.course_id,
                         s.first_name, s.last_name, s.matricule,
                         c.code, c.name as course_name 
                  FROM " . $this->table_name . " g 
                  INNER JOIN enrollments e ON g.enrollment_id = e.id 
                  INNER JOIN students s ON e.student_id = s.id 
                  INNER JOIN courses c ON e.course_id = c.id 
                  ORDER BY g.date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByEnrollment($enrollment_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE enrollment_id = :enrollment_id 
                  ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":enrollment_id", $enrollment_id);
        $stmt->execute();
        return $stmt;
    }

    public function getByStudent($student_id) {
        $query = "SELECT g.*, c.code, c.name as course_name 
                  FROM " . $this->table_name . " g 
                  INNER JOIN enrollments e ON g.enrollment_id = e.id 
                  INNER JOIN courses c ON e.course_id = c.id 
                  WHERE e.student_id = :student_id 
                  ORDER BY g.date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        return $stmt;
    }

    public function calculateAverage($enrollment_id) {
        $query = "SELECT AVG(grade) as average 
                  FROM " . $this->table_name . " 
                  WHERE enrollment_id = :enrollment_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":enrollment_id", $enrollment_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['average'] ?? 0;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (enrollment_id, grade, exam_type, date) 
                  VALUES (:enrollment_id, :grade, :exam_type, :date)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":enrollment_id", $this->enrollment_id);
        $stmt->bindParam(":grade", $this->grade);
        $stmt->bindParam(":exam_type", $this->exam_type);
        $stmt->bindParam(":date", $this->date);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET grade = :grade, 
                      exam_type = :exam_type, 
                      date = :date 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":grade", $this->grade);
        $stmt->bindParam(":exam_type", $this->exam_type);
        $stmt->bindParam(":date", $this->date);
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