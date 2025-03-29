<?php
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
checkAccess();

// Récupérer les catégories
$categories = getCategories();

// Supprimer une catégorie si demandé
if (isset($_GET['delete']) && isset($_GET['token'])) {
    // Vérifier le token CSRF
    if ($_GET['token'] === $_SESSION['token']) {
        $id = (int)$_GET['delete'];
        
        // Vérifier si la catégorie est utilisée par des articles
        $sql = "SELECT COUNT(*) as count FROM articles WHERE category_id = $id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $_SESSION['error'] = "Cette catégorie ne peut pas être supprimée car elle est utilisée par des articles.";
        } else {
            $sql = "DELETE FROM categories WHERE id = $id";
            
            if ($conn->query($sql)) {
                $_SESSION['success'] = "La catégorie a été supprimée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de la catégorie: " . $conn->error;
            }
        }
    } else {
        $_SESSION['error'] = "Token de sécurité invalide.";
    }
    
    // Rediriger pour éviter les soumissions multiples
    redirect(ADMIN_URL . '/categories.php');
}

// Traiter le formulaire d'ajout/modification de catégorie
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $error = false;
    
    // Vérifier si le nom est rempli
    if (empty($name)) {
        $_SESSION['error'] = "Le nom de la catégorie est obligatoire.";
        $error = true;
    }
    
    if (!$error) {
        // Générer le slug
        $slug = generateSlug($name);
        
        if ($id > 0) {
            // Mise à jour d'une catégorie existante
            $sql = "UPDATE categories SET name = '$name', slug = '$slug', description = '$description' WHERE id = $id";
            $success_message = "La catégorie a été mise à jour avec succès.";
        } else {
            // Ajout d'une nouvelle catégorie
            $sql = "INSERT INTO categories (name, slug, description) VALUES ('$name', '$slug', '$description')";
            $success_message = "La catégorie a été ajoutée avec succès.";
        }
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = $success_message;
            redirect(ADMIN_URL . '/categories.php');
        } else {
            // Vérifier si l'erreur est due à un doublon
            if ($conn->errno == 1062) {
                $_SESSION['error'] = "Une catégorie avec ce nom existe déjà.";
            } else {
                $_SESSION['error'] = "Erreur lors de l'enregistrement de la catégorie: " . $conn->error;
            }
        }
    }
}

// Récupérer une catégorie pour modification si demandé
$category = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $category = getCategoryById($id);
    
    if (!$category) {
        $_SESSION['error'] = "Catégorie non trouvée.";
        redirect(ADMIN_URL . '/categories.php');
    }
}

// Générer un token CSRF
$_SESSION['token'] = md5(uniqid(mt_rand(), true));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - Administration Devcreed Academy</title>
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
                    <a class="nav-link active" href="categories.php">
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
                <h1>Gestion des Catégories</h1>
                <div class="user-dropdown dropdown">
                    <div class="dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=random" alt="User">
                        <span><?php echo $_SESSION['full_name']; ?></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <?php displayError(); ?>
            <?php displaySuccess(); ?>
            
            <div class="row">
                <!-- Formulaire d'ajout/modification -->
                <div class="col-md-4">
                    <div class="admin-card">
                        <h5 class="card-title"><?php echo $category ? 'Modifier la catégorie' : 'Ajouter une catégorie'; ?></h5>
                        <form method="post" action="">
                            <?php if ($category): ?>
                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom de la catégorie</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $category ? htmlspecialchars($category['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo $category ? htmlspecialchars($category['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <?php echo $category ? 'Enregistrer les modifications' : 'Ajouter la catégorie'; ?>
                                </button>
                                <?php if ($category): ?>
                                <a href="categories.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i> Annuler
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Liste des catégories -->
                <div class="col-md-8">
                    <div class="admin-card">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Nom</th>
                                        <th width="45%">Description</th>
                                        <th width="25%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><?php echo $cat['id']; ?></td>
                                        <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                        <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="categories.php?delete=<?php echo $cat['id']; ?>&token=<?php echo $_SESSION['token']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>