<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté et est administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect(ADMIN_URL . '/index.php');
}

$error = '';
$success = '';

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $full_name = clean($_POST['full_name']);
    $role = clean($_POST['role']);
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    
    if ($id) {
        // Modification
        if (updateUser($id, $email, $full_name, $role)) {
            if ($password) {
                updateUserPassword($id, $password);
            }
            $_SESSION['success'] = 'L\'utilisateur a été mis à jour avec succès';
            redirect(ADMIN_URL . '/users.php');
        } else {
            $error = 'Une erreur est survenue lors de la mise à jour de l\'utilisateur';
        }
    } else {
        // Création
        if (createUser($username, $password, $email, $full_name, $role)) {
            $_SESSION['success'] = 'L\'utilisateur a été créé avec succès';
            redirect(ADMIN_URL . '/users.php');
        } else {
            $error = 'Une erreur est survenue lors de la création de l\'utilisateur';
        }
    }
}

// Suppression d'un utilisateur
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) { // Empêcher l'auto-suppression
        if (deleteUser($id)) {
            $_SESSION['success'] = 'L\'utilisateur a été supprimé avec succès';
        } else {
            $_SESSION['error'] = 'Une erreur est survenue lors de la suppression de l\'utilisateur';
        }
    } else {
        $_SESSION['error'] = 'Vous ne pouvez pas supprimer votre propre compte';
    }
    redirect(ADMIN_URL . '/users.php');
}

// Récupérer l'utilisateur à modifier si l'ID est fourni
$user = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $user = getUserById((int)$_GET['edit']);
}

// Récupérer tous les utilisateurs
$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs - Administration Eco Group</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/admin/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 sidebar">
                <div class="text-center mb-4">
                    <img src="<?php echo BASE_URL; ?>/assets/logo.png" alt="Eco Group Logo" style="max-width: 150px;">
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/index.php">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/articles.php">
                            <i class="fas fa-newspaper"></i> Articles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/categories.php">
                            <i class="fas fa-tags"></i> Catégories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo ADMIN_URL; ?>/users.php">
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/profile.php">
                            <i class="fas fa-user"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?php echo $user ? 'Modifier l\'utilisateur' : 'Gestion des utilisateurs'; ?></h1>
                </div>

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

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Formulaire -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST">
                                    <?php if ($user): ?>
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <?php if (!$user): ?>
                                    <div class="form-group">
                                        <label for="username">Nom d'utilisateur</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="full_name">Nom complet</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?php echo $user ? htmlspecialchars($user['full_name']) : ''; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="role">Rôle</label>
                                        <select class="form-control" id="role" name="role">
                                            <option value="editor" <?php echo ($user && $user['role'] === 'editor') ? 'selected' : ''; ?>>
                                                Éditeur
                                            </option>
                                            <option value="admin" <?php echo ($user && $user['role'] === 'admin') ? 'selected' : ''; ?>>
                                                Administrateur
                                            </option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="password"><?php echo $user ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe'; ?></label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               <?php echo $user ? '' : 'required'; ?>>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        <?php echo $user ? 'Mettre à jour' : 'Ajouter'; ?>
                                    </button>

                                    <?php if ($user): ?>
                                        <a href="<?php echo ADMIN_URL; ?>/users.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Annuler
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des utilisateurs -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nom d'utilisateur</th>
                                                <th>Nom complet</th>
                                                <th>Email</th>
                                                <th>Rôle</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $u): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $u['role'] === 'admin' ? 'danger' : 'info'; ?>">
                                                        <?php echo $u['role'] === 'admin' ? 'Administrateur' : 'Éditeur'; ?>
                                                    </span>
                                                </td>
                                                <td class="action-buttons">
                                                    <a href="<?php echo ADMIN_URL; ?>/users.php?edit=<?php echo $u['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                                    <a href="<?php echo ADMIN_URL; ?>/users.php?delete=<?php echo $u['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')" 
                                                       title="Supprimer">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Aucun utilisateur trouvé</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>