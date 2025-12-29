<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';
require_once '../../controllers/AuthController.php';
require_once '../../models/Student.php';
require_once '../../models/Enrollment.php';
require_once '../../models/Grade.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$auth->requireRole('student');

$student = new Student($db);
$enrollment = new Enrollment($db);
$grade = new Grade($db);

$student_id = $_SESSION['student_id'];
$student->id = $student_id;
$student_data = $student->getById();

$enrollments = $enrollment->getByStudent($student_id);
$grades = $grade->getByStudent($student_id);


$total_courses = 0;
$total_credits = 0;
$grades_array = [];

$enrollments->execute();
while ($enroll = $enrollments->fetch(PDO::FETCH_ASSOC)) {
    $total_courses++;
    $total_credits += $enroll['credits'];
    
    $avg = $grade->calculateAverage($enroll['id']);
    if ($avg > 0) {
        $grades_array[] = $avg;
    }
}

$overall_average = count($grades_array) > 0 ? array_sum($grades_array) / count($grades_array) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace étudiant | Gestionnaire Académique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-4">
        <h1 class="mb-4">Mon espace étudiant</h1>

        <!-- Student Info Card -->
        <div class="card mb-4 bg-primary text-white">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h6 class="text-white-50">Matricule</h6>
                        <h5><?= htmlspecialchars($student_data['matricule']) ?></h5>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-white-50">Nom complet</h6>
                        <h5><?= htmlspecialchars($student_data['first_name'] . ' ' . $student_data['last_name']) ?></h5>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-white-50">E-mail</h6>
                        <h5><?= htmlspecialchars($student_data['email']) ?></h5>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-white-50">Téléphone</h6>
                        <h5><?= htmlspecialchars($student_data['phone']) ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Cours inscrits</h6>
                        <h2 class="mb-0"><?= $total_courses ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Moyenne générale</h6>
                        <h2 class="mb-0"><?= $overall_average > 0 ? number_format($overall_average, 2) : 'N/A' ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="card-title">Crédits totaux</h6>
                        <h2 class="mb-0"><?= $total_credits ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrolled Courses -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-book me-2"></i>Mes cours</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Nom du cours</th>
                                <th>Crédits</th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $enrollments = $enrollment->getByStudent($student_id);
                            while ($enroll = $enrollments->fetch(PDO::FETCH_ASSOC)): 
                                $avg = $grade->calculateAverage($enroll['id']);
                                $avg_display = $avg > 0 ? number_format($avg, 2) : 'N/A';
                            ?>
                                <tr>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($enroll['code']) ?></span></td>
                                    <td><?= htmlspecialchars($enroll['name']) ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= $enroll['credits'] ?> crédits</span></td>
                                    <td><strong><?= $avg_display ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Grades -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-award me-2"></i>Notes récentes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cours</th>
                                <th>Type d'examen</th>
                                <th>Note</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grades = $grade->getByStudent($student_id);
                            while ($g = $grades->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($g['code']) ?></td>
                                    <td><?= htmlspecialchars($g['exam_type']) ?></td>
                                    <td><span class="badge <?= $g['grade'] >= 50 ? 'bg-success' : 'bg-danger' ?>"><?= $g['grade'] ?>/100</span></td>
                                    <td><?= htmlspecialchars($g['date']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>