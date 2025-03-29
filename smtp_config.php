<?php
// Configuration SMTP pour le serveur local
ini_set('SMTP', 'smtp.gmail.com');
ini_set('smtp_port', 587);
ini_set('sendmail_from', 'ruzubadekens@gmail.com');
ini_set('smtp_ssl', 'tls');
ini_set('smtp_auth', true);
ini_set('smtp_username', 'ruzubadekens@gmail.com');
ini_set('smtp_password', 'votre_mot_de_passe');

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>