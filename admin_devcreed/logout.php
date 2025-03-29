<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Détruire toutes les variables de session
session_start();
session_unset();
session_destroy();

// Rediriger vers la page de connexion
redirect('login.php');