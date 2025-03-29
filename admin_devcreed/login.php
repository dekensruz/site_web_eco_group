<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Traitement du formulaire de connexion et d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'register') {
            // Traitement de l'inscription
            $username = trim($_POST['username']);
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $errors = [];

            // Validation des champs
            if (empty($username)) {
                $errors[] = "Le nom d'utilisateur est requis";
            }
            if (empty($full_name)) {
                $errors[] = "Le nom complet est requis";
            }
            if (empty($email)) {
                $errors[] = "L'email est requis";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email n'est pas valide";
            }
            if (empty($password)) {
                $errors[] = "Le mot de passe est requis";
            } elseif (strlen($password) < 6) {
                $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
            }

            // Vérifier si l'email ou le nom d'utilisateur existe déjà
            $sql = "SELECT id FROM users WHERE email = ? OR username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Cet email ou nom d'utilisateur existe déjà";
            }

            if (empty($errors)) {
                // Hasher le mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer le nouvel utilisateur
                $sql = "INSERT INTO users (username, full_name, email, password, role, email_verified) VALUES (?, ?, ?, ?, 'visitor', 0)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $username, $full_name, $email, $hashed_password);
                
                if ($stmt->execute()) {
                    // Connexion automatique après l'inscription
                    $_SESSION['user_id'] = $stmt->insert_id;
                    $_SESSION['user_name'] = $username;
                    $_SESSION['user_role'] = 'visitor';
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['email'] = $email;
                    redirect('../index.php');
                } else {
                    $errors[] = "Une erreur est survenue lors de l'inscription";
                }
            }
        } elseif ($_POST['action'] === 'login') {
            // Traitement de la connexion
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $errors = [];

            // Validation des champs
            if (empty($email)) {
                $errors[] = "L'email est requis";
            }
            if (empty($password)) {
                $errors[] = "Le mot de passe est requis";
            }

            // Si pas d'erreurs, tenter la connexion
            if (empty($errors)) {
                $sql = "SELECT * FROM users WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        // Connexion réussie
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['username'] ?? '';
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['full_name'] = $user['full_name'] ?? $user['username'] ?? 'Utilisateur';
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['success'] = "Connexion réussie ! Bienvenue {$user['full_name']}";
                        if ($user['role'] === 'admin' || $user['role'] === 'editor') {
                            redirect('index.php');
                        } else {
                            redirect('../index.php');
                        }
                    } else {
                        $errors[] = "Email ou mot de passe incorrect";
                    }
                } else {
                    $errors[] = "Email ou mot de passe incorrect";
                }
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
    <title>Connexion - Devcreed Academy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .auth-tabs .nav-link {
            color: var(--text-color);
            border: none;
            border-bottom: 2px solid transparent;
            padding: 1rem;
            font-weight: 500;
        }
        .auth-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            background: none;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="../images/devcreed.png" alt="Devcreed Academy" height="60" class="mb-3">
                        </div>

                        <ul class="nav nav-tabs auth-tabs mb-4" id="authTabs" role="tablist">
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link active w-100" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">Connexion</button>
                            </li>
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link w-100" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">Inscription</button>
                            </li>
                        </ul>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="tab-content" id="authTabsContent">
                            <div class="tab-pane fade show active" id="login" role="tabpanel">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="login">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Mot de passe</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                    </button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="register" role="tabpanel">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="register">
                                    <div class="mb-3">
                                        <label for="reg-username" class="form-label">Nom d'utilisateur</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="reg-username" name="username" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reg-fullname" class="form-label">Nom complet</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                            <input type="text" class="form-control" id="reg-fullname" name="full_name" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reg-email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="reg-email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="reg-password" class="form-label">Mot de passe</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="reg-password" name="password" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="fas fa-user-plus me-2"></i>S'inscrire
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-muted">
                                <i class="fas fa-arrow-left me-1"></i>Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>