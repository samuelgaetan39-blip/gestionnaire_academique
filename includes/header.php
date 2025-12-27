<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <i class="bi bi-mortarboard-fill text-primary me-2 fs-4"></i>
            <span class="fw-bold">Gestionnaire Académique</span>
        </a>
        <div class="d-flex align-items-center">
            <span class="me-3">
                <i class="bi bi-person-circle me-1"></i>
                <?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?>
            </span>
            <a href="../../controllers/logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>
                Déconnexion
            </a>
        </div>
    </div>
</nav>