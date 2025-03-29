<?php
// Fonctions utilitaires
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo '<script>window.location.href="'.$url.'";</script>';
        exit();
    }
}

if (!headers_sent()) {
    session_start();
} else {
    error_log("Attention: des en-têtes ont déjà été envoyés avant l'initialisation de la session");
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eco_group_db');

// Chemins d'upload
define('BASE_URL', 'http://localhost/eco_group');
define('ADMIN_URL', BASE_URL . '/admin');
define('UPLOAD_DIR', __DIR__ . '/../../uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

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
        role ENUM('admin', 'editor', 'visitor') NOT NULL DEFAULT 'editor',
        email_verified TINYINT(1) NOT NULL DEFAULT 0,
        verification_token VARCHAR(255) DEFAULT NULL,
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
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT NOT NULL,
        excerpt TEXT,
        featured_image VARCHAR(255),
        category_id INT(11),
        author_id INT(11) NOT NULL,
        status ENUM('published', 'draft') NOT NULL DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if (!$conn->query($sql_articles)) {
        error_log("Erreur lors de la création de la table articles: " . $conn->error);
        die("Erreur lors de la création de la table articles: " . $conn->error);
    }
    
    // Créer la table des commentaires si elle n'existe pas
    $sql_comments = "CREATE TABLE IF NOT EXISTS comments (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        article_id INT(11) NOT NULL,
        user_id INT(11) DEFAULT NULL,
        parent_id INT(11) DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        comment TEXT NOT NULL,
        status ENUM('approved', 'pending', 'spam') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if (!$conn->query($sql_comments)) {
        error_log("Erreur lors de la création de la table comments: " . $conn->error);
        die("Erreur lors de la création de la table comments: " . $conn->error);
    }
    
    // Insérer l'administrateur par défaut
    $default_password = password_hash('EcoGroup2024', PASSWORD_DEFAULT);
    $admin_email = 'admin@eco-group.org';
    $admin_username = 'admin';
    $admin_full_name = 'Administrateur';
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->bind_param("ss", $admin_email, $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->bind_param("ssss", $admin_username, $default_password, $admin_email, $admin_full_name);
        $stmt->execute();
        $stmt->close();
    }
} else {
    echo "Erreur lors de la création de la base de données: " . $conn->error;
}

// Créer le dossier d'uploads s'il n'existe pas
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isEditor() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'editor';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}