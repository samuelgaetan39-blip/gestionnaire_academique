<?php
require_once '../config/database.php';
require_once '../models/Course.php';
require_once '../controllers/AuthController.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$auth->requireRole('admin');

$course = new Course($db);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $course->code = $_POST['code'];
    $course->name = $_POST['name'];
    $course->description = $_POST['description'];
    $course->credits = $_POST['credits'];
    $course->teacher_id = $_POST['teacher_id'];
    
    if ($course->create()) {
        header("Location: ../views/admin/courses.php?success=created");
        exit();
    }
    header("Location: ../views/admin/courses.php?error=create_failed");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $course->id = $_POST['id'];
    $course->code = $_POST['code'];
    $course->name = $_POST['name'];
    $course->description = $_POST['description'];
    $course->credits = $_POST['credits'];
    $course->teacher_id = $_POST['teacher_id'];
    
    if ($course->update()) {
        header("Location: ../views/admin/courses.php?success=updated");
        exit();
    }
    header("Location: ../views/admin/courses.php?error=update_failed");
    exit();
}


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $course->id = $_GET['id'];
    if ($course->delete()) {
        header("Location: ../views/admin/courses.php?success=deleted");
        exit();
    }
    header("Location: ../views/admin/courses.php?error=delete_failed");
    exit();
}
?>