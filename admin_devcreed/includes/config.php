<?php
// S'assurer qu'il n'y a aucun espace ou caractère avant l'ouverture de la balise PHP
// Démarrer la session avant tout output
if (!headers_sent()) {
    session_start();
} else {
    // Enregistrer l'erreur dans les logs
    error_log("Attention: des en-têtes ont déjà été envoyés avant l'initialisation de la session");
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'devcreed_db');

// Connexion à la base de données
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
$conn->set_charset('utf8mb4');

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données: " . $conn->connect_error);
}

// Sélectionner la base de données
$conn->select_db(DB_NAME);

// Créer la base de données si elle n'existe pas
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    // Sélectionner la base de données
    $conn->select_db(DB_NAME);
    
    // Créer la table des utilisateurs si elle n'existe pas
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'editor', 'visitor') NOT NULL DEFAULT 'visitor',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($sql_users)) {
        die("Erreur lors de la création de la table users: " . $conn->error);
    }
    
    // Créer la table des catégories si elle n'existe pas
    $sql_categories = "CREATE TABLE IF NOT EXISTS categories (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        slug VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($sql_categories)) {
        die("Erreur lors de la création de la table categories: " . $conn->error);
    }
    
    // Créer la table des articles si elle n'existe pas
    $sql_articles = "CREATE TABLE IF NOT EXISTS articles (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title NVARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT NOT NULL,
        excerpt TEXT,
        featured_image VARCHAR(255),
        category_id INT(11),
        author_id INT(11),
        status ENUM('published', 'draft') NOT NULL DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    if (!$conn->query($sql_articles)) {
        die("Erreur lors de la création de la table articles: " . $conn->error);
    }
    
    // Créer la table des commentaires si elle n'existe pas
    $sql_comments = "CREATE TABLE IF NOT EXISTS comments (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        article_id INT(11) NOT NULL,
        user_id INT(11),
        parent_id INT(11) DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        comment TEXT NOT NULL,
        status ENUM('approved', 'pending', 'spam') NOT NULL DEFAULT 'approved',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
    )";
    if (!$conn->query($sql_comments)) {
        die("Erreur lors de la création de la table comments: " . $conn->error);
    }
    
    // Créer la table des membres de l'équipe si elle n'existe pas
    $sql_team = "CREATE TABLE IF NOT EXISTS team_members (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        role VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        email VARCHAR(100),
        github_url VARCHAR(255),
        linkedin_url VARCHAR(255),
        twitter_url VARCHAR(255),
        facebook_url VARCHAR(255),
        youtube_url VARCHAR(255),
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if (!$conn->query($sql_team)) {
        die("Erreur lors de la création de la table team_members: " . $conn->error);
    }
    
    // Insérer ou mettre à jour les utilisateurs admin par défaut
    $default_password = password_hash('Dekens2006', PASSWORD_DEFAULT);
    $admin_accounts = [
        ['email' => 'admin@devcreed.com', 'username' => 'admin', 'full_name' => 'Administrateur'],
        ['email' => 'ruzubadekens@gmail.com', 'username' => 'ruzubadekens', 'full_name' => 'Ruzuba Dekens']
    ];
    
    foreach ($admin_accounts as $admin) {
        $admin_email = $admin['email'];
        $admin_username = $admin['username'];
        $admin_full_name = $admin['full_name'];
        
        // Utiliser une requête préparée pour la sélection
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
        $stmt->bind_param("ss", $admin_email, $admin_username);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows == 0) {
            // Insérer un nouvel utilisateur admin avec une requête préparée
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
            $stmt->bind_param("ssss", $admin_username, $default_password, $admin_email, $admin_full_name);
            $stmt->execute();
            $stmt->close();
        } else {
            // Mettre à jour l'utilisateur admin existant avec une requête préparée
            $stmt = $conn->prepare("UPDATE users SET email = ?, password = ? WHERE username = ? OR email = ?");
            $stmt->bind_param("ssss", $admin_email, $default_password, $admin_username, $admin_email);
            $stmt->execute();
            $stmt->close();
        }
    }
} else {
    echo "Erreur lors de la création de la base de données: " . $conn->error;
}

// Définir le chemin de base
define('BASE_URL', '/devcreed');
define('ADMIN_URL', BASE_URL . '/admin');
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Créer le dossier d'uploads s'il n'existe pas
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Fonction pour rediriger
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fonction pour vérifier si l'utilisateur est admin ou éditeur
function isAdminOrEditor() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor']);
}


// Fonction pour nettoyer les entrées
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Fonction pour générer un slug
function generateSlug($text) {
    // Remplacer les caractères non alphanumériques par des tirets
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Translittération
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Supprimer les caractères indésirables
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Supprimer les tirets dupliqués
    $text = preg_replace('~-+~', '-', $text);
    // Convertir en minuscules
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a-' . time();
    }
    
    return $text;
}

?>