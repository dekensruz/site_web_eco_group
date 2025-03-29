<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect(ADMIN_URL . '/login.php');
}

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect(ADMIN_URL . '/articles.php');
}

$id = (int)$_GET['id'];
$article = getArticleById($id);

// Vérifier si l'article existe
if (!$article) {
    redirect(ADMIN_URL . '/articles.php');
}

$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean($_POST['title']);
    $content = $_POST['content'];
    $category_id = (int)$_POST['category_id'];
    $excerpt = clean($_POST['excerpt']);
    $status = $_POST['status'];
    
    // Gestion de l'image mise en avant
    $featured_image = $article['featured_image'];
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
        $new_image = uploadImage($_FILES['featured_image']);
        if ($new_image) {
            $featured_image = $new_image;
        } else {
            $error = 'Erreur lors du téléchargement de l\'image';
        }
    }
    
    if (empty($error)) {
        if (updateArticle($id, $title, $content, $category_id, $featured_image, $excerpt, $status)) {
            $_SESSION['success'] = 'L\'article a été mis à jour avec succès';
            redirect(ADMIN_URL . '/articles.php');
        } else {
            $error = 'Une erreur est survenue lors de la mise à jour de l\'article';
        }
    }
}

// Récupérer toutes les catégories
$categories = getAllCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article - Administration Eco Group</title>
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
                        <a class="nav-link active" href="<?php echo ADMIN_URL; ?>/articles.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Modifier l'article</h1>
                    <a href="<?php echo ADMIN_URL; ?>/articles.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour aux articles
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="title">Titre</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($article['title']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="category_id">Catégorie</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Sélectionner une catégorie</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $category['id'] == $article['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="featured_image">Image mise en avant</label>
                                <?php if ($article['featured_image']): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo UPLOAD_URL . '/' . $article['featured_image']; ?>" 
                                             alt="Image actuelle" class="preview-image">
                                    </div>
                                <?php endif; ?>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="featured_image" 
                                           name="featured_image" accept="image/*">
                                    <label class="custom-file-label" for="featured_image">
                                        Choisir une nouvelle image
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="excerpt">Extrait</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" 
                                          rows="3"><?php echo htmlspecialchars($article['excerpt']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="content">Contenu</label>
                                <textarea class="form-control" id="content" name="content" 
                                          required><?php echo htmlspecialchars($article['content']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="status">Statut</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="draft" <?php echo $article['status'] === 'draft' ? 'selected' : ''; ?>>
                                        Brouillon
                                    </option>
                                    <option value="published" <?php echo $article['status'] === 'published' ? 'selected' : ''; ?>>
                                        Publié
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/27.1.0/classic/ckeditor.js"></script>
    <script>
        // Initialiser CKEditor
        ClassicEditor
            .create(document.querySelector('#content'))
            .catch(error => {
                console.error(error);
            });

        // Afficher le nom du fichier sélectionné
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
</body>
</html>