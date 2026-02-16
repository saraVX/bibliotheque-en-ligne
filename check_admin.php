<?php
// Vérifier si l'utilisateur est admin
function requireAdmin() {
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

// Vérifier si l'utilisateur est connecté (user normal)
function requireUser() {
    if(!isset($_SESSION['user_id'])) {
        header('Location: connexion.php');
        exit;
    }
}
?>
