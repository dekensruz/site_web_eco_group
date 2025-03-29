<?php
// En-tête commun pour toutes les pages
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Impact Eco Group</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <style>
        
    header {
    background-color: #2e7d32;
    color: white;
    padding: 1rem 0;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    display: flex;
    align-items: center;
}

.logo img {
    height: 50px;
    margin-right: 10px;
}

.logo h1 {
    font-size: 1.5rem;
    font-weight: 700;
}

/* Navigation */
nav ul {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
}

nav ul li {
    margin-left: auto;
}

nav ul li a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: block;
    padding: 0.5rem 1rem;
}

nav ul li a:hover {
    color: #f57c00;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.3s ease;
}

.mobile-menu-btn:hover {
    color: #f57c00;
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
        z-index: 1000;
    }
    nav ul {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        background-color: #2e7d32;
        flex-direction: column;
        align-items: center;
        padding: 1rem 0;
        transform: translateY(-150%);
        opacity: 0;
        visibility: hidden;
        transition: transform 0.4s ease, opacity 0.3s ease;
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
        padding: 0.8rem 1rem;
        font-size: 1.1rem;
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
}}
</style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const menu = document.querySelector('#menu');
        
        mobileMenuBtn.addEventListener('click', function() {
            menu.classList.toggle('active');
        });
    });
    </script>
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
