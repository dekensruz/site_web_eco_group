<?php
// Inclure les fichiers de configuration
require_once 'db/config.php';
require_once 'smtp_config.php';

// Initialisation des variables d'erreur
$nameErr = $emailErr = $phoneErr = $subjectErr = $messageErr = "";
$name = $email = $phone = $subject = $message = "";
$success = false;

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validation du nom
    if (empty($_POST["name"])) {
        $nameErr = "Le nom est requis";
    } else {
        $name = test_input($_POST["name"]);
        // Vérifier si le nom contient uniquement des lettres et des espaces
        if (!preg_match("/^[a-zA-ZÀ-ÿ ]*$/", $name)) {
            $nameErr = "Seuls les lettres et les espaces sont autorisés";
        }
    }

    // Validation de l'email
    if (empty($_POST["email"])) {
        $emailErr = "L'email est requis";
    } else {
        $email = test_input($_POST["email"]);
        // Vérifier si l'adresse e-mail est valide
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Format d'email invalide";
        }
    }
    
    // Validation du téléphone (optionnel)
    if (!empty($_POST["phone"])) {
        $phone = test_input($_POST["phone"]);
        // Vérifier si le numéro de téléphone contient au moins 8 chiffres
        $digitsOnly = preg_replace("/[^0-9]/", "", $phone);
        if (strlen($digitsOnly) < 8) {
            $phoneErr = "Le numéro de téléphone doit contenir au moins 8 chiffres";
        }
    }

    // Validation du sujet
    if (empty($_POST["subject"])) {
        $subjectErr = "Le sujet est requis";
    } else {
        $subject = test_input($_POST["subject"]);
    }

    // Validation du message
    if (empty($_POST["message"])) {
        $messageErr = "Le message est requis";
    } else {
        $message = test_input($_POST["message"]);
    }

    // Si aucune erreur, procéder à l'envoi du message
    if ($nameErr == "" && $emailErr == "" && $phoneErr == "" && $subjectErr == "" && $messageErr == "") {
        // Dans un environnement de production, vous pourriez également envoyer un email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $email . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $message_html = "<html><body>";
        $message_html .= "<p><strong>Nom:</strong> " . $name . "</p>";
        $message_html .= "<p><strong>Email:</strong> " . $email . "</p>";
        if (!empty($phone)) {
            $message_html .= "<p><strong>Téléphone:</strong> " . $phone . "</p>";
        }
        $message_html .= "<p><strong>Message:</strong><br>" . nl2br($message) . "</p>";
        $message_html .= "</body></html>";

        mail("ruzubadekens@gmail.com", "Contact: " . $subject, $message_html, $headers);
        
        try {
            // Enregistrer le message dans la base de données
            $stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            
            if ($stmt->execute()) {
                // Réinitialiser les variables
                $name = $email = $phone = $subject = $message = "";
                $success = true;
            } else {
                // En cas d'erreur lors de l'insertion
                echo "Erreur: " . $stmt->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            echo "Erreur: " . $e->getMessage();
        }
    }
}

// Fonction pour nettoyer les données
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Redirection vers la page d'accueil avec un message de succès
if ($success) {
    header("Location: index.php?message=success#contact");
    exit();
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Si des erreurs existent, rediriger avec les erreurs
    if ($nameErr != "" || $emailErr != "" || $phoneErr != "" || $subjectErr != "" || $messageErr != "") {
        $errors = array(
            'nameErr' => $nameErr,
            'emailErr' => $emailErr,
            'phoneErr' => $phoneErr,
            'subjectErr' => $subjectErr,
            'messageErr' => $messageErr
        );
        header("Location: index.php?errors=" . urlencode(json_encode($errors)) . "#contact");
        exit();
    }
}
?>