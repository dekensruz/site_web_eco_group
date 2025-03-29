<?php
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
checkAccess();

// Traiter le formulaire d'ajout d'article
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        $_SESSION['error'] = "Token de sécurité invalide.";
        redirect(ADMIN_URL . '/articles.php');
    }
    $title = clean($_POST['title']);
    $content = $_POST['content'];
    $category_id = (int)$_POST['category_id'];
    $status = clean($_POST['status']);
    $error = false;
    
    // Vérifier si les champs obligatoires sont remplis
    if (empty($title) || empty($content)) {
        $_SESSION['error'] = "Le titre et le contenu sont obligatoires.";
        $error = true;
    }
    
    // Valider le statut
    if (!in_array($status, ['draft', 'published'])) {
        $_SESSION['error'] = "Statut invalide.";
        $error = true;
    }
    
    // Traiter l'image de couverture si elle est fournie
    $featured_image = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $featured_image = uploadImage($_FILES['cover_image']);
        if (!$featured_image) {
            $error = true;
        }
    }
    
    if (!$error) {
        try {
            // Insérer l'article dans la base de données avec une requête préparée
            $author_id = $_SESSION['user_id'];
            $sql = "INSERT INTO articles (title, slug, content, excerpt, featured_image, category_id, author_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $excerpt = truncateText($content, 150);
            $slug = generateSlug($title);
            $stmt->bind_param('sssssiis', $title, $slug, $content, $excerpt, $featured_image, $category_id, $author_id, $status);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "L'article a été publié avec succès.";
                redirect(ADMIN_URL . '/articles.php');
            } else {
                $error = $stmt->error;
                throw new Exception($error);
            }
        } catch (Exception $e) {
            error_log("Error in add-article.php: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors de la publication de l'article.";
        }
    }
}

// Récupérer les catégories pour le formulaire
$categories = getCategories();

// Générer un token CSRF
$_SESSION['token'] = md5(uniqid(mt_rand(), true));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvel Article - Administration Devcreed Academy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="shortcut icon" href="../images/devcreed.png" type="image/x-icon">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/k35ju8dv19zxkn692he6f35k6wvcm3j9xvlo8h1bib0v9m2r/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 500,
            language: 'fr_FR',
            images_upload_url: 'upload-image.php',
            automatic_uploads: true,
            images_reuse_filename: true,
            file_picker_types: 'image',
            file_picker_callback: function(callback, value, meta) {
                // Fournir un fichier par défaut et texte
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');

                input.onchange = function() {
                    var file = this.files[0];
                    var reader = new FileReader();
                    reader.onload = function() {
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);
                        callback(blobInfo.blobUri(), { title: file.name });
                    };
                    reader.readAsDataURL(file);
                };
                input.click();
            },
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });
    </script>
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
                <h1>Nouvel Article</h1>
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
            
            <!-- Article Form -->
            <div class="admin-card">
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre *</label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Contenu *</label>
                        <textarea class="form-control" id="content" name="content"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Catégorie</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="0">Aucune catégorie</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cover_image" class="form-label">Image de couverture</label>
                        <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
                        <small class="text-muted">Formats acceptés : JPEG, PNG, GIF, WEBP. Taille maximale : 5MB</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" <?php echo isset($_POST['status']) && $_POST['status'] == 'draft' ? 'selected' : ''; ?>>Brouillon</option>
                            <option value="published" <?php echo isset($_POST['status']) && $_POST['status'] == 'published' ? 'selected' : ''; ?>>Publié</option>
                        </select>
                    </div>
                    
                    <div class="text-end">
                        <a href="articles.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Publier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.admin-sidebar').classList.toggle('collapsed');
        });
    </script>
</body>
</html>