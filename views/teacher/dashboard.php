<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';
require_once '../../controllers/AuthController.php';
require_once '../../models/Course.php';
require_once '../../models/Enrollment.php';
require_once '../../models/Student.php';
require_once '../../models/Grade.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$auth->requireRole('teacher');

$course = new Course($db);
$enrollment = new Enrollment($db);
$student = new Student($db);
$grade = new Grade($db);

$teacher_id = $_SESSION['teacher_id'];
$courses = $course->getByTeacher($teacher_id);

$selected_course_id = $_GET['course_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace enseignant | Gestionnaire Académique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="container mt-4">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Tableau de bord
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Espace Enseignant</h1>
                </div>

                <!-- My Courses -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-book me-2"></i>Mes Cours</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php while ($c = $courses->fetch(PDO::FETCH_ASSOC)): 
                                $enroll_stmt = $enrollment->getByCourse($c['id']);
                                $student_count = $enroll_stmt->rowCount();
                            ?>
                                <div class="col-md-4">
                                    <div class="card h-100 <?= $selected_course_id == $c['id'] ? 'border-success' : '' ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($c['code']) ?></h5>
                                            <p class="card-text"><?= htmlspecialchars($c['name']) ?></p>
                                            <p class="text-muted small mb-2">
                                                <i class="bi bi-people me-1"></i><?= $student_count ?> étudiant·e·s inscrit·e·s
                                            </p>
                                            <a href="?course_id=<?= $c['id'] ?>" class="btn btn-sm btn-success w-100">
                                                Gérer les notes
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Course Students and Grades -->
                <?php if ($selected_course_id): 
                    $course_data = $course->getById();
                    $course->id = $selected_course_id;
                    $course_info = $course->getById();
                    $enrollments_stmt = $enrollment->getByCourse($selected_course_id);
                ?>
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-journal-text me-2"></i>
                                <?= htmlspecialchars($course_info['code']) ?> - Gestion des notes
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Matricule</th>
                                            <th>Nom complet</th>
                                            <th>E-mail</th>
                                            <th>Moyenne</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($enroll = $enrollments_stmt->fetch(PDO::FETCH_ASSOC)): 
                                            $avg = $grade->calculateAverage($enroll['id']);
                                            $avg_display = $avg > 0 ? number_format($avg, 2) : 'N/A';
                                        ?>
                                            <tr>
                                                <td><span class="badge bg-primary"><?= htmlspecialchars($enroll['matricule']) ?></span></td>
                                                <td><?= htmlspecialchars($enroll['first_name'] . ' ' . $enroll['last_name']) ?></td>
                                                <td><?= htmlspecialchars($enroll['email']) ?></td>
                                                <td><strong><?= $avg_display ?></strong></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-success" 
                                                            onclick="openGradeModal(<?= $enroll['id'] ?>, '<?= htmlspecialchars($enroll['first_name'] . ' ' . $enroll['last_name']) ?>')">
                                                        <i class="bi bi-plus-circle me-1"></i>
                                                        Ajouter note
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Add Grade Modal -->
    <div class="modal fade" id="addGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Ajouter une note</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="../../controllers/GradeController.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="enrollment_id" id="enrollment_id">
                        <p class="mb-3">Étudiant: <strong id="student_name"></strong></p>
                        <div class="mb-3">
                            <label class="form-label">Type d'examen</label>
                            <select class="form-select" name="exam_type" required>
                                <option value="">Sélectionner le type</option>
                                <option value="Intra">Intra</option>
                                <option value="Final">Final</option>
                                <option value="Reprise">Reprise</option>
                                <option value="Contrôle continu">Contrôle continu</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note (sur 100)</label>
                            <input type="number" class="form-control" name="grade" min="0" max="100" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openGradeModal(enrollmentId, studentName) {
            document.getElementById('enrollment_id').value = enrollmentId;
            document.getElementById('student_name').textContent = studentName;
            var modal = new bootstrap.Modal(document.getElementById('addGradeModal'));
            modal.show();
        }
    </script>
</body>
</html>