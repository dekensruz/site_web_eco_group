<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

$error = '';
$success = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    if (verifyUserEmail($token)) {
        $success = 'Votre adresse e-mail a été vérifiée avec succès. Vous pouvez maintenant vous connecter.';
    } else {
        $error = 'Le lien de vérification est invalide ou a expiré.';
    }
} else {
    $error = 'Lien de vérification invalide.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de compte - Impact Eco Group</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .verify-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .btn-login {
            display: inline-block;
            background: #4CAF50;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            margin-top: 1rem;
        }
        .btn-login:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/logo.png" alt="Impact Eco Group Logo">
                    <h1>Impact Eco Group</h1>
                </a>
            </div>
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul id="menu">
                    <li><a href="index.php#home">Accueil</a></li>
                    <li><a href="index.php#about">À Propos</a></li>
                    <li><a href="index.php#objectives">Objectifs</a></li>
                    <li><a href="index.php#services">Services</a></li>
                    <li><a href="index.php#projects">Projets</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                    <li><a href="admin/login.php" class="btn-login">Connexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="verify-container">
        <h1>Vérification de compte</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <a href="admin/login.php" class="btn-login">Se connecter</a>
        <?php endif; ?>
    </main>

    <script>
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('#menu').classList.toggle('active');
        });
    </script>
</body>
</html>