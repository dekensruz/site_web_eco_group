<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect(ADMIN_URL . '/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    if (authenticateUser($username, $password)) {
        redirect(ADMIN_URL . '/index.php');
    } else {
        $error = 'Nom d\'utilisateur ou mot de passe incorrect';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Administration Eco Group</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/admin/css/admin.css">
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #f57c00;
            --accent-color: #1565c0;
            --light-color: #f5f5f5;
        }
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 0 auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: box-shadow 0.3s ease;
        }
        .login-container:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .login-logo img {
            max-width: 180px;
            height: auto;
            transition: transform 0.3s ease;
            filter: brightness(0);
        }
        .login-logo img:hover {
            transform: scale(1.05);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            color: #333;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #1b5e20;
            box-shadow: 0 4px 12px rgba(27, 94, 32, 0.2);
        }
        .alert {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
            font-weight: 500;
        }
        .alert-danger {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .text-center a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .text-center a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <img src="<?php echo BASE_URL; ?>/assets/logo.png" alt="Eco Group Logo">
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur ou Email</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
            <div class="text-center mt-3">
                <p>Pas encore de compte ? <a href="../register.php">Inscrivez-vous ici</a></p>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>