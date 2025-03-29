<?php
require_once 'includes/config.php';

// Détruire toutes les variables de session
session_unset();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
redirect(ADMIN_URL . '/login.php');