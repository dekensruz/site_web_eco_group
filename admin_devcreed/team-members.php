<?php
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
checkAccess();

// Traitement des actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add' || $action === 'edit') {
        // Traitement de l'image
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image = uploadImage($_FILES['image'], 'team');
        }
        
        $data = [
            'full_name' => $_POST['full_name'],
            'role' => $_POST['role'],
            'description' => $_POST['description'],
            'email' => $_POST['email'],
            'github_url' => $_POST['github_url'],
            'linkedin_url' => $_POST['linkedin_url'],
            'twitter_url' => $_POST['twitter_url'],
            'facebook_url' => $_POST['facebook_url'],
            'phone' => $_POST['phone'],
            'image' => $image ?: $_POST['current_image']
        ];
        
        if ($action === 'add') {
            if (addTeamMember($data)) {
                $_SESSION['success'] = 'Le membre a été ajouté avec succès.';
            } else {
                $_SESSION['error'] = 'Une erreur est survenue lors de l\'ajout du membre.';
            }
        } else {
            $id = $_POST['member_id'];
            if (updateTeamMember($id, $data)) {
                $_SESSION['success'] = 'Le membre a été mis à jour avec succès.';
            } else {
                $_SESSION['error'] = 'Une erreur est survenue lors de la mise à jour du membre.';
            }
        }
    } elseif ($action === 'delete' && isset($_POST['member_id'])) {
        $id = $_POST['member_id'];
        if (deleteTeamMember($id)) {
            $_SESSION['success'] = 'Le membre a été supprimé avec succès.';
        } else {
            $_SESSION['error'] = 'Une erreur est survenue lors de la suppression du membre.';
        }
    }
    
    header('Location: team-members.php');
    exit;
}

// Récupérer les membres de l'équipe
$members = getTeamMembers();
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
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-main-content">
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">Gestion de l'équipe</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="fas fa-plus"></i> Ajouter un membre
                    </button>
                </div>
                
                <?php
                displayError();
                displaySuccess();
                ?>
                
                <!-- Liste des membres -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Nom complet</th>
                                <th>Rôle</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                            <tr>
                                <td>
                                    <img src="../uploads/<?php echo $member['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($member['full_name']); ?>" 
                                         class="rounded-circle" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($member['role']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-member" 
                                            data-member='<?php echo json_encode($member); ?>'
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editMemberModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-member"
                                            data-id="<?php echo $member['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($member['full_name']); ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteMemberModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajout -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un membre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="team-members.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rôle</label>
                                <input type="text" name="role" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">GitHub URL</label>
                                <input type="url" name="github_url" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" name="linkedin_url" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Twitter URL</label>
                                <input type="url" name="twitter_url" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Facebook URL</label>
                                <input type="url" name="facebook_url" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
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
    
    <!-- Modal Modification -->
    <div class="modal fade" id="editMemberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier un membre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="team-members.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="member_id" id="edit_member_id">
                    <input type="hidden" name="current_image" id="edit_current_image">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet</label>
                                <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rôle</label>
                                <input type="text" name="role" id="edit_role" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" id="edit_phone" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">GitHub URL</label>
                                <input type="url" name="github_url" id="edit_github_url" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" name="linkedin_url" id="edit_linkedin_url" class="form-control">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Twitter URL</label>
                                <input type="url" name="twitter_url" id="edit_twitter_url" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Facebook URL</label>
                                <input type="url" name="facebook_url" id="edit_facebook_url" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Laissez vide pour conserver l'image actuelle</small>
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
    
    <!-- Modal Suppression -->
    <div class="modal fade" id="deleteMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le membre <strong id="delete_member_name"></strong> ?</p>
                    <p class="text-danger">Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <form action="team-members.php" method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="member_id" id="delete_member_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de la modification
        document.querySelectorAll('.edit-member').forEach(function(button) {
            button.addEventListener('click', function() {
                const member = JSON.parse(this.dataset.member);
                document.getElementById('edit_member_id').value = member.id;
                document.getElementById('edit_full_name').value = member.full_name;
                document.getElementById('edit_role').value = member.role;
                document.getElementById('edit_description').value = member.description;
                document.getElementById('edit_email').value = member.email;
                document.getElementById('edit_phone').value = member.phone;
                document.getElementById('edit_github_url').value = member.github_url;
                document.getElementById('edit_linkedin_url').value = member.linkedin_url;
                document.getElementById('edit_twitter_url').value = member.twitter_url;
                document.getElementById('edit_facebook_url').value = member.facebook_url;
                document.getElementById('edit_current_image').value = member.image;
            });
        });
        
        // Gestion de la suppression
        document.querySelectorAll('.delete-member').forEach(function(button) {
            button.addEventListener('click', function() {
                document.getElementById('delete_member_id').value = this.dataset.id;
                document.getElementById('delete_member_name').textContent = this.dataset.name;
            });
        });
    });
    </script>
</body>
</html>