<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';
require_once 'admin/includes/comment_functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Vous devez être connecté pour commenter.';
    redirect(BASE_URL . '/admin/login.php');
}

// Traitement du formulaire de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = (int)$_POST['article_id'];
    $comment = clean($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    // Récupérer les informations de l'utilisateur
    $user = getUserById($user_id);
    
    if ($user && !empty($comment) && $article_id > 0) {
        // Vérifier si l'article existe
        $article_check = $conn->prepare("SELECT id FROM articles WHERE id = ? AND status = 'published'");
        $article_check->bind_param("i", $article_id);
        $article_check->execute();
        
        if ($article_check->get_result()->num_rows === 0) {
            $_SESSION['error'] = 'Article introuvable ou non publié.';
        } else {
            // Créer le commentaire
            $status = 'pending'; // Les commentaires sont en attente de modération par défaut
            
            // Si l'utilisateur est admin ou editor, approuver automatiquement
            if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'editor')) {
                $status = 'approved';
            }
            
            if (createComment($article_id, $user['full_name'], $user['email'], $comment, $user_id, null, $status)) {
                $_SESSION['success'] = 'Votre commentaire a été soumis avec succès' . 
                                      ($status === 'pending' ? ' et sera visible après modération.' : '.');
            } else {
                $_SESSION['error'] = 'Une erreur est survenue lors de la soumission de votre commentaire.';
            }
        }
    } else {
        $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
    }
    
    // Rediriger vers l'article avec un message de succès
    $stmt = $conn->prepare("SELECT slug FROM articles WHERE id = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();
    
    if ($article) {
        header('Location: ' . BASE_URL . '/blog.php?article=' . $article['slug'] . '#comments');
        exit;
    } else {
        header('Location: ' . BASE_URL . '/blog.php');
        exit;
    }

} else {
    redirect(BASE_URL . '/blog.php');
}