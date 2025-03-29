<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier l'accès à la page d'administration
checkAdminAccess();

// Vérifier si l'utilisateur est connecté
checkAccess();

// Récupérer les statistiques
$total_articles = countArticles();
$published_articles = countArticles("status = 'published'");
$draft_articles = countArticles("status = 'draft'");

// Récupérer les catégories
$categories = getCategories();
$total_categories = count($categories);

// Récupérer les utilisateurs
$users = getUsers();
$total_users = count($users);

// Récupérer les derniers articles
$recent_articles = getArticles(5);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administration Devcreed Academy</title>
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
                    <a class="nav-link active" href="index.php">
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
                <li class="nav-item">
                    <a class="nav-link" href="team.php">
                        <i class="fas fa-user-friends"></i>
                        <span>Équipe</span>
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
                <h1>Tableau de bord</h1>
                <div class="user-dropdown dropdown">
                    <div class="dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name'] ?? 'Utilisateur'); ?>&background=random" alt="User">
                        <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Utilisateur'); ?></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <?php displayError(); ?>
            <?php displaySuccess(); ?>
            
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="admin-card text-center">
                        <div class="card-icon primary mx-auto">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h3><?php echo $total_articles; ?></h3>
                        <p>Articles au total</p>
                        <a href="articles.php" class="btn btn-sm btn-primary">Voir tous</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-card text-center">
                        <div class="card-icon success mx-auto">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3><?php echo $published_articles; ?></h3>
                        <p>Articles publiés</p>
                        <a href="articles.php?status=published" class="btn btn-sm btn-success">Voir publiés</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-card text-center">
                        <div class="card-icon warning mx-auto">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h3><?php echo $draft_articles; ?></h3>
                        <p>Brouillons</p>
                        <a href="articles.php?status=draft" class="btn btn-sm btn-warning">Voir brouillons</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-card text-center">
                        <div class="card-icon danger mx-auto">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h3><?php echo $total_categories; ?></h3>
                        <p>Catégories</p>
                        <a href="categories.php" class="btn btn-sm btn-danger">Voir toutes</a>
                    </div>
                </div>
                <?php if (isAdmin()): ?>
                <div class="col-md-3">
                    <div class="admin-card text-center">
                        <div class="card-icon info mx-auto">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3><?php echo $total_users; ?></h3>
                        <p>Utilisateurs</p>
                        <a href="users.php" class="btn btn-sm btn-info">Gérer</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-card text-center">
                        <div class="card-icon purple mx-auto">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h3><?php echo countTeamMembers(); ?></h3>
                        <p>Membres de l'équipe</p>
                        <a href="team.php" class="btn btn-sm btn-purple">Gérer</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Articles -->
            <div class="row mt-4">
                <?php if (isAdmin()): ?>
                <div class="col-md-4 mb-4">
                    <div class="admin-card">
                        <h5 class="card-title">Statistiques Utilisateurs</h5>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Administrateurs
                                <span class="badge bg-primary rounded-pill"><?php echo countUsers('admin'); ?></span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Éditeurs
                                <span class="badge bg-info rounded-pill"><?php echo countUsers('editor'); ?></span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Utilisateurs
                                <span class="badge bg-secondary rounded-pill"><?php echo countUsers('user'); ?></span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-end">
                            <a href="users.php" class="btn btn-sm btn-outline-primary">Gérer les utilisateurs</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-md-8">
                    <div class="admin-card">
                        <h5 class="card-title">Articles récents</h5>
                        <?php if (count($recent_articles) > 0): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Titre</th>
                                        <th>Catégorie</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_articles as $article): ?>
                                    <tr>
                                        <td><?php echo truncateText($article['title'], 30); ?></td>
                                        <td><?php echo $article['category_name'] ?? 'Non catégorisé'; ?></td>
                                        <td>
                                            <?php if ($article['status'] == 'published'): ?>
                                            <span class="badge bg-success">Publié</span>
                                            <?php else: ?>
                                            <span class="badge bg-warning">Brouillon</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($article['created_at']); ?></td>
                                        <td>
                                            <a href="edit-article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-end">
                            <a href="articles.php" class="btn btn-sm btn-outline-primary">Voir tous les articles</a>
                        </div>
                        <?php else: ?>
                        <p class="text-center py-3">Aucun article trouvé. <a href="add-article.php">Créer un article</a></p>
                        <?php endif;