<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect(ADMIN_URL . '/login.php');
}

// Récupérer les statistiques
$total_articles = count(getAllArticles());
$published_articles = count(array_filter(getAllArticles(), function($article) {
    return $article['status'] === 'published';
}));
$draft_articles = $total_articles - $published_articles;
$total_categories = count(getAllCategories());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administration Eco Group</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/admin/css/admin.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 1rem;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background-color: #28a745;
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        .main-content {
            padding: 2rem;
        }
        .stats-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .stats-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .stats-card .number {
            font-size: 2rem;
            font-weight: bold;
        }
        .stats-card .label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .welcome-message {
            margin-bottom: 2rem;
        }
    </style>
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
                        <a class="nav-link active" href="<?php echo ADMIN_URL; ?>/index.php">
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
                <div class="welcome-message">
                    <h1>Bienvenue, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Utilisateur'; ?></h1>
                    <p>Gérez votre contenu et suivez vos statistiques depuis votre tableau de bord.</p>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="fas fa-newspaper text-primary"></i>
                            <div class="number"><?php echo $total_articles; ?></div>
                            <div class="label">Total des articles</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="fas fa-check-circle text-success"></i>
                            <div class="number"><?php echo $published_articles; ?></div>
                            <div class="label">Articles publiés</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="fas fa-edit text-warning"></i>
                            <div class="number"><?php echo $draft_articles; ?></div>
                            <div class="label">Articles en brouillon</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <i class="fas fa-tags text-info"></i>
                            <div class="number"><?php echo $total_categories; ?></div>
                            <div class="label">Catégories</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Actions rapides</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="<?php echo ADMIN_URL; ?>/add-article.php" class="btn btn-success btn-block mb-3">
                                            <i class="fas fa-plus-circle"></i> Nouvel article
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn btn-info btn-block mb-3">
                                            <i class="fas fa-folder-plus"></i> Nouvelle catégorie
                                        </a>
                                    </div>
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