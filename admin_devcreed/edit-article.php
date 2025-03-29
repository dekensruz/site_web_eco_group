<?php
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
checkAccess();

// Récupérer l'article à modifier
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Aucun article spécifié.";
    redirect(ADMIN_URL . '/articles.php');
}

$article = getArticleById((int)$_GET['id']);
if (!$article) {
    $_SESSION['error'] = "Article non trouvé.";
    redirect(ADMIN_URL . '/articles.php');
}

// Traiter le formulaire de modification d'article
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

    // Vérifier si une catégorie valide a été sélectionnée
    if ($category_id <= 0) {
        $_SESSION['error'] = "Veuillez sélectionner une catégorie valide.";
        $error = true;
    }
    
    // Valider le statut
    if (!in_array($status, ['draft', 'published'])) {
        $_SESSION['error'] = "Statut invalide.";
        $error = true;
    }
    
    // Traiter l'image de couverture si elle est fournie
    $cover_image = $article['featured_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $new_cover_image = uploadImage($_FILES['cover_image']);
        if ($new_cover_image) {
            // Supprimer l'ancienne image si elle existe
            if ($cover_image && file_exists(UPLOAD_DIR . '/' . $cover_image)) {
                unlink(UPLOAD_DIR . '/' . $cover_image);
            }
            $cover_image = $new_cover_image;
        } else {
            $error = true;
        }
    }
    
    if (!$error) {
        try {
            // Mettre à jour l'article dans la base de données avec une requête préparée
            $sql = "UPDATE articles SET title = ?, slug = ?, content = ?, excerpt = ?, category_id = ?, status = ?, featured_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $excerpt = truncateText($content, 150);
            $slug = generateSlug($title);
            $stmt->bind_param('ssssissi', $title, $slug, $content, $excerpt, $category_id, $status, $cover_image, $article['id']);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "L'article a été mis à jour avec succès.";
                redirect(ADMIN_URL . '/articles.php');
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error in edit-article.php: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour de l'article.";
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
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
    <title>Modifier l'Article - Administration Devcreed Academy</title>
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
            media_live_embeds: true,
            media_url_resolver: function (data, resolve) {
                if (data.url.indexOf('youtube.com') !== -1 || data.url.indexOf('youtu.be') !== -1) {
                    var videoId = '';
                    if (data.url.indexOf('youtube.com/watch?v=') !== -1) {
                        videoId = data.url.split('v=')[1];
                    } else if (data.url.indexOf('youtu.be/') !== -1) {
                        videoId = data.url.split('youtu.be/')[1];
                    }
                    if (videoId) {
                        var ampersandPosition = videoId.indexOf('&');
                        if (ampersandPosition !== -1) {
                            videoId = videoId.substring(0, ampersandPosition);
                        }
                        var thumbnailUrl = 'https://img.youtube.com/vi/' + videoId + '/maxresdefault.jpg';
                        var embedHtml = '<div class="youtube-embed" style="position:relative;max-width:100%;margin:1em 0;border-radius:8px;overflow:hidden;box-shadow:0 4px 8px rgba(0,0,0,0.1);background:#f8f9fa;">' +
                            '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">' +
                            '<div style="position:absolute;top:0;left:0;width:100%;height:100%;background:url(' + thumbnailUrl + ') center/cover no-repeat;">' +
                            '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:68px;height:48px;background-color:#212121;border-radius:14px;opacity:0.8;">' +
                            '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);border-style:solid;border-width:12px 0 12px 20px;border-color:transparent transparent transparent #fff;"></div>' +
                            '</div></div>' +
                            '<iframe style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;opacity:0;" ' +
                            'src="https://www.youtube.com/embed/' + videoId + '?autoplay=1" ' +
                            'title="YouTube video" ' +
                            'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" ' +
                            'allowfullscreen></iframe>' +
                            '</div>' +
                            '<div style="padding:12px 15px;font-size:14px;color:#606060;">YouTube Video</div>' +
                            '</div>';
                        var iframe = document.createElement('iframe');
                        iframe.src = 'https://www.youtube.com/embed/' + videoId;
                        iframe.style.display = 'none';
                        document.body.appendChild(iframe);
                        iframe.onload = function() {
                            document.body.removeChild(iframe);
                            resolve({html: embedHtml});
                        };
                        iframe.onerror = function() {
                            document.body.removeChild(iframe);
                            resolve({html: embedHtml});
                        };
                        resolve({html: embedHtml});
                    }
                }
                resolve({html: ''});
            },
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
                <h1>Modifier l'Article</h1>
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
                        <input type="text" class="form-control" id="title" name="title" required value="<?php echo htmlspecialchars(isset($_POST['title']) ? $_POST['title'] : $article['title']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Contenu *</label>
                        <textarea class="form-control" id="content" name="content"><?php echo htmlspecialchars(isset($_POST['content']) ? $_POST['content'] : $article['content']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Catégorie</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="0">Aucune catégorie</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) ? $_POST['category_id'] : $article['category_id']) == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cover_image" class="form-label">Image de couverture</label>
                        <?php if ($article['featured_image']): ?>
                        <div class="mb-2">
                            <img src="../uploads/<?php echo $article['featured_image']; ?>" alt="Cover" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
                        <small class="text-muted">Formats acceptés : JPEG, PNG, GIF, WEBP. Taille maximale : 5MB</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" <?php echo $article['status'] == 'draft' ? 'selected' : ''; ?>>Brouillon</option>
                            <option value="published" <?php echo $article['status'] == 'published' ? 'selected' : ''; ?>>Publié</option>
                        </select>
                    </div>
                    
                    <div class="text-end">
                        <a href="articles.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
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