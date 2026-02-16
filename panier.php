<?php
session_start();
require_once 'config.php';
require_once 'check_admin.php';
requireUser(); // Seulement pour les utilisateurs connectés

$user_id = $_SESSION['user_id'];

// Vider le panier
if(isset($_GET['vider'])) {
    $vider = $db->prepare("DELETE FROM panier WHERE user_id = ?");
    $vider->execute([$user_id]);
    header('Location: panier.php');
    exit;
}

// Supprimer un article
if(isset($_GET['supprimer'])) {
    $supprimer = $db->prepare("DELETE FROM panier WHERE id = ? AND user_id = ?");
    $supprimer->execute([$_GET['supprimer'], $user_id]);
    header('Location: panier.php');
    exit;
}

// Modifier quantité
if(isset($_POST['modifier_quantite'])) {
    $panier_id = $_POST['panier_id'];
    $nouvelle_quantite = $_POST['quantite'];
    
    // Vérifier le stock
    $info = $db->prepare("
        SELECT p.*, l.stock 
        FROM panier p 
        JOIN livres l ON p.livre_id = l.id 
        WHERE p.id = ? AND p.user_id = ?
    ");
    $info->execute([$panier_id, $user_id]);
    $item = $info->fetch();
    
    if($item && $nouvelle_quantite <= $item['stock'] && $nouvelle_quantite > 0) {
        $update = $db->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
        $update->execute([$nouvelle_quantite, $panier_id]);
    }
    header('Location: panier.php');
    exit;
}

// Récupérer le panier
$panier = $db->prepare("
    SELECT p.*, l.titre, l.prix, l.stock 
    FROM panier p 
    JOIN livres l ON p.livre_id = l.id 
    WHERE p.user_id = ?
");
$panier->execute([$user_id]);
$articles = $panier->fetchAll();

// Calculer le total
$total = 0;
foreach($articles as $a) {
    $total += $a['prix'] * $a['quantite'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mon Panier</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7fafc;
        }
        .header {
            background: #2d3748;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
        }
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 2rem;
        }
        .cart-items {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .item-info h3 {
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        .item-info p {
            color: #718096;
        }
        .item-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #48bb78;
        }
        .item-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .quantity-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .quantity-form input {
            width: 60px;
            padding: 0.3rem;
            border: 1px solid #cbd5e0;
            border-radius: 3px;
            text-align: center;
        }
        .btn-quantity {
            padding: 0.3rem 0.8rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .remove-link {
            color: #e53e3e;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .cart-total {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: right;
        }
        .cart-total h2 {
            color: #2d3748;
            margin-bottom: 1rem;
        }
        .total-amount {
            font-size: 2rem;
            font-weight: bold;
            color: #48bb78;
        }
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-left: 1rem;
        }
        .btn-danger {
            background: #e53e3e;
        }
        .empty-cart {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
        }
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        .success {
            background: #c6f6d5;
            color: #22543d;
        }
        .error {
            background: #fed7d7;
            color: #742a2a;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Mon Panier</h1>
        <div>
            <a href="index.php">Accueil</a>
            <a href="deconnexion.php">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="message success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['erreur'])): ?>
            <div class="message error"><?= $_SESSION['erreur'] ?></div>
            <?php unset($_SESSION['erreur']); ?>
        <?php endif; ?>

        <?php if(empty($articles)): ?>
            <div class="empty-cart">
                <h2>Votre panier est vide</h2>
                <p style="margin: 1rem 0; color: #718096;">Découvrez nos livres et ajoutez vos favoris</p>
                <a href="index.php" class="btn">Voir les livres</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach($articles as $article): ?>
                <div class="cart-item">
                    <div class="item-info">
                        <h3><?= htmlspecialchars($article['titre']) ?></h3>
                        <p>Prix unitaire: <?= number_format($article['prix'], 2) ?> €</p>
                        <?php if($article['quantite'] > $article['stock']): ?>
                            <p style="color: #e53e3e;">Stock insuffisant! (<?= $article['stock'] ?> dispo)</p>
                        <?php endif; ?>
                    </div>
                    <div class="item-actions">
                        <form method="post" class="quantity-form">
                            <input type="hidden" name="panier_id" value="<?= $article['id'] ?>">
                            <input type="number" name="quantite" value="<?= $article['quantite'] ?>" min="1" max="<?= $article['stock'] ?>">
                            <button type="submit" name="modifier_quantite" class="btn-quantity">↻</button>
                        </form>
                        <span class="item-price"><?= number_format($article['prix'] * $article['quantite'], 2) ?> €</span>
                        <a href="?supprimer=<?= $article['id'] ?>" class="remove-link" onclick="return confirm('Supprimer ?')">🗑️</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-total">
                <h2>Total</h2>
                <span class="total-amount"><?= number_format($total, 2) ?> €</span>
                <div style="margin-top: 1rem;">
                    <a href="?vider=1" class="btn btn-danger" onclick="return confirm('Vider le panier ?')">Vider le panier</a>
                    <a href="commander.php" class="btn">Passer la commande</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
