<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// Vérifier si un ID livre est fourni
if(!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$livre_id = $_GET['id'];

// Vérifier si le livre existe et a du stock
$livre = $db->prepare("SELECT * FROM livres WHERE id = ?");
$livre->execute([$livre_id]);
$livre = $livre->fetch();

if(!$livre) {
    $_SESSION['erreur'] = "Livre non trouvé";
    header('Location: index.php');
    exit;
}

if($livre['stock'] <= 0) {
    $_SESSION['erreur'] = "Ce livre n'est pas disponible";
    header('Location: livre.php?id=' . $livre_id);
    exit;
}

// Vérifier si le livre est déjà dans le panier
$check = $db->prepare("SELECT * FROM panier WHERE user_id = ? AND livre_id = ?");
$check->execute([$user_id, $livre_id]);
$existe = $check->fetch();

if($existe) {
    // Augmenter la quantité si pas de limite de stock
    if($existe['quantite'] < $livre['stock']) {
        $update = $db->prepare("UPDATE panier SET quantite = quantite + 1 WHERE id = ?");
        $update->execute([$existe['id']]);
        $_SESSION['success'] = "Quantité augmentée";
    } else {
        $_SESSION['erreur'] = "Stock insuffisant";
    }
} else {
    // Ajouter au panier
    $insert = $db->prepare("INSERT INTO panier (user_id, livre_id, quantite) VALUES (?, ?, 1)");
    $insert->execute([$user_id, $livre_id]);
    $_SESSION['success'] = "Livre ajouté au panier";
}

// Rediriger vers la page précédente ou le panier
if(isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: panier.php');
}
exit;
?>
