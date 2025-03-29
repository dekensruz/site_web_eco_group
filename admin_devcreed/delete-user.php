<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier l'accès administrateur
checkAdminAccess();

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    redirect(ADMIN_URL . '/users.php');
}

// Vérifier le token CSRF
if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
    $_SESSION['error'] = "Token de sécurité invalide.";
    redirect(ADMIN_URL . '/users.php');
}

// Vérifier si l'ID de l'utilisateur est fourni
if (!isset($_POST['user_id'])) {
    $_SESSION['error'] = "ID de l'utilisateur manquant.";
    redirect(ADMIN_URL . '/users.php');
}

$userId = (int)$_POST['user_id'];

// Vérifier que l'utilisateur n'essaie pas de se supprimer lui-même
if ($userId === $_SESSION['user_id']) {
    $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte.";
    redirect(ADMIN_URL . '/users.php');
}

// Supprimer l'utilisateur
if (deleteUser($userId)) {
    $_SESSION['success'] = "L'utilisateur a été supprimé avec succès.";
} else {
    $_SESSION['error'] = "Une erreur est survenue lors de la suppression de l'utilisateur.";
}

redirect(ADMIN_URL . '/users.php');