<?php
require_once 'includes/functions.php';

// Vérifier l'accès administrateur
checkAdminAccess();

// Traitement des actions
if (isset($_POST['action'])) {
    $action = clean($_POST['action']);
    
    if ($action === 'add' || $action === 'edit') {
        // Récupérer les données du formulaire
        $data = [
            'full_name' => clean($_POST['full_name']),
            'role' => clean($_POST['role']),
            'description' => clean($_POST['description']),
            'email' => clean($_POST['email']),
            'phone' => clean($_POST['phone']),
            'github_url' => clean($_POST['github_url']),
            'linkedin_url' => clean($_POST['linkedin_url']),
            'twitter_url' => clean($_POST['twitter_url']),
            'facebook_url' => clean($_POST['facebook_url']),
            'youtube_url' => clean($_POST['youtube_url'])
        ];
        
        // Traiter l'image si elle est fournie
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image = uploadImage($_FILES['image'], 'team');
            if ($image) {
                $data['image'] = $image;
            }
        }
        
        if ($action === 'add') {
            if (addTeamMember($data)) {
                $_SESSION['success'] = "Le membre a été ajouté avec succès.";
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de l'ajout du membre.";
            }
        } else {
            $id = (int)$_POST['id'];
            if (updateTeamMember($id, $data)) {
                $_SESSION['success'] = "Le membre a été mis à jour avec succès.";
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de la mise à jour du membre.";
            }
        }
        
        redirect(ADMIN_URL . '/team.php');
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if (deleteTeamMember($id)) {
            $_SESSION['success'] = "Le membre a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de la suppression du membre.";
        }
        redirect(ADMIN_URL . '/team.php');
    }
}

// Récupérer tous les membres de l'équipe
$team_members = getTeamMembers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de l'équipe - Administration Devcreed Academy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="shortcut icon" href="../images/devcreed.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="navbar-brand">
                <img src="../images/devcreed.png" alt="Devcreed Academy">
                <span>Devcreed Admin</span>
            </div>
            <ul class="nav flex-column mt-4">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="articles.php">
                        <i class="fas fa-newspaper"></i>
                        <span>Articles</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags"></i>
                        <span>Catégories</span>
                    </a>
                </li>
                <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="team.php">
                        <i class="fas fa-user-friends"></i>
                        <span>Équipe</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item mt-5">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <button class="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Gestion de l'équipe</h1>
                <div class="user-dropdown dropdown">
                    <div class="dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name'] ?? 'Utilisateur'); ?>&background=random" alt="User">
                        <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Utilisateur'); ?></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <?php displayError(); ?>
            <?php displaySuccess(); ?>
            
            <!-- Team Members List -->
            <div class="admin-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Membres de l'équipe</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="fas fa-plus me-2"></i>Ajouter un membre
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Nom complet</th>
                                <th>Rôle</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($team_members as $member): ?>
                            <tr>
                                <td>
                                    <?php if ($member['image']): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . $member['image']; ?>" alt="<?php echo htmlspecialchars($member['full_name']); ?>" class="team-member-image">
                                    <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($member['full_name']); ?>&background=random" alt="<?php echo htmlspecialchars($member['full_name']); ?>" class="team-member-image">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($member['role']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info me-2" data-bs-toggle="modal" data-bs-target="#editMemberModal<?php echo $member['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteMemberModal<?php echo $member['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Edit Modal -->
                            <div class="modal fade" id="editMemberModal<?php echo $member['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modifier un membre</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="team.php" method="post" enctype="multipart/form-data">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Nom complet</label>
                                                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($member['full_name']); ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Rôle</label>
                                                    <input type="text" class="form-control" name="role" value="<?php echo htmlspecialchars($member['role']); ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($member['description']); ?></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Photo</label>
                                                    <input type="file" class="form-control" name="image" accept="image/*">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($member['email']); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Téléphone</label>
                                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">GitHub URL</label>
                                                    <input type="url" class="form-control" name="github_url" value="<?php echo htmlspecialchars($member['github_url']); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">LinkedIn URL</label>
                                                    <input type="url" class="form-control" name="linkedin_url" value="<?php echo htmlspecialchars($member['linkedin_url']); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Twitter URL</label>
                                                    <input type="url" class="form-control" name="twitter_url" value="<?php echo htmlspecialchars($member['twitter_url']); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Facebook URL</label>
                                                    <input type="url" class="form-control" name="facebook_url" value="<?php echo htmlspecialchars($member['facebook_url']); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">YouTube URL</label>
                                                    <input type="url" class="form-control" name="youtube_url" value="<?php echo htmlspecialchars($member['youtube_url']); ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteMemberModal<?php echo $member['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirmer la suppression</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Êtes-vous sûr de vouloir supprimer ce membre de l'équipe ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <form action="team.php" method="post">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un membre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="team.php" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <input type="text" class="form-control" name="role" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">GitHub URL</label>
                            <input type="url" class="form-control" name="github_url">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">LinkedIn URL</label>
                            <input type="url" class="form-control" name="linkedin_url">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Twitter URL</label>
                            <input type="url" class="form-control" name="twitter_url">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Facebook URL</label>
                            <input type="url" class="form-control" name="facebook_url">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" class="form-control" name="youtube_url">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <style>
    .team-member-image {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: #fff;
    }
    
    .btn-purple:hover {
        background-color: #5a32a3;
        border-color: #5a32a3;
        color: #fff;
    }
    
    .card-icon.purple {
        background-color: #6f42c1;
    }
    </style>
</body>
</html>