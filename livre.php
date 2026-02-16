<?php
session_start();
require_once 'config.php';

if(!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->execute([$_GET['id']]);
$livre = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$livre) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($livre['titre']) ?> - Bibliothèque</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7fafc;
            line-height: 1.6;
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .book-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .book-title {
            font-size: 2.5rem;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        .book-author {
            font-size: 1.2rem;
            color: #718096;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .book-price {
            font-size: 2rem;
            color: #48bb78;
            font-weight: bold;
            margin: 1rem 0;
        }
        .stock-info {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            <?= $livre['stock'] > 10 ? 'background: #c6f6d5; color: #22543d;' : 
               ($livre['stock'] > 0 ? 'background: #fefcbf; color: #744210;' : 
               'background: #fed7d7; color: #742a2a;') ?>
        }
        .description-courte {
            background: #f7fafc;
            padding: 1.5rem;
            border-radius: 5px;
            margin: 1.5rem 0;
            font-size: 1.1rem;
            color: #4a5568;
            border-left: 4px solid #667eea;
        }
        .description-longue {
            margin: 2rem 0;
            padding: 1.5rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }
        .description-longue h3 {
            color: #2d3748;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        .description-longue p {
            color: #4a5568;
            white-space: pre-line;
            line-height: 1.8;
        }
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1rem;
            margin-right: 1rem;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #5a67d8;
        }
        .btn-disabled {
            background: #cbd5e0;
            cursor: not-allowed;
        }
        .btn-disabled:hover {
            background: #cbd5e0;
        }
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Bibliothèque</h1>
        <div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="panier.php">🛒 Panier</a>
                <a href="profil.php"><?= htmlspecialchars($_SESSION['user_nom']) ?></a>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="admin_dashboard.php">Admin</a>
                <?php endif; ?>
                <a href="deconnexion.php">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="book-card">
            <h1 class="book-title"><?= htmlspecialchars($livre['titre']) ?></h1>
            <div class="book-author">Par <?= htmlspecialchars($livre['auteur']) ?></div>
            
            <div class="book-price"><?= number_format($livre['prix'], 2) ?> €</div>
            
            <div class="stock-info">
                <?= $livre['stock'] > 0 ? "Stock: {$livre['stock']} exemplaires" : "Rupture de stock" ?>
            </div>

            <div class="description-courte">
                <?= nl2br(htmlspecialchars($livre['description_courte'])) ?>
            </div>

            <div class="description-longue">
                <h3>📖 Description détaillée</h3>
                <p><?= nl2br(htmlspecialchars($livre['description_longue'])) ?></p>
            </div>

            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($livre['stock'] > 0): ?>
                    <form method="get" action="ajouter_panier.php" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $livre['id'] ?>">
                        <button type="submit" class="btn">➕ Ajouter au panier</button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-disabled" disabled>Rupture de stock</button>
                <?php endif; ?>
            <?php else: ?>
                <a href="connexion.php" class="btn">🔑 Connectez-vous pour acheter</a>
            <?php endif; ?>

            <a href="index.php" class="back-link">← Retour à la liste</a>
        </div>
    </div>
</body>
</html>
