<?php
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
checkAccess();

// Définir le nombre d'éléments par page pour la pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filtrer par statut si demandé
$where = '';
if (isset($_GET['status']) && in_array($_GET['status'], ['approved', 'pending', 'spam'])) {
    $status = $_GET['status'];
    $where = "status = '$status'";
}

// Récupérer les commentaires
$comments = getComments($limit, $offset, $where);

// Compter le nombre total de commentaires pour la pagination
$total_comments = countComments($where);
$total_pages = ceil($total_comments / $limit);

// Approuver, rejeter ou supprimer un commentaire si demandé
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['token'])) {
    if ($_GET['token'] !== $_SESSION['token']) {
        $_SESSION['error'] = "Token de sécurité invalide.";
        redirect(ADMIN_URL . '/comments.php');
    }
    
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $sql = "UPDATE comments SET status = 'approved' WHERE id = $id";
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Le commentaire a été approuvé.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'approbation du commentaire: " . $conn->error;
        }
    } elseif ($action === 'reject') {
        $sql = "UPDATE comments SET status = 'spam' WHERE id = $id";
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Le commentaire a été rejeté.";
        } else {
            $_SESSION['error'] = "Erreur lors du rejet du commentaire: " . $conn->error;
        }
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM comments WHERE id = $id";
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Le commentaire a été supprimé.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du commentaire: " . $conn->error;
        }
    }
    
    redirect(ADMIN_URL . '/comments.php');
}

// Générer un token de sécurité s'il n'existe pas
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commentaires - Administration Devcreed Academy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="shortcut icon" href="../images/devcreed.png" type="image/x-icon">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <img src="../images/devcreed.png" alt="Devcreed Academy" height="40" class="me-2">
                        <span class="fs-4">Admin</span>
                    </a>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                <span>Tableau de bord</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="articles.php">
                                <i class="fas fa-newspaper me-2"></i>
                                <span>Articles</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="comments.php">
                                <i class="fas fa-comments me-2"></i>
                                <span>Commentaires</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags me-2"></i>
                                <span>Catégories</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>
                                <span>Utilisateurs</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user-circle me-2"></i>
                                <span>Mon profil</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                <span>Déconnexion</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Gestion des Commentaires</h1>
                </div>

                <?php displayError(); ?>
                <?php displaySuccess(); ?>

                <!-- Comments Management -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Liste des Commentaires</h5>
                        <div>
                            <a href="comments.php" class="btn btn-sm btn-outline-secondary me-2 <?php echo !isset($_GET['status']) ? 'active' : ''; ?>">
                                Tous
                            </a>
                            <a href="comments.php?status=approved" class="btn btn-sm btn-outline-success me-2 <?php echo isset($_GET['status']) && $_GET['status'] == 'approved' ? 'active' : ''; ?>">
                                Approuvés
                            </a>
                            <a href="comments.php?status=pending" class="btn btn-sm btn-outline-warning me-2 <?php echo isset($_GET['status']) && $_GET['status'] == 'pending' ? 'active' : ''; ?>">
                                En attente
                            </a>
                            <a href="comments.php?status=spam" class="btn btn-sm btn-outline-danger me-2 <?php echo isset($_GET['status']) && $_GET['status'] == 'spam' ? 'active' : ''; ?>">
                                Spam
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($comments) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Article</th>
                                            <th>Auteur</th>
                                            <th>Commentaire</th>
                                            <th>Statut</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($comments as $comment): ?>
                                            <tr>
                                                <td><?php echo $comment['id']; ?></td>
                                                <td>
                                                    <a href="../article.php?slug=<?php echo $comment['article_slug']; ?>" target="_blank">
                                                        <?php echo truncateText($comment['article_title'], 30); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($comment['name']); ?><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($comment['email']); ?></small>
                                                </td>
                                                <td><?php echo truncateText($comment['comment'], 50); ?></td>
                                                <td>
                                                    <?php if ($comment['status'] == 'approved'): ?>
                                                        <span class="badge bg-success">Approuvé</span>
                                                    <?php elseif ($comment['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">En attente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Spam</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatDate($comment['created_at']); ?></td>
                                                <td>
                                                    <?php if ($comment['status'] != 'approved'): ?>
                                                        <a href="comments.php?action=approve&id=<?php echo $comment['id']; ?>&token=<?php echo $_SESSION['token']; ?>" class="btn btn-sm btn-success" title="Approuver">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($comment['status'] != 'spam'): ?>
                                                        <a href="comments.php?action=reject&id=<?php echo $comment['id']; ?>&token=<?php echo $_SESSION['token']; ?>" class="btn btn-sm btn-warning" title="Rejeter">
                                                            <i class="fas fa-ban"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="comments.php?action=delete&id=<?php echo $comment['id']; ?>&token=<?php echo $_SESSION['token']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="comments.php?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Aucun commentaire trouvé.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>