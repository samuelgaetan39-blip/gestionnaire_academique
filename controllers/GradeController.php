<?php
require_once '../../config/database.php';
require_once '../../models/Grade.php';
require_once '../../controllers/AuthController.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);


if (!in_array($auth->getRole(), ['teacher', 'admin'])) {
    header("Location: ../../index.php");
    exit();
}

$grade = new Grade($db);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $grade->enrollment_id = $_POST['enrollment_id'];
    $grade->grade = $_POST['grade'];
    $grade->exam_type = $_POST['exam_type'];
    $grade->date = $_POST['date'];
    
    if ($grade->create()) {
        $redirect = $auth->getRole() == 'admin' ? 'admin' : 'teacher';
        header("Location: ../../views/$redirect/grades.php?success=created");
        exit();
    }
    header("Location: ../../views/teacher/grades.php?error=create_failed");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $grade->id = $_POST['id'];
    $grade->grade = $_POST['grade'];
    $grade->exam_type = $_POST['exam_type'];
    $grade->date = $_POST['date'];
    
    if ($grade->update()) {
        $redirect = $auth->getRole() == 'admin' ? 'admin' : 'teacher';
        header("Location: ../../views/$redirect/grades.php?success=updated");
        exit();
    }
    header("Location: ../../views/teacher/grades.php?error=update_failed");
    exit();
}


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $grade->id = $_GET['id'];
    if ($grade->delete()) {
        $redirect = $auth->getRole() == 'admin' ? 'admin' : 'teacher';
        header("Location: ../../views/$redirect/grades.php?success=deleted");
        exit();
    }
    header("Location: ../../views/teacher/grades.php?error=delete_failed");
    exit();
}
?>