<?php
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
checkAccess();

// Vérifier si un fichier a été envoyé
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    // Utiliser la fonction uploadImage du fichier functions.php
    $uploaded_file = uploadImage($_FILES['file'], 'articles');
    
    if ($uploaded_file) {
        // Succès : renvoyer l'URL de l'image au format attendu par TinyMCE
        // Le chemin $uploaded_file contient déjà le sous-dossier (ex: 'articles/image.jpg')
        // On doit construire correctement l'URL sans double slash
        $response = [
            'location' => UPLOAD_URL . '/' . $uploaded_file
        ];
        
        // Pour déboguer, on peut vérifier le chemin complet
        $full_path = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/uploads/' . $uploaded_file;
        error_log('Chemin physique du fichier: ' . $full_path);
        error_log('URL de l\'image: ' . $response['location']);
        error_log('Fichier existe: ' . (file_exists($full_path) ? 'Oui' : 'Non'));
        
        // Déboguer le chemin de l'image pour vérification dans les logs
        error_log('Chemin de l\'image uploadée: ' . UPLOAD_URL . '/' . $uploaded_file);
        
        // Renvoyer la réponse au format JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // Échec : renvoyer une erreur
        header('HTTP/1.1 500 Server Error');
        echo json_encode(['error' => 'Échec du téléchargement de l\'image']);
        exit;
    }
} else {
    // Aucun fichier envoyé ou erreur
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Aucun fichier envoyé ou erreur lors du téléchargement']);
    exit;
}