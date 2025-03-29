<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier l'accès administrateur
checkAdminAccess();

// Traitement du changement de rôle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $userId = (int)$_POST['user_id'];
    $newRole = $_POST['role'];
    
    // Vérifier que l'utilisateur existe
    $user = getUserById($userId);
    if ($user) {
        // Seul un super admin peut modifier les rôles
        if (isSuperAdmin()) {
            if (updateUserRole($userId, $newRole)) {
                $_SESSION['success'] = "Le rôle de l'utilisateur a été mis à jour avec succès.";
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du rôle.";
            }
        } else {
            $_SESSION['error'] = "Vous n'avez pas les droits nécessaires pour modifier les rôles.";
        }
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé.";
    }
    
    // Rediriger pour éviter la soumission multiple du formulaire
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Récupérer la liste des utilisateurs avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Récupérer les utilisateurs pour la page actuelle
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Calculer le nombre total de pages
$total_users = countUsers();
$total_pages = ceil($total_users / $limit);

// Récupérer les statistiques
$totalUsers = countUsers();
$totalAdmins = countUsers('admin');
$totalEditors = countUsers('editor');
$totalVisitors = countUsers('visitor');

// Traiter le formulaire d'ajout/modification d'utilisateur
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $full_name = clean($_POST['full_name']);
    $role = clean($_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $error = false;
    
    // Vérifier si les champs obligatoires sont remplis
    if (empty($username) || empty($email) || empty($full_name) || empty($role)) {
        $_SESSION['error'] = "Tous les champs marqués d'un astérisque sont obligatoires.";
        $error = true;
    }
    
    // Vérifier si l'email est valide
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Veuillez entrer une adresse email valide.";
        $error = true;
    }
    
    // Vérifier le mot de passe pour un nouvel utilisateur
    if ($id == 0 && (empty($password) || empty($confirm_password))) {
        $_SESSION['error'] = "Le mot de passe est obligatoire pour un nouvel utilisateur.";
        $error = true;
    }
    
    // Vérifier si les mots de passe correspondent
    if (!empty($password) && $password != $confirm_password) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        $error = true;
    }
    
    // Vérifier la longueur du mot de passe
    if (!empty($password) && strlen($password) < 6) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
        $error = true;
    }
    
    if (!$error) {
        // Vérifier si le nom d'utilisateur existe déjà
        $sql = "SELECT * FROM users WHERE username = '$username' AND id != $id";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Ce nom d'utilisateur est déjà utilisé.";
        } else {
            // Vérifier si l'email existe déjà
            $sql = "SELECT * FROM users WHERE email = '$email' AND id != $id";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Cette adresse email est déjà utilisée.";
            } else {
                if ($id > 0) {
                    // Mise à jour d'un utilisateur existant
                    $sql = "UPDATE users SET username = '$username', email = '$email', full_name = '$full_name', role = '$role'";
                    
                    // Mettre à jour le mot de passe si fourni
                    if (!empty($password)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $sql .= ", password = '$hashed_password'";
                    }
                    
                    $sql .= " WHERE id = $id";
                    $success_message = "L'utilisateur a été mis à jour avec succès.";
                } else {
                    // Ajout d'un nouvel utilisateur
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO users (username, password, email, full_name, role) VALUES ('$username', '$hashed_password', '$email', '$full_name', '$role')";
                    $success_message = "L'utilisateur a été ajouté avec succès.";
                }
                
                if ($conn->query($sql)) {
                    $_SESSION['success'] = $success_message;
                    redirect(ADMIN_URL . '/users.php');
                } else {
                    $_SESSION['error'] = "Erreur lors de l'enregistrement de l'utilisateur: " . $conn->error;
                }
            }
        }
    }
}

// Nous n'avons plus besoin de récupérer un utilisateur pour modification

// Générer un token CSRF
$_SESSION['token'] = md5(uniqid(mt_rand(), true));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Administration Devcreed Academy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="shortcut icon" href="../images/devcreed.png" type="image/x-icon">
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
                <li class="nav-item">
                    <a class="nav-link active" href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
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
                <h1>Gestion des Utilisateurs</h1>
                <div class="user-info">
                    <span class="username"><?php echo isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'Utilisateur'; ?></span>
                    <a href="profile.php" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-user-circle"></i>
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="content-wrapper p-4">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-5">
                    <h4 class="mb-4">Statistiques des utilisateurs</h4>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">Total Utilisateurs</h5>
                                <p class="card-text display-4"><?php echo $totalUsers; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">Administrateurs</h5>
                                <p class="card-text display-4"><?php echo $totalAdmins; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">Éditeurs</h5>
                                <p class="card-text display-4"><?php echo $totalEditors; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-secondary text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">Visiteurs</h5>
                                <p class="card-text display-4"><?php echo $totalVisitors; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card mt-5">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Liste des Utilisateurs</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" data-action="add">
                            <i class="fas fa-plus"></i> Ajouter un Utilisateur
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom d'utilisateur</th>
                                        <th>Nom complet</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Date d'inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php
                                                echo '<form method="POST" onchange="this.submit()">';
                                                echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
                                                echo '<select name="role" class="form-select">';
                                                foreach (['admin' => 'Administrateur', 'editor' => 'Éditeur', 'visitor' => 'Visiteur'] as $value => $label) {
                                                    echo '<option value="' . $value . '" ' . ($user['role'] === $value ? 'selected' : '') . '>' . $label . '</option>';
                                                }
                                                echo '</select>';
                                                echo '</form>';
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if (isset($_SESSION['user']['id']) && $user['id'] !== $_SESSION['user']['id']): ?>
                                            <form method="POST" action="delete-user.php" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur? ')">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                <i class="fas fa-user-shield"></i> Vous-même
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- User Modal pour ajouter un utilisateur -->
            <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="userModalLabel">Ajouter un utilisateur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="" method="POST" id="userForm">
                            <div class="modal-body">
                                <input type="hidden" name="id" value="">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nom d'utilisateur *</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rôle *</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Sélectionner un rôle</option>
                                        <option value="visitor">Visiteur</option>
                                        <option value="user">Utilisateur</option>
                                        <option value="editor">Éditeur</option>
                                        <?php if (isSuperAdmin()): ?>
                                        <option value="admin">Administrateur</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
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

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmer la suppression</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Êtes-vous sûr de vouloir supprimer cet utilisateur ?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <form action="" method="POST" id="deleteForm">
                                <input type="hidden" name="delete_user" id="deleteUserId">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function confirmDelete(userId) {
                document.getElementById('deleteUserId').value = userId;
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            }
            </script>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>