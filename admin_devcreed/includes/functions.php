<?php
require_once 'config.php';

// Fonction pour compter le nombre total de membres de l'équipe
function countTeamMembers() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as total FROM team_members");
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Fonction pour récupérer tous les membres de l'équipe
function getTeamMembers() {
    global $conn;
    $result = $conn->query("SELECT * FROM team_members ORDER BY created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fonction pour récupérer un membre de l'équipe par son ID
function getTeamMemberById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}



// Fonction pour supprimer un membre de l'équipe
function deleteTeamMember($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM team_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est un administrateur
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Fonction pour vérifier l'accès à une page
function checkAccess() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
        redirect('../index.php');
    }
    
    // Vérifier si l'utilisateur a le rôle approprié
    if ($_SESSION['user_role'] === 'visitor') {
        $_SESSION['error'] = "Accès non autorisé. Vous n'avez pas les permissions nécessaires.";
        redirect('../index.php');
    }
}

// Fonction pour vérifier l'accès administrateur
function checkAdminAccess() {
    checkAccess();
    if (!isAdmin()) {
        $_SESSION['error'] = "Vous n'avez pas les droits d'accès à cette page.";
        redirect(ADMIN_URL . '/index.php');
    }
}

// Fonction pour afficher les messages d'erreur
function displayError() {
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo $_SESSION['error'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['error']);
    }
}

// Fonction pour afficher les messages de succès
function displaySuccess() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo $_SESSION['success'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['success']);
    }
}

// Fonction pour télécharger une image
function uploadImage($file, $destination = 'articles') {
    // Vérifier si le fichier existe
    if (!isset($file) || $file['error'] != 0) {
        return false;
    }
    
    // Vérifier le type de fichier
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = "Type de fichier non autorisé. Seuls les formats JPEG, PNG, GIF et WEBP sont acceptés.";
        return false;
    }
    
    // Vérifier la taille du fichier (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['error'] = "Le fichier est trop volumineux. La taille maximale est de 5MB.";
        return false;
    }
    
    // Créer le dossier de destination s'il n'existe pas
    $upload_dir = UPLOAD_DIR . '/' . $destination;
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Générer un nom de fichier unique
    $filename = uniqid() . '_' . basename($file['name']);
    $target_file = $upload_dir . '/' . $filename;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $destination . '/' . $filename;
    } else {
        $_SESSION['error'] = "Une erreur est survenue lors du téléchargement du fichier.";
        return false;
    }
}

// Fonction pour récupérer les catégories
function getCategories() {
    global $conn;
    $sql = "SELECT * FROM categories ORDER BY name ASC";
    $result = $conn->query($sql);
    $categories = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

// Fonction pour récupérer une catégorie par son ID
function getCategoryById($id) {
    global $conn;
    $id = (int) $id;
    $sql = "SELECT * FROM categories WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Fonction pour récupérer les articles
function getArticles($limit = null, $offset = 0, $where = '') {
    global $conn;
    $sql = "SELECT a.*, c.name as category_name, u.full_name as author_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id";
    $params = [];
    $types = '';
    
    if (!empty($where)) {
        $sql .= " WHERE " . $where;
    }
    
    $sql .= " ORDER BY a.created_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        $types .= 'ii';
    }
    
    $articles = [];
    try {
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error in getArticles: " . $e->getMessage());
    }
    
    return $articles;
}

// Fonction pour récupérer un article par son ID
function getArticleById($id) {
    global $conn;
    $sql = "SELECT a.*, c.name as category_name, u.full_name as author_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE a.id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $article = $result->fetch_assoc();
        } else {
            $article = null;
        }
        
        $stmt->close();
        return $article;
    } catch (Exception $e) {
        error_log("Error in getArticleById: " . $e->getMessage());
        return null;
    }
}

// Fonction pour supprimer un utilisateur
function deleteUser($userId) {
    global $conn;
    $userId = (int)$userId;
    
    // Vérifier que l'utilisateur existe
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    // Supprimer l'utilisateur
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    
    return $stmt->execute();
}

// Fonction pour compter le nombre total d'articles
function countArticles($where = '') {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM articles";
    $params = [];
    $types = '';
    
    if (!empty($where)) {
        $sql .= " WHERE " . $where;
    }
    
    try {
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['total'];
    } catch (Exception $e) {
        error_log("Error in countArticles: " . $e->getMessage());
        return 0;
    }
}

// Fonction pour récupérer les utilisateurs
function getUsers() {
    global $conn;
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $users = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

// Fonction pour récupérer un utilisateur par son ID
function getUserById($id) {
    global $conn;
    $id = (int) $id;
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Fonction pour mettre à jour le rôle d'un utilisateur
function updateUserRole($userId, $newRole) {
    global $conn;
    $userId = (int) $userId;
    
    // Vérifier que le rôle est valide
    if (!in_array($newRole, ['admin', 'editor', 'visitor'])) {
        return false;
    }
    
    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newRole, $userId);
    
    return $stmt->execute();
}

// Fonction pour vérifier si l'utilisateur est un super admin
function isSuperAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Fonction pour compter le nombre total d'utilisateurs
function countUsers($role = null) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM users";
    $params = [];
    $types = '';
    
    if ($role !== null) {
        $sql .= " WHERE role = ?";
        $params[] = $role;
        $types .= 's';
    }
    
    try {
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['total'];
    } catch (Exception $e) {
        error_log("Error in countUsers: " . $e->getMessage());
        return 0;
    }
}

// Inclure les fonctions communes
require_once __DIR__ . '/../../includes/common-functions.php';

// Fonction pour tronquer un texte
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . '...';
}





// Fonction pour ajouter un membre de l'équipe
function addTeamMember($data) {
    global $conn;
    $sql = "INSERT INTO team_members (full_name, role, description, image, email, github_url, linkedin_url, twitter_url, facebook_url, phone) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssssss', 
            $data['full_name'],
            $data['role'],
            $data['description'],
            $data['image'],
            $data['email'],
            $data['github_url'],
            $data['linkedin_url'],
            $data['twitter_url'],
            $data['facebook_url'],
            $data['phone']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Error in addTeamMember: " . $e->getMessage());
        return false;
    }
}

// Fonction pour mettre à jour un membre de l'équipe
function updateTeamMember($id, $data) {
    global $conn;
    $sql = "UPDATE team_members SET 
            full_name = ?, 
            role = ?, 
            description = ?, 
            image = ?, 
            email = ?, 
            github_url = ?, 
            linkedin_url = ?, 
            twitter_url = ?, 
            facebook_url = ?, 
            youtube_url = ?,
            phone = ? 
            WHERE id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssssi', 
            $data['full_name'],
            $data['role'],
            $data['description'],
            $data['image'],
            $data['email'],
            $data['github_url'],
            $data['linkedin_url'],
            $data['twitter_url'],
            $data['facebook_url'],
            $data['youtube_url'],
            $data['phone'],
            $id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Error in updateTeamMember: " . $e->getMessage());
        return false;
    }
}