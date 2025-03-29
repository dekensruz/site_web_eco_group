<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    http_response_code(401);
    die('Non autorisé');
}

// Vérifier si un fichier a été uploadé
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
    http_response_code(400);
    die(json_encode(['error' => ['message' => 'Aucun fichier uploadé ou erreur lors du téléchargement']]));
}

$file = $_FILES['file'];

// Vérifier le type MIME
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    die(json_encode(['error' => ['message' => 'Type de fichier non autorisé']]));
}

// Vérifier la taille (max 5MB)
if ($file['size'] > 5000000) {
    http_response_code(400);
    die(json_encode(['error' => ['message' => 'Le fichier est trop volumineux (max 5MB)']])); 
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
    // Retourner l'URL de l'image pour TinyMCE avec la structure correcte
    echo json_encode([
        'location' => UPLOAD_URL . '/' . $filename,
        'data' => [
            'src' => UPLOAD_URL . '/' . $filename
        ]
    ]);
} else {
    http_response_code(500);
    die(json_encode(['error' => ['message' => 'Erreur lors du déplacement du fichier']]));
}