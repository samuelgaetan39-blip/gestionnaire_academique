<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class AuthController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT u.*, 
                         s.id as student_id, s.first_name as s_first_name, s.last_name as s_last_name, s.matricule,
                         t.id as teacher_id, t.first_name as t_first_name, t.last_name as t_last_name 
                  FROM users u 
                  LEFT JOIN students s ON u.id = s.user_id 
                  LEFT JOIN teachers t ON u.id = t.user_id 
                  WHERE u.email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = $row['role'];

                if ($row['role'] == 'student') {
                    $_SESSION['student_id'] = $row['student_id'];
                    $_SESSION['first_name'] = $row['s_first_name'];
                    $_SESSION['last_name'] = $row['s_last_name'];
                    $_SESSION['matricule'] = $row['matricule'];
                } elseif ($row['role'] == 'teacher') {
                    $_SESSION['teacher_id'] = $row['teacher_id'];
                    $_SESSION['first_name'] = $row['t_first_name'];
                    $_SESSION['last_name'] = $row['t_last_name'];
                } else {
                    $_SESSION['first_name'] = 'Administrateur |';
                    $_SESSION['last_name'] = 'Administratrice';
                }

                return true;
            }
        }
        
        return false;
    }

    public function logout() {
        session_destroy();
        header("Location: ../index.php");
        exit();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getRole() {
        return $_SESSION['role'] ?? null;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: index.php");
            exit();
        }
    }

    public function requireRole($role) {
        $this->requireLogin();
        if ($this->getRole() != $role) {
            header("Location: index.php");
            exit();
        }
    }
}
?>