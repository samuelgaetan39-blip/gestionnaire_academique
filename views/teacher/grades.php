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
    <title>Gestion des notes | Gestionnaire Académique</title>
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
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="grades.php">
                                <i class="bi bi-award me-2"></i>
                                Gestion des notes
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des Notes</h1>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        switch($_GET['success']) {
                            case 'created': echo 'Note ajoutée avec succès !'; break;
                            case 'updated': echo 'Note modifiée avec succès !'; break;
                            case 'deleted': echo 'Note supprimée avec succès !'; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Course Selection -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-book me-2"></i>Sélectionner un cours</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php 
                            $courses->execute();
                            while ($c = $courses->fetch(PDO::FETCH_ASSOC)): 
                                $enroll_stmt = $enrollment->getByCourse($c['id']);
                                $student_count = $enroll_stmt->rowCount();
                            ?>
                                <div class="col-md-4">
                                    <a href="?course_id=<?= $c['id'] ?>" class="text-decoration-none">
                                        <div class="card h-100 <?= $selected_course_id == $c['id'] ? 'border-success border-3' : '' ?>">
                                            <div class="card-body">
                                                <h5 class="card-title text-success"><?= htmlspecialchars($c['code']) ?></h5>
                                                <p class="card-text text-dark"><?= htmlspecialchars($c['name']) ?></p>
                                                <p class="text-muted small mb-0">
                                                    <i class="bi bi-people me-1"></i><?= $student_count ?> étudiants
                                                    <i class="bi bi-award ms-2 me-1"></i><?= $c['credits'] ?> crédits
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <?php if ($selected_course_id): 
                    $course->id = $selected_course_id;
                    $course_info = $course->getById();
                    $enrollments_stmt = $enrollment->getByCourse($selected_course_id);
                ?>
                    <!-- Students List with Grades -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                Étudiants inscrits - <?= htmlspecialchars($course_info['code']) ?>
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
                                            $avg_class = $avg >= 50 ? 'text-success' : ($avg > 0 ? 'text-danger' : '');
                                        ?>
                                            <tr>
                                                <td><span class="badge bg-primary"><?= htmlspecialchars($enroll['matricule']) ?></span></td>
                                                <td><?= htmlspecialchars($enroll['first_name'] . ' ' . $enroll['last_name']) ?></td>
                                                <td><?= htmlspecialchars($enroll['email']) ?></td>
                                                <td><strong class="<?= $avg_class ?>"><?= $avg_display ?></strong></td>
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

                    <!-- All Grades for Selected Course -->
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-journal-text me-2"></i>
                                Toutes les notes - <?= htmlspecialchars($course_info['code']) ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Étudiant</th>
                                            <th>Matricule</th>
                                            <th>Type d'examen</th>
                                            <th>Note</th>
                                            <th>Date</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $enrollments_for_grades = $enrollment->getByCourse($selected_course_id);
                                        $has_grades = false;
                                        while ($enroll = $enrollments_for_grades->fetch(PDO::FETCH_ASSOC)): 
                                            $grades_stmt = $grade->getByEnrollment($enroll['id']);
                                            while ($g = $grades_stmt->fetch(PDO::FETCH_ASSOC)):
                                                $has_grades = true;
                                                $grade_class = $g['grade'] >= 50 ? 'bg-success' : 'bg-danger';
                                        ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($enroll['first_name'] . ' ' . $enroll['last_name']) ?></td>
                                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($enroll['matricule']) ?></span></td>
                                                    <td><?= htmlspecialchars($g['exam_type']) ?></td>
                                                    <td><span class="badge <?= $grade_class ?>"><?= $g['grade'] ?>/100</span></td>
                                                    <td><?= htmlspecialchars($g['date']) ?></td>
                                                    <td class="text-end">
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick='editGrade(<?= json_encode($g) ?>)'>
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <a href="../../controllers/GradeController.php?action=delete&id=<?= $g['id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger" 
                                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                        <?php 
                                            endwhile;
                                        endwhile; 
                                        
                                        if (!$has_grades):
                                        ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    Aucune note enregistrée pour ce cours
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- No Course Selected -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Veuillez sélectionner un cours pour gérer les notes.
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
                        <div class="alert alert-light">
                            <strong>Étudiant:</strong> <span id="student_name"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type d'examen</label>
                            <select class="form-select" name="exam_type" required>
                                <option value="">Sélectionner le type</option>
                                <option value="Partiel">Partiel</option>
                                <option value="Final">Final</option>
                                <option value="TP">TP</option>
                                <option value="Contrôle continu">Contrôle continu</option>
                                <option value="Projet">Projet</option>
                                <option value="Oral">Oral</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note (/100)</label>
                            <input type="number" class="form-control" name="grade" min="0" max="100" step="0.01" required>
                            <div class="form-text">Entrez une note entre 0 et 100</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Grade Modal -->
    <div class="modal fade" id="editGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Modifier une note</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="../../controllers/GradeController.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_grade_id">
                        <div class="mb-3">
                            <label class="form-label">Type d'examen</label>
                            <select class="form-select" name="exam_type" id="edit_exam_type" required>
                                <option value="">Sélectionner le type</option>
                                <option value="Partiel">Partiel</option>
                                <option value="Final">Final</option>
                                <option value="TP">TP</option>
                                <option value="Contrôle continu">Contrôle continu</option>
                                <option value="Projet">Projet</option>
                                <option value="Oral">Oral</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note (/100)</label>
                            <input type="number" class="form-control" name="grade" id="edit_grade_value" min="0" max="100" step="0.01" required>
                            <div class="form-text">Entrez une note entre 0 et 100</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" id="edit_grade_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            Modifier
                        </button>
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

        function editGrade(grade) {
            document.getElementById('edit_grade_id').value = grade.id;
            document.getElementById('edit_exam_type').value = grade.exam_type;
            document.getElementById('edit_grade_value').value = grade.grade;
            document.getElementById('edit_grade_date').value = grade.date;
            
            var modal = new bootstrap.Modal(document.getElementById('editGradeModal'));
            modal.show();
        }
    </script>
    
</body>
</html>