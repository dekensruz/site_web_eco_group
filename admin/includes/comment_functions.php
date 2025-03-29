<?php

function createComment($article_id, $name, $email, $comment, $user_id = null, $parent_id = null, $status = 'pending') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, parent_id, name, email, comment, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissss", $article_id, $user_id, $parent_id, $name, $email, $comment, $status);
        
        if ($stmt->execute()) {
            return $conn->insert_id;
        } else {
            error_log("Erreur lors de la création du commentaire: " . $stmt->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception lors de la création du commentaire: " . $e->getMessage());
        return false;
    }
}

function getCommentsByArticleId($article_id, $status = 'approved') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT c.*, u.full_name as user_name 
                               FROM comments c 
                               LEFT JOIN users u ON c.user_id = u.id 
                               WHERE c.article_id = ? AND c.status = ? 
                               ORDER BY c.created_at DESC");
        $stmt->bind_param("is", $article_id, $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Exception lors de la récupération des commentaires: " . $e->getMessage());
        return [];
    }
}

function updateCommentStatus($comment_id, $status) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("UPDATE comments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $comment_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Exception lors de la mise à jour du statut du commentaire: " . $e->getMessage());
        return false;
    }
}

function deleteComment($comment_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Exception lors de la suppression du commentaire: " . $e->getMessage());
        return false;
    }
}