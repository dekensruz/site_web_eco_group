<?php
// Inclure le fichier de configuration de la base de données
require_once 'db/config.php';

// Vérification de l'authentification (simple pour la démonstration)
$authenticated = false;
$error = "";

// Identifiants par défaut (dans un environnement de production, utilisez un système d'authentification plus sécurisé)
$default_username = "admin";
$default_password = "eco2023";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["username"]) && isset($_POST["password"])) {
        if ($_POST["username"] === $default_username && $_POST["password"] === $default_password) {
            $authenticated = true;
            // Définir une session pour maintenir l'authentification
            session_start();
            $_SESSION["authenticated"] = true;
        } else {
            $error = "Identifiants incorrects";
        }
    }
} else {
    // Vérifier si déjà authentifié via session
    session_start();
    if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"] === true) {
        $authenticated = true;
    }
}

// Fonction pour se déconnecter
if (isset($_GET["logout"])) {
    session_start();
    session_unset();
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Récupérer les messages de la base de données si authentifié
$messages = array();
if ($authenticated) {
    $sql = "SELECT * FROM messages ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Impact Eco Group</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .login-form {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .message-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .message-table th, .message-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .message-table th {
            background-color: var(--primary-color);
            color: white;
            position: sticky;
            top: 0;
        }
        
        .message-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .admin-header h2 {
            margin: 0;
            color: var(--primary-color);
        }
        
        .logout-btn {
            background-color: var(--error-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: #c62828;
        }
        
        .message-detail {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            white-space: pre-wrap;
        }
        
        .logout-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .logout-link:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: var(--error-color);
            margin-bottom: 15px;
        }
        
        .no-messages {
            text-align: center;
            padding: 20px;
            color: var(--gray-color);
        }
        
        .toggle-message {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction pour basculer l'affichage des détails du message
            const toggleMessageDetails = function(id) {
                const detailElement = document.getElementById('message-detail-' + id);
                if (detailElement) {
                    if (detailElement.style.display === 'none' || detailElement.style.display === '') {
                        detailElement.style.display = 'block';
                        document.getElementById('toggle-btn-' + id).innerHTML = '<i class="fas fa-chevron-up"></i> Masquer';
                    } else {
                        detailElement.style.display = 'none';
                        document.getElementById('toggle-btn-' + id).innerHTML = '<i class="fas fa-chevron-down"></i> Voir plus';
                    }
                }
            };
            
            // Attacher l'événement aux boutons
            document.querySelectorAll('.toggle-message').forEach(button => {
                button.addEventListener('click', function() {
                    const messageId = this.getAttribute('data-id');
                    toggleMessageDetails(messageId);
                });
            });
        });
    </script>
</head>
<body>
    <div class="admin-container">
        <h1 style="text-align: center; color: var(--primary-color);">Administration - Impact Eco Group</h1>
        
        <?php if (!$authenticated): ?>
            <!-- Formulaire de connexion -->
            <div class="login-form">
                <h2>Connexion</h2>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="admin.php" method="POST">
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn">Se connecter</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Affichage des messages -->
            <div class="admin-header">
                <h2>Messages reçus</h2>
                <a href="admin.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a>
            </div>
            
            <?php if (empty($messages)): ?>
                <div class="no-messages">Aucun message reçu pour le moment.</div>
            <?php else: ?>
                <table class="message-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Sujet</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"><?php echo htmlspecialchars($message['email']); ?></a></td>
                                <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                <td>
                                    <button id="toggle-btn-<?php echo $message['id']; ?>" class="toggle-message" data-id="<?php echo $message['id']; ?>">
                                        <i class="fas fa-chevron-down"></i> Voir plus
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" style="padding: 0;">
                                    <div id="message-detail-<?php echo $message['id']; ?>" class="message-detail" style="display: none;">
                                        <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($message['phone'] ?: 'Non renseigné'); ?></p>
                                        <p><strong>Message:</strong></p>
                                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script>
        function toggleMessage(index) {
            const messageRow = document.getElementById(`message-${index}`);
            if (messageRow.style.display === 'none') {
                messageRow.style.display = 'table-row';
            } else {
                messageRow.style.display = 'none';
            }
        }
    </script>
</body>
</html>