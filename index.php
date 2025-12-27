<?php
session_start();
require_once 'config/database.php';
require_once 'controllers/AuthController.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);

if ($auth->isLoggedIn()) {
    $role = $auth->getRole();
    switch ($role) {
        case 'admin':
            header("Location: views/admin/dashboard.php");
            break;
        case 'teacher':
            header("Location: views/teacher/dashboard.php");
            break;
        case 'student':
            header("Location: views/student/dashboard.php");
            break;
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($email, $password)) {
        $role = $auth->getRole();
        switch ($role) {
            case 'admin':
                header("Location: views/admin/dashboard.php");
                break;
            case 'teacher':
                header("Location: views/teacher/dashboard.php");
                break;
            case 'student':
                header("Location: views/student/dashboard.php");
                break;
        }
        exit();
    } else {
        $error = "E-mail ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire Académique | Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-primary">
    <div class="container mt-4">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 my-auto pb-5">
                <div class="text-center mb-4">
                    <div class="login-icon mb-2">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <h1 class="display-6 fw-bold text-primary">Gestionnaire Académique</h1>
                    <p class="text-muted">Une aide précieuse à votre service</p>
                </div>

                <div class="card shadow-lg border-0 card-login">
                    <div class="card-body pt-4 pb-4 px-4">
                        <h2 class="card-title mb-1 text-center">Connexion</h2>
                        <p class="text-muted text-center mb-4">Entrez vos identifiants pour accéder à votre compte</p>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="votre.email@domaine.com" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Se connecter
                            </button>
                        </form>

                        <div class="mt-4 p-3 custom-notice rounded text-center">
                            <p class="mb-0 small">Pour obtenir un compte, veuillez contacter l'administrateur ou l'administratrice du site.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>