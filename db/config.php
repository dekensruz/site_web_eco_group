<?php
// Configuration de la base de données
$servername = "localhost";
$username = "root"; // Nom d'utilisateur par défaut de XAMPP
$password = ""; // Mot de passe par défaut de XAMPP (vide)
$dbname = "eco_group_db";

// Création de la connexion
$conn = new mysqli($servername, $username, $password);

// Vérification de la connexion
if ($conn->connect_error) {
    die("La connexion a échoué: " . $conn->connect_error);
}

// Création de la base de données si elle n'existe pas
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Sélection de la base de données
    $conn->select_db($dbname);
    
    // Création de la table messages si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) !== TRUE) {
        echo "Erreur lors de la création de la table: " . $conn->error;
    }
} else {
    echo "Erreur lors de la création de la base de données: " . $conn->error;
}
?>