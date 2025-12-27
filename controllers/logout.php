<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once 'AuthController.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$auth->logout();
?>