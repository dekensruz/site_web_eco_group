<?php
// Pied de page commun pour toutes les pages
?>

<style>
    :root {
    --primary-color: #2e7d32; /* Vert forêt - représente l'environnement et la durabilité */
    --secondary-color: #f57c00; /* Orange - représente l'énergie et la vitalité */
    --accent-color: #1565c0; /* Bleu - représente l'eau et la confiance */
    --light-color: #f5f5f5; /* Blanc cassé pour le fond */
    --dark-color: #212121; /* Presque noir pour le texte */
    --success-color: #4caf50; /* Vert clair pour les messages de succès */
    --warning-color: #ff9800; /* Orange pour les avertissements */
    --error-color: #f44336; /* Rouge pour les erreurs */
    --gray-color: #757575; /* Gris pour les textes secondaires */
    --light-gray: #e0e0e0; /* Gris clair pour les bordures */
}
/* Footer */
footer {
    background-color: var(--dark-color);
    color: white;
    padding: 3rem 0;
}

.footer-container {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
}

.footer-col {
    flex: 1;
    min-width: 200px;
}

.footer-col h3 {
    color: var(--secondary-color);
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
}

.footer-col ul {
    list-style: none;
}

.footer-col ul li {
    margin-bottom: 0.8rem;
}

.footer-col ul li a {
    color: #bdbdbd;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-col ul li a:hover {
    color: white;
}

.social-links {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: #424242;
    color: white;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.social-links a:hover {
    background-color: var(--secondary-color);
}

.copyright {
    text-align: center;
    padding-top: 2rem;
    margin-top: 2rem;
    border-top: 1px solid #424242;
    color: #bdbdbd;
}

/* Media Queries pour Responsive Design */
@media (max-width: 1200px) {
    .container {
        max-width: 960px;
    }
    .blog-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}

@media (max-width: 992px) {
   
    .footer-container {
        gap: 1.5rem;
   
}

@media (max-width: 768px) {
    .container {
        max-width: 540px;
        padding: 0 15px;
    }
    .mobile-menu-btn {
        display: block;
        padding: 8px;
        margin-right: 5px;
    }
    nav ul {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        background-color: var(--primary-color);
        flex-direction: column;
        align-items: center;
        padding: 1rem 0;
        transform: translateY(-150%);
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s ease;
        z-index: 999;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    nav ul#menu.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
        display: flex;
    }
    nav ul li {
        margin: 0.8rem 0;
        width: 100%;
        text-align: center;
    }
    nav ul li a {
        display: block;
        padding: 0.5rem 1rem;
    }
    .logo img {
        height: 40px;
    }
    .logo h1 {
        font-size: 1.2rem;
    }
    .footer-container {
        flex-direction: column;
        gap: 2rem;
        text-align: center;
    }
    .footer-col {
        min-width: 100%;
    }
    .social-links {
        justify-content: center;
        margin-top: 1.5rem;
    }
    .copyright {
        margin-top: 2rem;
        padding-top: 1.5rem;
    }
}
</style>
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
                    <li><a href="index.php#about">À Propos</a></li>
                    <li><a href="index.php#services">Services</a></li>
                    <li><a href="index.php#projects">Projets</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
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
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Impact Eco Group. Tous droits réservés.</p>
            </div>
        </div>
    </footer>