<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';
require_once '../../controllers/AuthController.php';
require_once '../../models/Course.php';
require_once '../../models/Teacher.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);
$auth->requireRole('admin');

$course = new Course($db);
$teacher = new Teacher($db);

$search = $_GET['search'] ?? '';
if ($search) {
    $courses = $course->search($search);
} else {
    $courses = $course->getAll();
}

$teachers = $teacher->getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des cours | Gestionnaire Académique</title>
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
                            <a class="nav-link active" href="courses.php">
                                <i class="bi bi-book me-2"></i>
                                Cours
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="enrollments.php">
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
                    <h1 class="h2 flex-grow-1" style="max-width: 75%;">Gestion des cours</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        <i class="bi bi-plus-circle me-2"></i>
                        Ajouter un cours
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php
                        switch($_GET['success']) {
                            case 'created': echo 'Cours créé avec succès !'; break;
                            case 'updated': echo 'Cours modifié avec succès !'; break;
                            case 'deleted': echo 'Cours supprimé avec succès !'; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Search Bar -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="d-flex align-items-center gap-2">
                            <div class="search-bar flex-grow-1">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" name="search" 
                                    placeholder="Rechercher par code ou nom de cours..." 
                                    value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary px-4">
                                Rechercher
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Courses Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom du cours</th>
                                        <th>Enseignant</th>
                                        <th>Crédits</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $courses->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($row['code']) ?></span></td>
                                            <td><?= htmlspecialchars($row['name']) ?></td>
                                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                            <td><span class="badge bg-warning text-dark"><?= $row['credits'] ?> crédits</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary" onclick='editCourse(<?= json_encode($row) ?>)'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="../../controllers/CourseController.php?action=delete&id=<?= $row['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')">
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

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="../../controllers/CourseController.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Code du cours</label>
                                <input type="text" class="form-control" name="code" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Crédits</label>
                                <input type="number" class="form-control" name="credits" min="1" max="10" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Nom du cours</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Enseignant</label>
                                <select class="form-select" name="teacher_id" required>
                                    <option value="">Sélectionner un enseignant</option>
                                    <?php 
                                    $teachers->execute();
                                    while ($t = $teachers->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier un cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="../../controllers/CourseController.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Code du cours</label>
                                <input type="text" class="form-control" name="code" id="edit_code" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Crédits</label>
                                <input type="number" class="form-control" name="credits" id="edit_credits" min="1" max="10" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Nom du cours</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Enseignant</label>
                                <select class="form-select" name="teacher_id" id="edit_teacher_id" required>
                                    <option value="">Sélectionner un enseignant</option>
                                    <?php 
                                    $teachers->execute();
                                    while ($t = $teachers->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCourse(course) {
            document.getElementById('edit_id').value = course.id;
            document.getElementById('edit_code').value = course.code;
            document.getElementById('edit_name').value = course.name;
            document.getElementById('edit_description').value = course.description;
            document.getElementById('edit_credits').value = course.credits;
            document.getElementById('edit_teacher_id').value = course.teacher_id;
            
            var modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
            modal.show();
        }
    </script>
</body>
</html>