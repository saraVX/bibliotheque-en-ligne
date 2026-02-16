<?php
session_start();
require_once 'config.php';
require_once 'check_admin.php';
requireUser();

$user_id = $_SESSION['user_id'];

// Récupérer toutes les commandes de l'utilisateur avec suivi
$commandes = $db->prepare("
    SELECT c.*, 
           (SELECT statut FROM suivi_livraison WHERE commande_id = c.id ORDER BY date_mise_a_jour DESC LIMIT 1) as dernier_statut,
           (SELECT date_mise_a_jour FROM suivi_livraison WHERE commande_id = c.id ORDER BY date_mise_a_jour DESC LIMIT 1) as derniere_maj
    FROM commandes c
    WHERE c.user_id = ?
    ORDER BY c.date_commande DESC
");
$commandes->execute([$user_id]);
$commandes = $commandes->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes commandes - Bibliothèque</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .orders-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        h1 {
            color: var(--text);
            margin-bottom: 2rem;
            font-weight: 300;
        }
        
        h1 span {
            color: var(--accent);
            font-weight: 700;
        }
        
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }
        
        .order-number {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text);
        }
        
        .order-date {
            color: var(--text-light);
        }
        
        .order-status {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .status-en_attente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmee {
            background: #d4edda;
            color: #155724;
        }
        
        .status-expediee {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-livree {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .order-tracking {
            margin: 2rem 0;
        }
        
        .tracking-timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 2rem 0;
        }
        
        .tracking-timeline::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e9ecef;
            z-index: 1;
        }
        
        .timeline-step {
            position: relative;
            z-index: 2;
            background: white;
            padding: 0 0.5rem;
            text-align: center;
            flex: 1;
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            background: white;
            border: 3px solid #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 1.2rem;
        }
        
        .step-icon.completed {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }
        
        .step-icon.active {
            border-color: var(--accent);
            color: var(--accent);
        }
        
        .step-label {
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .step-label.completed {
            color: var(--success);
        }
        
        .step-label.active {
            color: var(--accent);
            font-weight: 600;
        }
        
        .tracking-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1.5rem;
        }
        
        .tracking-number {
            font-size: 1.1rem;
            color: var(--text);
            margin-bottom: 1rem;
        }
        
        .tracking-number code {
            background: var(--primary);
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 5px;
            margin-left: 1rem;
        }
        
        .delivery-date {
            color: var(--success);
            font-weight: 500;
        }
        
        .order-details {
            margin-top: 1.5rem;
        }
        
        .details-title {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 1rem;
        }
        
        .products-list {
            list-style: none;
        }
        
        .product-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .order-total {
            text-align: right;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--success);
            margin-top: 1rem;
        }
        
        .delivery-address {
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .empty-orders {
            text-align: center;
            padding: 4rem;
            background: white;
            border-radius: 15px;
        }
        
        .empty-orders p {
            color: var(--text-light);
            margin-bottom: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: var(--transition);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">📚 <span>Biblio</span>thèque</a>
            </div>
            <div class="nav-links">
                <a href="index.php">Accueil</a>
                <a href="panier.php">Panier</a>
                <a href="profil.php">Profil</a>
                <a href="deconnexion.php">Déconnexion</a>
            </div>
        </div>
    </header>

    <div class="orders-container">
        <h1>📦 <span>Mes</span> commandes</h1>

        <?php if(empty($commandes)): ?>
            <div class="empty-orders">
                <p>Vous n'avez pas encore passé de commande</p>
                <a href="index.php" class="btn">Découvrir nos livres</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach($commandes as $commande): 
                    // Récupérer les produits de la commande
                    $produits = $db->prepare("
                        SELECT cd.*, l.titre 
                        FROM commande_details cd
                        JOIN livres l ON cd.livre_id = l.id
                        WHERE cd.commande_id = ?
                    ");
                    $produits->execute([$commande['id']]);
                    $produits = $produits->fetchAll();
                    
                    // Récupérer le suivi détaillé
                    $suivi = $db->prepare("
                        SELECT * FROM suivi_livraison 
                        WHERE commande_id = ? 
                        ORDER BY date_mise_a_jour DESC
                    ");
                    $suivi->execute([$commande['id']]);
                    $suivis = $suivi->fetchAll();
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <span class="order-number">Commande #<?= str_pad($commande['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            <span class="order-date"> - <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></span>
                        </div>
                        <span class="order-status status-<?= $commande['statut'] ?>">
                            <?= $commande['statut'] == 'en_attente' ? 'En attente' : 
                               ($commande['statut'] == 'confirmee' ? 'Confirmée' : 
                               ($commande['statut'] == 'expediee' ? 'Expédiée' : 'Livrée')) ?>
                        </span>
                    </div>

                    <!-- Timeline de suivi -->
                    <div class="order-tracking">
                        <div class="tracking-timeline">
                            <?php
                            $etapes = [
                                ['icon' => '📝', 'label' => 'Commande', 'statut' => 'commande_recue'],
                                ['icon' => '✅', 'label' => 'Confirmée', 'statut' => 'paiement_recu'],
                                ['icon' => '📦', 'label' => 'Préparée', 'statut' => 'en_preparation'],
                                ['icon' => '🚚', 'label' => 'Expédiée', 'statut' => 'expediee'],
                                ['icon' => '🏠', 'label' => 'Livrée', 'statut' => 'livree']
                            ];
                            
                            $statuts_suivi = array_column($suivis, 'statut');
                            
                            foreach($etapes as $index => $etape):
                                $completed = in_array($etape['statut'], $statuts_suivi);
                                $active = $commande['statut'] == $etape['statut'];
                            ?>
                            <div class="timeline-step">
                                <div class="step-icon <?= $completed ? 'completed' : ($active ? 'active' : '') ?>">
                                    <?= $etape['icon'] ?>
                                </div>
                                <div class="step-label <?= $completed ? 'completed' : ($active ? 'active' : '') ?>">
                                    <?= $etape['label'] ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if($commande['statut'] == 'expediee' || $commande['statut'] == 'livree'): ?>
                        <div class="tracking-info">
                            <div class="tracking-number">
                                🚚 Numéro de suivi : <code>BIB<?= str_pad($commande['id'], 8, '0', STR_PAD_LEFT) ?>FR</code>
                            </div>
                            <?php if($commande['date_livraison_prevue']): ?>
                            <div class="delivery-date">
                                📅 Livraison prévue le <?= date('d/m/Y', strtotime($commande['date_livraison_prevue'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Détails de la commande -->
                    <div class="order-details">
                        <div class="details-title">📖 Produits commandés</div>
                        <ul class="products-list">
                            <?php foreach($produits as $produit): ?>
                            <li class="product-item">
                                <span><?= htmlspecialchars($produit['titre']) ?> x<?= $produit['quantite'] ?></span>
                                <span><?= number_format($produit['prix_unitaire'] * $produit['quantite'], 2) ?> €</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="product-item" style="font-weight: 500;">
                            <span>Livraison (<?= htmlspecialchars($commande['mode_livraison']) ?>)</span>
                            <span><?= number_format($commande['frais_livraison'], 2) ?> €</span>
                        </div>
                        
                        <div class="order-total">
                            Total : <?= number_format($commande['total'], 2) ?> €
                        </div>
                        
                        <div class="delivery-address">
                            <strong>📍 Adresse de livraison :</strong><br>
                            <?= nl2br(htmlspecialchars($commande['adresse'])) ?><br>
                            <?= htmlspecialchars($commande['code_postal']) ?> <?= htmlspecialchars($commande['ville']) ?><br>
                            Tél: <?= htmlspecialchars($commande['telephone']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Bibliothèque - Tous droits réservés</p>
    </footer>
</body>
</html>
