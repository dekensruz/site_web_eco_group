<?php
global $conn;

function registerUser($username, $password, $email, $full_name) {
    global $conn;
    try {
        error_log("Tentative d'inscription pour l'utilisateur: " . $username . " avec l'email: " . $email);
        
        // Vérifier si l'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            error_log("Tentative d'inscription avec un nom d'utilisateur ou email déjà existant: " . $username . " / " . $email);
            return 'exists';
        }
        
        // Hasher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            error_log("Erreur lors du hashage du mot de passe pour l'utilisateur: " . $username);
            return false;
        }
        
        // Insérer l'utilisateur avec le rôle 'visitor'
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'visitor')");
        if (!$stmt) {
            error_log("Erreur de préparation de la requête SQL: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $full_name);
        
        if ($stmt->execute()) {
            error_log("Inscription réussie pour l'utilisateur: " . $username);
            return true;
        }
        
        error_log("Échec de l'insertion en base de données. Erreur: " . $stmt->error);
        return false;
    } catch (Exception $e) {
        error_log("Exception lors de l'enregistrement de l'utilisateur: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
        return false;
    }
}

function getAllUsers() {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, username, email, full_name, role, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    } catch (Exception $e) {
        error_log("Exception lors de la récupération des utilisateurs: " . $e->getMessage());
        return false;
    }
}

function authenticateUser($username, $password) {
    global $conn;
    try {
        // Tentative de connexion avec le nom d'utilisateur ou l'email
        $stmt = $conn->prepare("SELECT id, username, email, password, role, full_name FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
                $_SESSION['email'] = $user['email'];
                return true;
            } else {
                error_log("Échec d'authentification - Mot de passe incorrect pour l'utilisateur: " . $username);
            }
        } else {
            error_log("Échec d'authentification - Utilisateur non trouvé: " . $username);
        }
        return false;
    } catch (Exception $e) {
        error_log("Erreur d'authentification: " . $e->getMessage());
        return false;
    }
}

function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

function createArticle($title, $content, $category_id, $featured_image = null, $excerpt = '', $status = 'draft') {
    global $conn;
    
    try {
        // Générer le slug
        $slug = generateSlug($title);
        $base_slug = $slug;
        $counter = 1;
        
        // Vérifier si le slug existe déjà
        while (true) {
            $check = $conn->prepare("SELECT id FROM articles WHERE slug = ?");
            $check->bind_param("s", $slug);
            $check->execute();
            if ($check->get_result()->num_rows === 0) break;
            $slug = $base_slug . '-' . $counter++;
        }
        
        $stmt = $conn->prepare("INSERT INTO articles (title, slug, content, excerpt, featured_image, category_id, author_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $author_id = $_SESSION['user_id'];
        $stmt->bind_param("sssssiis", $title, $slug, $content, $excerpt, $featured_image, $category_id, $author_id, $status);
        
        if ($stmt->execute()) {
            return $conn->insert_id;
        } else {
            error_log("Erreur lors de la création de l'article: " . $stmt->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception lors de la création de l'article: " . $e->getMessage());
        return false;
    }
}

function getUserById($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, username, email, full_name, role FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Exception lors de la récupération de l'utilisateur: " . $e->getMessage());
        return false;
    }
}

function uploadImage($file) {
    // Vérifier le type MIME
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    // Vérifier la taille (max 5MB)
    if ($file['size'] > 5000000) {
        return false;
    }
    
    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $upload_path = UPLOAD_DIR . '/' . $filename;
    
    // Créer le dossier d'upload s'il n'existe pas
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $filename;
    }
    
    return false;
}

function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

function updateUser($user_id, $email, $full_name) {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE users SET email = ?, full_name = ? WHERE id = ?");
        $stmt->bind_param("ssi", $email, $full_name, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Exception lors de la mise à jour de l'utilisateur: " . $e->getMessage());
        return false;
    }
}

function updateUserPassword($user_id, $new_password) {
    global $conn;
    try {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            error_log("Erreur lors du hashage du mot de passe pour l'utilisateur ID: " . $user_id);
            return false;
        }
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Exception lors de la mise à jour du mot de passe: " . $e->getMessage());
        return false;
    }
}

function getCategoryById($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, name, slug, description, created_at FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Exception lors de la récupération de la catégorie: " . $e->getMessage());
        return false;
    }
}

function getAllCategories() {
    global $conn;
    try {
        $sql = "SELECT id, name, slug, description, created_at FROM categories ORDER BY name ASC";
        $result = $conn->query($sql);
        
        if ($result) {
            $categories = [];
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            return $categories;
        } else {
            error_log("Erreur lors de la récupération des catégories: " . $conn->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception lors de la récupération des catégories: " . $e->getMessage());
        return false;
    }
}

function getArticleById($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT a.*, c.name as category_name, u.full_name as author_name 
                               FROM articles a 
                               LEFT JOIN categories c ON a.category_id = c.id 
                               LEFT JOIN users u ON a.author_id = u.id 
                               WHERE a.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Exception lors de la récupération de l'article: " . $e->getMessage());
        return false;
    }
}

function updateArticle($id, $title, $content, $category_id, $featured_image = null, $excerpt = '', $status = null) {
    global $conn;
    try {
        // Récupérer l'article actuel
        $stmt = $conn->prepare("SELECT status FROM articles WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_article = $result->fetch_assoc();
        
        if (!$current_article) {
            error_log("Article non trouvé pour la mise à jour. ID: " . $id);
            return false;
        }
        
        // Mise à jour du statut
        if ($status !== null && $status !== '') {
            error_log("Nouveau statut fourni pour l'article ID: " . $id . " - Statut: " . $status);
        } else {
            $status = $current_article['status'];
            error_log("Conservation du statut actuel pour l'article ID: " . $id . " - Statut: " . $status);
        }
        
        // S'assurer que le statut est valide et correctement appliqué
        $valid_statuses = ['draft', 'published'];
        if (!in_array($status, $valid_statuses)) {
            error_log("Statut invalide fourni pour l'article. ID: " . $id . ", Status: " . $status);
            $status = $current_article['status']; // Conserver l'ancien statut en cas d'erreur
        } else {
            error_log("Application du nouveau statut valide pour l'article ID: " . $id . " - Ancien statut: " . $current_article['status'] . " - Nouveau statut: " . $status);
        }

        // Générer le slug
        $slug = generateSlug($title);
        $base_slug = $slug;
        $counter = 1;
        
        // Vérifier si le slug existe déjà (en excluant l'article actuel)
        while (true) {
            $check = $conn->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
            $check->bind_param("si", $slug, $id);
            $check->execute();
            if ($check->get_result()->num_rows === 0) break;
            $slug = $base_slug . '-' . $counter++;
        }
        
        error_log("Mise à jour de l'article ID: " . $id . " - Nouveau statut: " . $status);
        
        $stmt = $conn->prepare("UPDATE articles SET title = ?, slug = ?, content = ?, excerpt = ?, featured_image = ?, category_id = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sssssiis", $title, $slug, $content, $excerpt, $featured_image, $category_id, $status, $id);
        
        if ($stmt->execute()) {
            error_log("Mise à jour réussie de l'article - ID: " . $id . " - Statut: " . $status);
            return true;
        } else {
            error_log("Échec de la mise à jour de l'article - ID: " . $id . " - Erreur: " . $stmt->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception lors de la mise à jour de l'article: " . $e->getMessage());
        return false;
    }
}

function deleteArticle($id) {
    global $conn;
    try {
        // Supprimer l'article
        $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Erreur lors de la suppression de l'article: " . $stmt->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception lors de la suppression de l'article: " . $e->getMessage());
        return false;
    }
}

function getAllArticles($limit = null, $offset = 0, $where = '') {
    global $conn;
    $sql = "SELECT a.*, c.name as category_name, u.full_name as author_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id";
    
    if (!empty($where)) {
        $sql .= " WHERE " . $where;
    }
    
    $sql .= " ORDER BY a.created_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT ?, ?";
    }
    
    try {
        $stmt = $conn->prepare($sql);
        
        if ($limit !== null) {
            $stmt->bind_param('ii', $offset, $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $articles = [];
        
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        
        $stmt->close();
        return $articles;
    } catch (Exception $e) {
        error_log("Error in getAllArticles: " . $e->getMessage());
        return [];
    }
}

function createUser($username, $password, $email, $full_name, $role = 'visitor') {
    global $conn;
    try {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            error_log("Tentative de création d'un utilisateur avec un nom ou email déjà existant: " . $username);
            return false;
        }
        
        // Hasher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            error_log("Erreur lors du hashage du mot de passe pour l'utilisateur: " . $username);
            return false;
        }
        
        // Insérer l'utilisateur
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $hashed_password, $email, $full_name, $role);
        
        if ($stmt->execute()) {
            error_log("Nouvel utilisateur créé avec succès: " . $username);
            return true;
        }
        
        error_log("Échec de la création de l'utilisateur. Erreur: " . $stmt->error);
        return false;
    } catch (Exception $e) {
        error_log("Exception lors de la création de l'utilisateur: " . $e->getMessage());
        return false;
    }
}

function deleteCategory($id) {
    global $conn;
    try {
        // Vérifier si la catégorie est utilisée par des articles
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            error_log("Impossible de supprimer la catégorie ID: " . $id . " - Des articles y sont associés");
            return false;
        }
        
        // Supprimer la catégorie
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            error_log("Catégorie supprimée avec succès - ID: " . $id);
            return true;
        }
        
        error_log("Erreur lors de la suppression de la catégorie: " . $stmt->error);
        return false;
    } catch (Exception $e) {
        error_log("Exception lors de la suppression de la catégorie: " . $e->getMessage());
        return false;
    }
}

function createCategory($name, $description = '') {
    global $conn;
    try {
        // Générer le slug
        $slug = generateSlug($name);
        $base_slug = $slug;
        $counter = 1;
        
        // Vérifier si le slug existe déjà
        while (true) {
            $check = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
            $check->bind_param("s", $slug);
            $check->execute();
            if ($check->get_result()->num_rows === 0) break;
            $slug = $base_slug . '-' . $counter++;
        }
        
        // Insérer la nouvelle catégorie
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $slug, $description);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Erreur lors de la création de la catégorie: " . $stmt->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception lors de la création de la catégorie: " . $e->getMessage());
        return false;
    }
}

function updateCategory($id, $name, $description = '') {
    global $conn;
    try {
        // Générer le slug
        $slug = generateSlug($name);
        $base_slug = $slug;
        $counter = 1;
        
        // Vérifier si le slug existe déjà (en excluant la catégorie actuelle)
        while (true) {
            $check = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
            $check->bind_param("si", $slug, $id);
            $check->execute();
            if ($check->get_result()->num_rows === 0) break;
            $slug = $base_slug . '-' . $counter++;
        }
        
        // Mettre à jour la catégorie
        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $slug, $description, $id);
        
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Erreur lors de la mise à jour de la catégorie: " . $stmt->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception lors de la mise à jour de la catégorie: " . $e->getMessage());
        return false;
    }
}