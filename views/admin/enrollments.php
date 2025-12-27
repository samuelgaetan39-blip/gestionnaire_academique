<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';
require_once '../../controllers/AuthController.php';
require_once '../../models/Enrollment.php';
require_once '../../models/Student.php';
require_once '../../models/Course.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$auth->requireRole('admin');

$enrollment = new Enrollment($db);
$student = new Student($db);
$course = new Course($db);

$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $enrollments = $enrollment->search($search);
} else {
    $enrollments = $enrollment->getAll();
}

$students = $student->getAll();
$courses = $course->getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des inscriptions | Gestionnaire Académique</title>
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
                            <a class="nav-link" href="students.php">
                                <i class="bi bi-people me-2"></i>
                                Étudiants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="teachers.php">
                                <i class="bi bi-person-badge me-2"></i>
                                Enseignants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="courses.php">
                                <i class="bi bi-book me-2"></i>
                                Cours
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="enrollments.php">
                                <i class="bi bi-journal-check me-2"></i>
                                Inscriptions
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 flex-grow-1" style="max-width: 75%;">Gestion des inscriptions</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEnrollmentModal">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nouvelle inscription
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        switch($_GET['success']) {
                            case 'created': echo 'Inscription créée avec succès !'; break;
                            case 'deleted': echo 'Inscription supprimée avec succès !'; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == 'already_enrolled'): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Cet étudiant est déjà inscrit à ce cours !
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="d-flex align-items-center gap-2">
                            <div class="search-bar flex-grow-1">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" name="search" 
                                    placeholder="Rechercher un étudiant, un matricule, un cours ou une date (AAAA-MM-JJ)" 
                                    value="<?= htmlspecialchars($search) ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary px-4">
                                Rechercher
                            </button>
                            
                            <?php if (!empty($search)): ?>
                                <a href="enrollments.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <!-- Enrollments Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Étudiant</th>
                                        <th>Code cours</th>
                                        <th>Nom du cours</th>
                                        <th>Date d'inscription</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $enrollments->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($row['matricule']) ?></span></td>
                                            <td><?= htmlspecialchars($row['student_first_name'] . ' ' . $row['student_last_name']) ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($row['code']) ?></span></td>
                                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                                            <td><?= htmlspecialchars($row['enrollment_date']) ?></td>
                                            <td class="text-end">
                                                <a href="../../controllers/EnrollmentController.php?action=delete&id=<?= $row['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette inscription ?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Enrollment Modal -->
    <div class="modal fade" id="addEnrollmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle inscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="../../controllers/EnrollmentController.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Étudiant</label>
                            <select class="form-select" name="student_id" required>
                                <option value="">Sélectionner un étudiant</option>
                                <?php 
                                $students->execute();
                                while ($s = $students->fetch(PDO::FETCH_ASSOC)): 
                                ?>
                                    <option value="<?= $s['id'] ?>">
                                        <?= htmlspecialchars($s['matricule'] . ' - ' . $s['first_name'] . ' ' . $s['last_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cours</label>
                            <select class="form-select" name="course_id" required>
                                <option value="">Sélectionner un cours</option>
                                <?php 
                                $courses->execute();
                                while ($c = $courses->fetch(PDO::FETCH_ASSOC)): 
                                ?>
                                    <option value="<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['code'] . ' - ' . $c['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date d'inscription</label>
                            <input type="date" class="form-control" name="enrollment_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Inscrire</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>