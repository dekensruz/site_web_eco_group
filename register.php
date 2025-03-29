<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = clean($_POST['email']);
    $full_name = clean($_POST['full_name']);
    
    // Validation des champs
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($full_name)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse e-mail invalide';
    } else {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Ce nom d\'utilisateur ou cette adresse e-mail est déjà utilisé';
        } else {
            // Enregistrer l'utilisateur avec le rôle 'visitor' et statut 'active'
            $token = registerUser($username, $password, $email, $full_name, 'visitor');
            
            if ($token) {
                $_SESSION['success'] = 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.';
                redirect('admin/login.php');
            } else {
                $error = 'Une erreur est survenue lors de la création de votre compte';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Impact Eco Group</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #f57c00;
            --accent-color: #1565c0;
            --light-color: #f5f5f5;
        }
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Roboto', sans-serif;
        }
        .register-container {
            max-width: 600px;
            width: 90%;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            transition: box-shadow 0.3s ease;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @media (max-width: 768px) {
            .register-container {
                width: 95%;
                padding: 2rem;
            }
            .register-container h1 {
                font-size: 1.8rem;
            }
            .form-group input {
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            .register-container {
                width: 100%;
                padding: 1.5rem;
                border-radius: 10px;
            }
            .register-container h1 {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }
            .form-group {
                margin-bottom: 1rem;
            }
            .form-group label {
                font-size: 0.9rem;
            }
            .form-group input {
                padding: 0.6rem 0.8rem;
            }
            .btn-register {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
            .login-link {
                margin-top: 1rem;
                font-size: 0.9rem;
            }
        }
        .register-container:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        .register-container h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
            outline: none;
        }
        .btn-register {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background-color: #1b5e20;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(27, 94, 32, 0.2);
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-weight: 500;
        }
        .alert-danger {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .login-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <main class="register-container">
        <h1>Créer un compte</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="full_name">Nom complet</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-register">S'inscrire</button>
            </form>
        <?php endif; ?>
        
        <div class="login-link">
            <p>Vous avez déjà un compte ? <a href="admin/login.php">Connectez-vous</a></p>
        </div>
    </main>

    <script>
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('#menu').classList.toggle('active');
        });
    </script>
</body>
</html>