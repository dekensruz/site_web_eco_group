<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect(ADMIN_URL . '/login.php');
}

$error = '';
$success = '';

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    
    if ($id) {
        // Modification
        if (updateCategory($id, $name, $description)) {
            $_SESSION['success'] = 'La catégorie a été mise à jour avec succès';
            redirect(ADMIN_URL . '/categories.php');
        } else {
            $error = 'Une erreur est survenue lors de la mise à jour de la catégorie';
        }
    } else {
        // Création
        if (createCategory($name, $description)) {
            $_SESSION['success'] = 'La catégorie a été créée avec succès';
            redirect(ADMIN_URL . '/categories.php');
        } else {
            $error = 'Une erreur est survenue lors de la création de la catégorie';
        }
    }
}

// Suppression d'une catégorie
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (deleteCategory($id)) {
        $_SESSION['success'] = 'La catégorie a été supprimée avec succès';
    } else {
        $_SESSION['error'] = 'Une erreur est survenue lors de la suppression de la catégorie';
    }
    redirect(ADMIN_URL . '/categories.php');
}

// Récupérer la catégorie à modifier si l'ID est fourni
$category = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $category = getCategoryById((int)$_GET['edit']);
}

// Récupérer toutes les catégories
$categories = getAllCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories - Administration Eco Group</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/admin/css/admin1.css">
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
                        <a class="nav-link active" href="<?php echo ADMIN_URL; ?>/categories.php">
                            <i class="fas fa-tags"></i> Catégories
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/users.php">
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>
                    <?php endif; ?>
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
                    <h1><?php echo $category ? 'Modifier la catégorie' : 'Gestion des catégories'; ?></h1>
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
                                    <?php if ($category): ?>
                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="form-group">
                                        <label for="name">Nom de la catégorie</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo $category ? htmlspecialchars($category['name']) : ''; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" 
                                                  rows="3"><?php echo $category ? htmlspecialchars($category['description']) : ''; ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        <?php echo $category ? 'Mettre à jour' : 'Ajouter'; ?>
                                    </button>

                                    <?php if ($category): ?>
                                        <a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Annuler
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des catégories -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>Description</th>
                                                <th>Slug</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                                <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                                <td><?php echo htmlspecialchars($cat['slug']); ?></td>
                                                <td class="action-buttons">
                                                    <a href="<?php echo ADMIN_URL; ?>/categories.php?edit=<?php echo $cat['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?php echo ADMIN_URL; ?>/categories.php?delete=<?php echo $cat['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')" 
                                                       title="Supprimer">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($categories)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Aucune catégorie trouvée</td>
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