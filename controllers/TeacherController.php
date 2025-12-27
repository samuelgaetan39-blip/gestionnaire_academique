<?php
require_once '../../config/database.php';
require_once '../../models/Teacher.php';
require_once '../../controllers/AuthController.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$auth->requireRole('admin');

$teacher = new Teacher($db);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $query = "INSERT INTO users (email, password, role) VALUES (:email, :password, 'teacher')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->bindParam(':password', $password);
    
    if ($stmt->execute()) {
        $user_id = $db->lastInsertId();
        
        $teacher->user_id = $user_id;
        $teacher->first_name = $_POST['first_name'];
        $teacher->last_name = $_POST['last_name'];
        $teacher->email = $_POST['email'];
        $teacher->specialization = $_POST['specialization'];
        $teacher->phone = $_POST['phone'];
        
        if ($teacher->create()) {
            header("Location: ../../views/admin/teachers.php?success=created");
            exit();
        }
    }
    header("Location: ../../views/admin/teachers.php?error=create_failed");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $teacher->id = $_POST['id'];
    $teacher->first_name = $_POST['first_name'];
    $teacher->last_name = $_POST['last_name'];
    $teacher->email = $_POST['email'];
    $teacher->specialization = $_POST['specialization'];
    $teacher->phone = $_POST['phone'];
    
    if ($teacher->update()) {
        header("Location: ../../views/admin/teachers.php?success=updated");
        exit();
    }
    header("Location: ../../views/admin/teachers.php?error=update_failed");
    exit();
}


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $teacher->id = $_GET['id'];
    if ($teacher->delete()) {
        header("Location: ../../views/admin/teachers.php?success=deleted");
        exit();
    }
    header("Location: ../../views/admin/teachers.php?error=delete_failed");
    exit();
}
?>