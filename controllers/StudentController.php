<?php
require_once '../config/database.php';
require_once '../models/Student.php';
require_once '../controllers/AuthController.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$auth->requireRole('admin');

$student = new Student($db);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $query = "INSERT INTO users (email, password, role) VALUES (:email, :password, 'student')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->bindParam(':password', $password);
    
    if ($stmt->execute()) {
        $user_id = $db->lastInsertId();
        
        $student->user_id = $user_id;
        $student->matricule = $_POST['matricule'];
        $student->first_name = $_POST['first_name'];
        $student->last_name = $_POST['last_name'];
        $student->email = $_POST['email'];
        $student->date_of_birth = $_POST['date_of_birth'];
        $student->address = $_POST['address'];
        $student->phone = $_POST['phone'];
        
        if ($student->create()) {
            header("Location: ../views/admin/students.php?success=created");
            exit();
        }
    }
    header("Location: ../views/admin/students.php?error=create_failed");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $student->id = $_POST['id'];
    $student->matricule = $_POST['matricule'];
    $student->first_name = $_POST['first_name'];
    $student->last_name = $_POST['last_name'];
    $student->email = $_POST['email'];
    $student->date_of_birth = $_POST['date_of_birth'];
    $student->address = $_POST['address'];
    $student->phone = $_POST['phone'];
    
    if ($student->update()) {
        header("Location: ../views/admin/students.php?success=updated");
        exit();
    }
    header("Location: ../views/admin/students.php?error=update_failed");
    exit();
}


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $student->id = $_GET['id'];
    if ($student->delete()) {
        header("Location: ../views/admin/students.php?success=deleted");
        exit();
    }
    header("Location: ../views/admin/students.php?error=delete_failed");
    exit();
}
?>