<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Eco Group - Solutions Durables en RDC</title>
    <link rel="stylesheet" href="css/style3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <img src="assets/logo.png" alt="Impact Eco Group Logo">
                <h1>Impact Eco Group</h1>
            </div>
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul id="menu">
                    <li><a href="#home">Accueil</a></li>
                    <li><a href="#about">À Propos</a></li>
                    <li><a href="#objectives">Objectifs</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#projects">Projets</a></li>
                    <li><a href="#blog">Blog</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="admin/login.php" class="btn-login">Connexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h2>Solutions Durables pour un Avenir Meilleur</h2>
            <p>Impact Eco Group s'engage à apporter des solutions durables aux enjeux de développement en République Démocratique du Congo, en alliant croissance économique et protection de l'environnement.</p>
            <a href="#about" class="btn">Découvrir</a>
        </div>
    </section>

    <!-- À Propos Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-title">
                <h2>À Propos de Nous</h2>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <p>Impact Eco Group a été fondé pour répondre aux défis environnementaux et de développement en République Démocratique du Congo (RDC), un pays riche en ressources naturelles mais confronté à la déforestation, à la perte de biodiversité et à une gestion inappropriée de ses ressources.</p>
                    <p>Nos motivations incluent la préservation de l'environnement, le développement durable, l'éducation et la sensibilisation, ainsi que le soutien aux communautés locales.</p>
                    <p>Nous nous positionnons comme un défenseur de l'environnement tout en jouant un rôle clé dans le développement socio-économique durable en RDC, créant un équilibre entre prospérité économique et protection de l'environnement.</p>
                </div>
                <div class="about-image">
                    <img src="assets/images/communaute2.jpg" alt="Communauté locale en RDC">
                </div>
            </div>
        </div>
    </section>

    <!-- Objectifs Section -->
    <section id="objectives" class="objectives">
        <div class="container">
            <div class="section-title">
                <h2>Nos Objectifs</h2>
            </div>
            <div class="objectives-grid">
                <div class="objective-card">
                    <i class="fas fa-tree fa-3x"></i>
                    <h3>Protection et Restauration des Écosystèmes</h3>
                    <p>Nous œuvrons pour préserver la biodiversité et restaurer les écosystèmes dégradés en RDC.</p>
                </div>
                <div class="objective-card">
                    <i class="fas fa-seedling fa-3x"></i>
                    <h3>Promotion de l'Agriculture Durable</h3>
                    <p>Nous encourageons des pratiques agricoles respectueuses de l'environnement et économiquement viables.</p>
                </div>
                <div class="objective-card">
                    <i class="fas fa-solar-panel fa-3x"></i>
                    <h3>Développement de Projets d'Énergie Renouvelable</h3>
                    <p>Nous investissons dans des solutions énergétiques propres et accessibles pour les communautés locales.</p>
                </div>
                <div class="objective-card">
                    <i class="fas fa-graduation-cap fa-3x"></i>
                    <h3>Éducation et Formation</h3>
                    <p>Nous sensibilisons et formons les populations aux enjeux environnementaux et au développement durable.</p>
                </div>
                <div class="objective-card">
                    <i class="fas fa-handshake fa-3x"></i>
                    <h3>Engagement et Partenariat</h3>
                    <p>Nous collaborons avec diverses parties prenantes pour maximiser notre impact positif sur l'environnement et les communautés.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-title">
                <h2>Nos Services</h2>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <img src="assets/images/agriculture1.jpg" alt="Agriculture Durable">
                    <div class="service-content">
                        <h3>Agriculture Durable</h3>
                        <p>Nous promouvons des pratiques agricoles durables qui préservent les sols et augmentent les rendements, tout en réduisant l'impact environnemental.</p>
                        <a href="agriculture.php" class="btn" style="margin-top: 1rem;">En savoir plus</a>
                    </div>
                </div>
                <div class="service-card">
                    <img src="assets/images/energie_renouvelable.jpeg" alt="Énergie Renouvelable">
                    <div class="service-content">
                        <h3>Énergie Renouvelable</h3>
                        <p>Développement de projets d'énergie renouvelable pour réduire la dépendance aux combustibles fossiles et améliorer l'accès à l'électricité.</p>
                        <a href="energie_renouvelable.php" class="btn" style="margin-top: 1rem;">En savoir plus</a>
                    </div>
                </div>
                <div class="service-card">
                    <img src="assets/images/school.jpg" alt="Éducation et Formation">
                    <div class="service-content">
                        <h3>Éducation et Formation</h3>
                        <p>Nous informons et formons les communautés sur les enjeux environnementaux pour encourager des comportements durables à long terme.</p>
                        <a href="education_formation.php" class="btn" style="margin-top: 1rem;">En savoir plus</a>
                    </div>
                </div>
                <div class="service-card">
                    <img src="assets/images/finance.jpg" alt="Finance Durable">
                    <div class="service-content">
                        <h3>Finance Durable</h3>
                        <p>Création de mécanismes de financement pour soutenir les PME adoptant des pratiques respectueuses de l'environnement.</p>
                        <a href="finance.php" class="btn" style="margin-top: 1rem;">En savoir plus</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Projets Section -->
    <section id="projects" class="projects">
        <div class="container">
            <div class="section-title">
                <h2>Nos Projets</h2>
            </div>
            <div class="projects-grid">
                <div class="project-card">
                    <img src="assets/images/diamant.jpg" alt="Projet Éco-village à Luiza">
                    <div class="project-overlay">
                        <h3>Éco-village à Luiza</h3>
                        <p>Un modèle de développement durable pour l'exploitation responsable des ressources minières.</p>
                    </div>
                </div>
                <div class="project-card">
                    <img src="assets/images/semence.jpg" alt="Distribution de Semences Améliorées">
                    <div class="project-overlay">
                        <h3>Semences Améliorées</h3>
                        <p>Distribution de semences adaptées pour promouvoir une agriculture durable.</p>
                    </div>
                </div>
                <div class="project-card">
                    <img src="assets/images/football_enfants.jpg" alt="Programme Éducatif">
                    <div class="project-overlay">
                        <h3>Programme Éducatif</h3>
                        <p>Incitation des enfants à retourner à l'école et promotion d'activités ludiques comme le football.</p>
                    </div>
                </div>
                <div class="project-card">
                    <img src="assets/images/corruption.jpg" alt="Formation Anti-corruption">
                    <div class="project-overlay">
                        <h3>Lutte contre la Corruption</h3>
                        <p>Formation sur les mécanismes de prévention de la corruption pour un environnement favorable à la finance durable.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Témoignages Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Témoignages</h2>
            </div>
            <div class="testimonial-slider">
                <div class="testimonial-card">
                    <p>"Grâce à Impact Eco Group, notre communauté a pu adopter des pratiques agricoles durables qui ont considérablement amélioré nos rendements tout en préservant nos terres."</p>
                    <div class="testimonial-author">Jean Mutombo, Agriculteur à Kananga</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section id="blog" class="blog">
        <div class="container">
            <div class="section-title">
                <h2>Articles Récents</h2>
            </div>
            <div class="blog-grid">
                <?php
                require_once 'admin/includes/config.php';
                $sql = "SELECT a.*, c.name as category_name, u.full_name as author_name 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        WHERE a.status = 'published' 
                        ORDER BY a.created_at DESC LIMIT 3";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($article = $result->fetch_assoc()) {
                        echo '<div class="blog-card">
                            <div class="blog-image-container">';
                        if ($article['featured_image']) {
                            echo '<img src="' . UPLOAD_URL . '/' . htmlspecialchars($article['featured_image']) . '" alt="' . htmlspecialchars($article['title']) . '">
                            </div>';
                        }
                        echo '<div class="blog-content">
                            <div class="blog-meta">
                              Par ' . htmlspecialchars($article['author_name']) . '
                                dans  ' . htmlspecialchars($article['category_name']) . '
                            le ' . date('d/m/Y', strtotime($article['created_at'])) . '
                            </div>';
                        echo '<h3>' . htmlspecialchars($article['title']) . '</h3>';

                        echo '<p>' . htmlspecialchars($article['excerpt']) . '</p>';
                        echo '<a href="blog.php?article=' . htmlspecialchars($article['slug']) . '" class="btn">Lire la suite</a>';
                        echo '</div></div>';
                    }
                } else {
                    echo '<p>Aucun article disponible pour le moment.</p>';
                }
                ?>
            </div>
            <div class="text-center" style="margin-top: 2rem;">
                <a href="blog.php" class="btn">Voir tous les articles</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-title">
                <h2>Contactez-Nous</h2>
            </div>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Nos Coordonnées</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Kananga, Province du Kasaï-Central, RDC</p>
                    <p><i class="fas fa-envelope"></i> info@impactecogroup.org</p>
                    <p><i class="fas fa-phone"></i> +243 123 456 789</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <?php if (isset($_GET['message']) && $_GET['message'] === 'success'): ?>
                        <div class="alert alert-success">
                            <p>Votre message a été envoyé avec succès! Nous vous répondrons dans les plus brefs délais.</p>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['errors'])): ?>
                        <div class="alert alert-error">
                            <p>Veuillez corriger les erreurs dans le formulaire.</p>
                        </div>
                        <?php 
                            $errors = json_decode(urldecode($_GET['errors']), true);
                        ?>
                    <?php endif; ?>
                    <form action="process_form.php" method="POST">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                            <?php if (isset($errors['nameErr']) && !empty($errors['nameErr'])): ?>
                                <div class="error-message"><?php echo $errors['nameErr']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="exemple@gmail.com" required>
                            <?php if (isset($errors['emailErr']) && !empty($errors['emailErr'])): ?>
                                <div class="error-message"><?php echo $errors['emailErr']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone (optionnel)</label>
                            <input type="tel" id="phone" name="phone" class="form-control" placeholder="+243 123 456 789">
                            <?php if (isset($errors['phoneErr']) && !empty($errors['phoneErr'])): ?>
                                <div class="error-message"><?php echo $errors['phoneErr']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="subject">Sujet</label>
                            <input type="text" id="subject" name="subject" class="form-control" required>
                            <?php if (isset($errors['subjectErr']) && !empty($errors['subjectErr'])): ?>
                                <div class="error-message"><?php echo $errors['subjectErr']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" required></textarea>
                            <?php if (isset($errors['messageErr']) && !empty($errors['messageErr'])): ?>
                                <div class="error-message"><?php echo $errors['messageErr']; ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn" >Envoyer</button> 
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-container">
                <div class="footer-col">
                    <h3>Impact Eco Group</h3>
                    <p>Solutions durables pour un avenir meilleur en République Démocratique du Congo.</p>
                </div>
                <div class="footer-col">
                    <h3>Liens Rapides</h3>
                    <ul>
                        <li><a href="#home">Accueil</a></li>
                        <li><a href="#about">À Propos</a></li>
                        <li><a href="#objectives">Objectifs</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#projects">Projets</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Nos Activités</h3>
                    <ul>
                        <li><a href="agriculture.php">Agriculture Durable</a></li>
                        <li><a href="energie_renouvelable.php">Énergie Renouvelable</a></li>
                        <li><a href="education_formation.php">Éducation et Formation</a></li>
                        <li><a href="finance.php">Finance Durable</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Nos Partenaires</h3>
                    <ul class="partners-list">
                        <li><a href="#">ISIG-Goma</a></li>
                        <li><a href="#">Couleurs Afrique</a></li>
                        <li><a href="#">CFPI</a></li>
                    </ul>
                </div>
            </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Impact Eco Group. Tous droits réservés. Conçu par Dekens Ruzuba</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    <script src="js/form-validation.js"></script>
    <script src="js/phone-validation.js"></script>
</body>
</html>