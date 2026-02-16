<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est admin
function estAdmin() {
    if(!isset($_SESSION['user_id'])) {
        return false;
    }
    
    global $db;
    $stmt = $db->prepare("SELECT is_admin FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    return $user && $user['is_admin'] == 1;
}

// Rediriger si pas admin
function requireAdmin() {
    if(!estAdmin()) {
        header('Location: index.php');
        exit;
    }
}
?>
