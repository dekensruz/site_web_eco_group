<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect(ADMIN_URL . '/login.php');
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
    $featured_image = null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
        $featured_image = uploadImage($_FILES['featured_image']);
        if (!$featured_image) {
            $error = 'Erreur lors du téléchargement de l\'image';
        }
    }
    
    if (empty($error)) {
        if (createArticle($title, $content, $category_id, $featured_image, $excerpt, $status)) {
            $_SESSION['success'] = 'L\'article a été créé avec succès';
            redirect(ADMIN_URL . '/articles.php');
        } else {
            $error = 'Une erreur est survenue lors de la création de l\'article';
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
    <title>Nouvel article - Administration Eco Group</title>
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
                    <h1>Nouvel article</h1>
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
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="form-group">
                                <label for="category_id">Catégorie</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Sélectionner une catégorie</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="featured_image">Image mise en avant</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="featured_image" name="featured_image" accept="image/*">
                                    <label class="custom-file-label" for="featured_image">Choisir une image</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="excerpt">Extrait</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="content">Contenu</label>
                                <div id="editor" class="editor-wrapper"></div>
                                <input type="hidden" name="content" id="content">
                            </div>

                            <div class="form-group">
                                <label for="status">Statut</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="draft">Brouillon</option>
                                    <option value="published">Publié</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload"></i> Publier
                            </button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="<?php echo ADMIN_URL; ?>/css/quill.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    imageResize: {
                        displaySize: true,
                        modules: ['Resize', 'DisplaySize', 'Toolbar']
                    },
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'align': [] }, { 'indent': '-1' }, { 'indent': '+1' }],
                        ['blockquote', 'code-block'],
                        ['link', 'image', 'video'],
                        ['clean']
                    ],
                    history: {
                        delay: 2000,
                        maxStack: 500
                    }
                },
                placeholder: 'Rédigez votre article ici...'
            });

            // Gestionnaire pour l'upload d'images
            var toolbar = quill.getModule('toolbar');
            toolbar.addHandler('image', function() {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();

                input.onchange = function() {
                    var file = input.files[0];
                    if (file) {
                        var formData = new FormData();
                        formData.append('file', file);

                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', '<?php echo ADMIN_URL; ?>/upload-image.php', true);

                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                var response = JSON.parse(xhr.responseText);
                                var range = quill.getSelection(true);
                                quill.insertEmbed(range.index, 'image', response.location, 'user');
                            } else {
                                console.error('Erreur lors du téléchargement de l\'image');
                            }
                        };
                        xhr.send(formData);
                    }
                };
            });

            // Mettre à jour le champ caché avant la soumission du formulaire
            document.querySelector('form').onsubmit = function() {
                document.getElementById('content').value = quill.root.innerHTML;
                return true;
            };
                        
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                var response = JSON.parse(xhr.responseText);
                                var range = quill.getSelection(true);
                                quill.insertEmbed(range.index, 'image', response.location, 'user');
                            } else {
                                console.error('Erreur lors du téléchargement de l\'image');
                            }
                        };
                        xhr.send(formData);
                    }
                
            );

            // Validation du formulaire avant soumission
            $('form').on('submit', function(e) {
                const title = $('#title').val().trim();
                const content = quill.root.innerHTML.trim();
                
                if (!title || !content) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs obligatoires');
                    return false;
                }
                return true;
            });

            // Afficher le nom du fichier sélectionné
            $('.custom-file-input').on('change', function() {
                const file = this.files[0];
                const fileTypes = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (file) {
                    if (fileTypes.includes(file.type)) {
                        let fileName = $(this).val().split('\\').pop();
                        $(this).next('.custom-file-label').addClass("selected").html(fileName);
                    } else {
                        alert('Veuillez sélectionner une image valide (JPG, PNG ou GIF)');
                        this.value = '';
                        $(this).next('.custom-file-label').html('Choisir une image');
                    }
                }
            });
        
    </script>
</body>
</html>
    </script>
</body>
</html>