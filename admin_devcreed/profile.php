<?php
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
checkAccess();

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Traiter le formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = clean($_POST['full_name']);
    $email = clean($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $error = false;
    
    // Vérifier si le nom complet est rempli
    if (empty($full_name)) {
        $_SESSION['error'] = "Le nom complet est obligatoire.";
        $error = true;
    }
    
    // Vérifier si l'email est valide
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Veuillez entrer une adresse email valide.";
        $error = true;
    }
    
    // Vérifier si l'email existe déjà pour un autre utilisateur
    $sql = "SELECT * FROM users WHERE email = '$email' AND id != $user_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Cette adresse email est déjà utilisée par un autre utilisateur.";
        $error = true;
    }
    
    // Vérifier si le mot de passe actuel est correct (si fourni)
    if (!empty($current_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = "Le mot de passe actuel est incorrect.";
            $error = true;
        }
        
        // Vérifier si le nouveau mot de passe est valide
        if (empty($new_password)) {
            $_SESSION['error'] = "Le nouveau mot de passe est obligatoire.";
            $error = true;
        } elseif ($new_password != $confirm_password) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
            $error = true;
        } elseif (strlen($new_password) < 6) {
            $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
            $error = true;
        }
    }
    
    if (!$error) {
        // Mettre à jour les informations de l'utilisateur
        $sql = "UPDATE users SET full_name = '$full_name', email = '$email'";
        
        // Mettre à jour le mot de passe si fourni
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password = '$hashed_password'";
        }
        
        $sql .= " WHERE id = $user_id";
        
        if ($conn->query($sql)) {
            $_SESSION['full_name'] = $full_name; // Mettre à jour le nom dans la session
            $_SESSION['success'] = "Votre profil a été mis à jour avec succès.";
            redirect(ADMIN_URL . '/profile.php');
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du profil: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Administration Devcreed Academy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="shortcut icon" href="../images/devcreed.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="navbar-brand">
                <img src="../images/devcreed.png" alt="Devcreed Academy">
                <span>Devcreed Admin</span>
            </div>
            <ul class="nav flex-column mt-4">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="articles.php">
                        <i class="fas fa-newspaper"></i>
                        <span>Articles</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags"></i>
                        <span>Catégories</span>
                    </a>
                </li>
                <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item mt-5">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <button class="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Mon Profil</h1>
                <div class="user-dropdown dropdown">
                    <div class="dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=random" alt="User">
                        <span><?php echo $_SESSION['full_name']; ?></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item active" href="profile.php"><i class="fas fa-user me-2"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <?php displayError(); ?>
            <?php displaySuccess(); ?>
            
            <!-- Profile Form -->
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="admin-card">
                        <h5 class="card-title">Informations personnelles</h5>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                <small class="text-muted">Le nom d'utilisateur ne peut pas être modifié.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Rôle</label>
                                <input type="text" class="form-control" id="role" value="<?php echo $user['role'] == 'admin' ? 'Administrateur' : 'Éditeur'; ?>" disabled>
                            </div>
                            
                            <hr class="my-4">
                            <h6 class="mb-3">Changer le mot de passe</h6>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <small class="text-muted">Laissez vide si vous ne souhaitez pas changer votre mot de passe.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>