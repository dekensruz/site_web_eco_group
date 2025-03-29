<?php
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
checkAccess();

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtrer par statut si demandé
$where = '';
if (isset($_GET['status']) && in_array($_GET['status'], ['published', 'draft'])) {
    $status = clean($_GET['status']);
    $where = "status = '$status'";
}

// Récupérer les articles
$articles = getArticles($limit, $offset, $where);

// Compter le nombre total d'articles pour la pagination
$total_articles = countArticles($where);
$total_pages = ceil($total_articles / $limit);

// Supprimer un article si demandé
if (isset($_GET['delete']) && isset($_GET['token'])) {
    // Vérifier le token CSRF
    if ($_GET['token'] === $_SESSION['token']) {
        $id = (int)$_GET['delete'];
        $sql = "DELETE FROM articles WHERE id = $id";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = "L'article a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'article: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Token de sécurité invalide.";
    }
    
    // Rediriger pour éviter les soumissions multiples
    redirect(ADMIN_URL . '/articles.php');
}

// Générer un token CSRF
$_SESSION['token'] = md5(uniqid(mt_rand(), true));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Articles - Administration Devcreed Academy</title>
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
                    <a class="nav-link active" href="articles.php">
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
                <h1>Gestion des Articles</h1>
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
            
            <!-- Articles Management -->
            <div class="admin-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Liste des Articles</h5>
                    <div>
                        <a href="articles.php" class="btn btn-sm btn-outline-secondary me-2 <?php echo !isset($_GET['status']) ? 'active' : ''; ?>">
                            Tous
                        </a>
                        <a href="articles.php?status=published" class="btn btn-sm btn-outline-success me-2 <?php echo isset($_GET['status']) && $_GET['status'] == 'published' ? 'active' : ''; ?>">
                            Publiés
                        </a>
                        <a href="articles.php?status=draft" class="btn btn-sm btn-outline-warning me-2 <?php echo isset($_GET['status']) && $_GET['status'] == 'draft' ? 'active' : ''; ?>">
                            Brouillons
                        </a>
                        <a href="add-article.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Nouvel Article
                        </a>
                    </div>
                </div>
                
                <?php if (count($articles) > 0): ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">Titre</th>
                                <th width="15%">Catégorie</th>
                                <th width="15%">Auteur</th>
                                <th width="10%">Statut</th>
                                <th width="15%">Date</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): ?>
                            <tr>
                                <td><?php echo $article['id']; ?></td>
                                <td><?php echo truncateText($article['title'], 50); ?></td>
                                <td><?php echo $article['category_name'] ?? 'Non catégorisé'; ?></td>
                                <td><?php echo $article['author_name']; ?></td>
                                <td>
                                    <?php if ($article['status'] == 'published'): ?>
                                        <span class="badge bg-success">Publié</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Brouillon</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($article['created_at']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit-article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="articles.php?delete=<?php echo $article['id']; ?>&token=<?php echo $_SESSION['token']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">
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
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php else: ?>
                <div class="alert alert-info">Aucun article trouvé.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>