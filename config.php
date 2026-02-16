<?php
try {
    $db = new PDO('sqlite:' . __DIR__ . '/bibliotheque.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fonction pour vérifier si l'utilisateur est admin
    function estAdmin() {
        global $db;
        if(!isset($_SESSION['user_id'])) return false;
        $stmt = $db->prepare("SELECT role FROM utilisateurs WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        return $user && $user['role'] === 'admin';
    }
    
    // Fonction pour vérifier si l'utilisateur est connecté
    function estConnecte() {
        return isset($_SESSION['user_id']);
    }
    
} catch(PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}
?>
