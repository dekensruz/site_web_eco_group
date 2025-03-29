<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/functions.php';

$articles_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $articles_per_page;

// Récupérer l'article spécifique si un slug est fourni
$single_article = null;
if (isset($_GET['article'])) {
    $slug = clean($_GET['article']);
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name, u.full_name as author_name 
                           FROM articles a 
                           LEFT JOIN categories c ON a.category_id = c.id 
                           LEFT JOIN users u ON a.author_id = u.id 
                           WHERE a.slug = ? AND a.status = 'published'");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $single_article = $result->fetch_assoc();
    }
    $stmt->close();
}

// Récupérer tous les articles pour la pagination
$sql = "SELECT a.*, c.name as category_name, u.full_name as author_name 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        LEFT JOIN users u ON a.author_id = u.id 
        WHERE a.status = 'published' 
        ORDER BY a.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $articles_per_page, $offset);
$stmt->execute();
$articles = $stmt->get_result();

// Compter le nombre total d'articles pour la pagination
$total_articles = $conn->query("SELECT COUNT(*) as count FROM articles WHERE status = 'published'")->fetch_assoc()['count'];
$total_pages = ceil($total_articles / $articles_per_page);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $single_article ? htmlspecialchars($single_article['title']) . ' - ' : ''; ?>Blog - Impact Eco Group</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --accent-color: #2196F3;
            --text-color: #333;
            --light-gray: #e0e0e0;
            --gray-color: #666;
            --white: #fff;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --transition: all 0.3s ease;
        }

        .blog-container {
            max-width: 1200px;
            margin: 6rem auto 2rem;
            padding: 0 1.5rem;
        }

        .blog-header {
            margin-bottom: 3rem;
            text-align: center;
        }

        .blog-header h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .blog-header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .blog-filters {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 2rem;
            padding: 1rem;
            background: var(--white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .search-form {
            flex: 1;
            min-width: 300px;
            max-width: 500px;
        }

        .search-box {
            display: flex;
            gap: 0.5rem;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid var(--light-gray);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-box input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: var(--shadow-sm);
        }

        .search-box button {
            padding: 0.8rem 1.5rem;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-box button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .category-filter select {
            padding: 1rem 2rem 1rem 1rem;
            border: 2px solid var(--light-gray);
            border-radius: var(--radius-md);
            font-size: 1rem;
            min-width: 200px;
            cursor: pointer;
            appearance: none;
            background: var(--white) url('data:image/svg+xml;utf8,<svg fill="black" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 0.5rem center;
            transition: var(--transition);
        }

        .category-filter select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: var(--shadow-sm);
        }

        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }

        .blog-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .blog-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: var(--transition);
        }

        .blog-card:hover img {
            transform: scale(1.05);
        }

        .blog-content {
            padding: 2rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .blog-content h2 {
            font-size: 1.5rem;
            color: var(--text-color);
            margin: 0;
            line-height: 1.4;
        }

        .blog-meta {
            color: var(--gray-color);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .blog-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .blog-meta i {
            color: var(--primary-color);
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: var(--transition);
            text-align: center;
            margin-top: auto;
            border: 2px solid var(--primary-color);
        }

        .btn:hover {
            background: transparent;
            color: var(--primary-color);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 3rem;
        }

        .pagination a, .pagination .current {
            padding: 0.8rem 1.2rem;
            border-radius: var(--radius-md);
            transition: var(--transition);
            min-width: 40px;
            text-align: center;
        }

        .pagination a {
            background: var(--white);
            color: var(--text-color);
            text-decoration: none;
            box-shadow: var(--shadow-sm);
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .pagination .current {
            background: var(--primary-color);
            color: var(--white);
            font-weight: bold;
        }

        .single-article {
            max-width: 800px;
            margin: 0 auto;
            padding: 2.5rem;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }

        .single-article h1 {
            font-size: 2.5rem;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            line-height: 1.3;
        }

        .single-article img {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: var(--radius-lg);
            margin: 2rem 0;
            box-shadow: var(--shadow-md);
        }

        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-color);
        }

        .back-to-blog {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
            padding: 0.8rem 1.5rem;
            background: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: var(--transition);
            border: 2px solid var(--primary-color);
        }

        .back-to-blog:hover {
            background: transparent;
            color: var(--primary-color);
        }

        .comments-section {
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 2px solid var(--light-gray);
        }

        .comments-section h3 {
            font-size: 1.8rem;
            color: var(--text-color);
            margin-bottom: 2rem;
        }

        .comment-form {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: var(--radius-md);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--light-gray);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: var(--shadow-sm);
        }

        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .comment {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .comment-content {
            color: var(--text-color);
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .blog-header h1 {
                font-size: 2rem;
            }

            .blog-grid {
                grid-template-columns: 1fr;
            }

            .single-article {
                padding: 1.5rem;
            }

            .single-article h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <main class="blog-container">
        <?php
        // Récupérer toutes les catégories
        $categories_sql = "SELECT * FROM categories ORDER BY name ASC";
        $categories_result = $conn->query($categories_sql);

        // Récupérer la catégorie sélectionnée
        $selected_category = isset($_GET['category']) ? (int)$_GET['category'] : null;

        // Modifier la requête SQL pour filtrer par catégorie si nécessaire
        $where_clause = "WHERE a.status = 'published'";
        if ($selected_category) {
            $where_clause .= " AND a.category_id = " . (int)$selected_category;
        }

        // Recherche
        $search_query = isset($_GET['search']) ? clean($_GET['search']) : '';
        if ($search_query) {
            $search_term = $conn->real_escape_string($search_query);
            $where_clause .= " AND (a.title LIKE '%{$search_term}%' OR a.content LIKE '%{$search_term}%')";
        }

        // Mettre à jour la requête principale avec les filtres
        $sql = "SELECT a.*, c.name as category_name, u.full_name as author_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                LEFT JOIN users u ON a.author_id = u.id 
                {$where_clause} 
                ORDER BY a.created_at DESC 
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $articles_per_page, $offset);
        $stmt->execute();
        $articles = $stmt->get_result();
        ?>

        <?php if ($single_article): ?>
            <a href="blog.php" class="back-to-blog"><i class="fas fa-arrow-left"></i> Retour au blog</a>
            <article class="single-article">
                <h1><?php echo htmlspecialchars($single_article['title']); ?></h1>
                <p class="blog-meta">
                    Par <?php echo htmlspecialchars($single_article['author_name']); ?> 
                    dans <?php echo htmlspecialchars($single_article['category_name']); ?> 
                    le <?php echo date('d/m/Y', strtotime($single_article['created_at'])); ?>
                </p>
                <?php if ($single_article['featured_image']): ?>
                    <img src="<?php echo UPLOAD_URL . '/' . basename(htmlspecialchars($single_article['featured_image'])); ?>" 
                         alt="<?php echo htmlspecialchars($single_article['title']); ?>">
                <?php endif; ?>
                <div class="article-content">
                    <?php echo $single_article['content']; ?>
                </div>
                
                <!-- Section des commentaires -->
                <div class="comments-section mt-5">
                    <h3>Commentaires</h3>
                    
                    <?php
                    // Récupérer les commentaires approuvés pour cet article
                    $comments_sql = "SELECT c.*, u.full_name as user_name 
                                    FROM comments c 
                                    LEFT JOIN users u ON c.user_id = u.id 
                                    WHERE c.article_id = ? AND c.status = 'approved' 
                                    ORDER BY c.created_at DESC";
                    $comments_stmt = $conn->prepare($comments_sql);
                    $comments_stmt->bind_param("i", $single_article['id']);
                    $comments_stmt->execute();
                    $comments = $comments_stmt->get_result();
                    ?>
                    
                    <!-- Formulaire de commentaire -->
                    <div class="comment-form mb-4">
                        <?php if (isLoggedIn()): ?>
                            <form action="post-comment.php" method="POST" class="mt-3">
                                <input type="hidden" name="article_id" value="<?php echo $single_article['id']; ?>">
                                <div class="form-group">
                                    <label for="comment">Votre commentaire</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Publier le commentaire</button>
                            </form>
                        <?php else: ?>
                            <p>Veuillez vous <a href="register.php">inscrire</a> ou vous <a href="admin/login.php">connecter</a> pour laisser un commentaire.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Liste des commentaires -->
                    <div class="comments-list">
                        <?php if ($comments->num_rows > 0): ?>
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                                <div class="comment mb-3 p-3 border rounded">
                                    <div class="comment-header d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></small>
                                    </div>
                                    <div class="comment-content mt-2">
                                        <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php else: ?>
            <div class="blog-header">
                <h1>Notre Blog</h1>
                <div class="blog-filters">
                    <form action="blog.php" method="GET" class="search-form">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Rechercher un article..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    <div class="category-filter">
                        <select name="category" onchange="window.location.href=this.value">
                            <option value="blog.php">Toutes les catégories</option>
                            <?php while($cat = $categories_result->fetch_assoc()): ?>
                                <option value="blog.php?category=<?php echo $cat['id']; ?>" <?php echo ($selected_category == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="blog-grid">
                <?php while ($article = $articles->fetch_assoc()): ?>
                    <div class="blog-card">
                        <?php if ($article['featured_image']): ?>
                            <img src="<?php echo UPLOAD_URL . '/' . basename(htmlspecialchars($article['featured_image'])); ?>" 
                                 alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <?php endif; ?>
                        <div class="blog-content">
                            <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                            <p class="blog-meta">
                                Par <?php echo htmlspecialchars($article['author_name']); ?> 
                                dans <?php echo htmlspecialchars($article['category_name']); ?>
                            </p>
                            <p><?php echo htmlspecialchars($article['excerpt']); ?></p>
                            <a href="?article=<?php echo htmlspecialchars($article['slug']); ?>" class="btn">Lire la suite</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    <!-- JavaScript -->
    <script src="js/main.js"></script>
    <script>
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('#menu').classList.toggle('active');
        });
    </script>
</body>
</html>