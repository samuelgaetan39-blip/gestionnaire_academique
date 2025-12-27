<?php
require_once '../../config/database.php';
require_once '../../models/Enrollment.php';
require_once '../../controllers/AuthController.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$auth->requireRole('admin');

$enrollment = new Enrollment($db);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    
    if ($enrollment->checkExists($_POST['student_id'], $_POST['course_id'])) {
        header("Location: ../../views/admin/enrollments.php?error=already_enrolled");
        exit();
    }
    
    $enrollment->student_id = $_POST['student_id'];
    $enrollment->course_id = $_POST['course_id'];
    $enrollment->enrollment_date = $_POST['enrollment_date'];
    
    if ($enrollment->create()) {
        header("Location: ../../views/admin/enrollments.php?success=created");
        exit();
    }
    header("Location: ../../views/admin/enrollments.php?error=create_failed");
    exit();
}


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $enrollment->id = $_GET['id'];
    if ($enrollment->delete()) {
        header("Location: ../../views/admin/enrollments.php?success=deleted");
        exit();
    }
    header("Location: ../../views/admin/enrollments.php?error=delete_failed");
    exit();
}
?>