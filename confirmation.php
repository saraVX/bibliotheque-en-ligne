<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

// Récupérer la commande
$commande = $db->prepare("
    SELECT c.*, u.nom, u.email 
    FROM commandes c
    JOIN utilisateurs u ON c.user_id = u.id
    WHERE c.id = ? AND c.user_id = ?
");
$commande->execute([$_GET['id'], $_SESSION['user_id']]);
$commande = $commande->fetch();

if(!$commande) {
    header('Location: index.php');
    exit;
}

// Récupérer les détails
$details = $db->prepare("
    SELECT cd.*, l.titre 
    FROM commande_details cd
    JOIN livres l ON cd.livre_id = l.id
    WHERE cd.commande_id = ?
");
$details->execute([$_GET['id']]);
$articles = $details->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Commande confirmée</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .confirmation-card {
            background: white;
            max-width: 600px;
            width: 100%;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        h1 {
            color: #27ae60;
            margin-bottom: 1rem;
        }
        .order-number {
            background: #f1f8e9;
            padding: 1rem;
            border-radius: 5px;
            margin: 2rem 0;
        }
        .order-number p {
            color: #2c3e50;
            font-size: 1.1rem;
        }
        .order-number .number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #27ae60;
        }
        .details {
            text-align: left;
            margin: 2rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .details h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e1e8ed;
        }
        .total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #27ae60;
            margin-top: 1rem;
            text-align: right;
        }
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 1rem;
        }
        .btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <div class="success-icon">✅</div>
        <h1>Commande confirmée !</h1>
        <p>Merci pour votre achat. Votre commande a bien été enregistrée.</p>
        
        <div class="order-number">
            <p>Numéro de commande</p>
            <div class="number">#<?= str_pad($commande['id'], 6, '0', STR_PAD_LEFT) ?></div>
        </div>

        <div class="details">
            <h3>Détails de la commande</h3>
            <?php foreach($articles as $article): ?>
            <div class="detail-item">
                <span><?= htmlspecialchars($article['titre']) ?> x<?= $article['quantite'] ?></span>
                <span><?= number_format($article['prix_unitaire'] * $article['quantite'], 2) ?> €</span>
            </div>
            <?php endforeach; ?>
            <div class="total">
                Total: <?= number_format($commande['total'], 2) ?> €
            </div>
        </div>

        <div class="details">
            <h3>Livraison</h3>
            <p><strong>Adresse:</strong> <?= htmlspecialchars($commande['adresse_livraison']) ?></p>
            <p><strong>Ville:</strong> <?= htmlspecialchars($commande['code_postal']) ?> <?= htmlspecialchars($commande['ville']) ?></p>
            <p><strong>Téléphone:</strong> <?= htmlspecialchars($commande['telephone']) ?></p>
            <p><strong>Statut:</strong> <?= $commande['statut'] ?></p>
        </div>

        <a href="index.php" class="btn">Retour à l'accueil</a>
    </div>
</body>
</html>
