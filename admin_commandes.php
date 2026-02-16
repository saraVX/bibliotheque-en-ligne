<?php
session_start();
require_once 'config.php';
require_once 'check_admin.php';
requireAdmin();

// Mise à jour du statut de commande
if(isset($_POST['update_statut'])) {
    $stmt = $db->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
    $stmt->execute([$_POST['statut'], $_POST['commande_id']]);
    $message = "Statut mis à jour avec succès !";
}

// Récupérer toutes les commandes avec infos utilisateur
$commandes = $db->query("
    SELECT c.*, u.nom, u.email 
    FROM commandes c
    JOIN utilisateurs u ON c.user_id = u.id
    ORDER BY c.date_commande DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des commandes - Admin</title>
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
        .header h1 {
            font-size: 1.5rem;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .message {
            background: #48bb78;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
        }
        .commandes-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .commande-card {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
        }
        .commande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            cursor: pointer;
            background: #f8fafc;
            padding: 1rem;
            border-radius: 5px;
        }
        .commande-header:hover {
            background: #edf2f7;
        }
        .commande-info {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 0.8rem;
            color: #718096;
            text-transform: uppercase;
        }
        .info-value {
            font-weight: bold;
            color: #2d3748;
        }
        .statut-badge {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .statut-en_attente { background: #fed7d7; color: #742a2a; }
        .statut-confirmee { background: #c6f6d5; color: #22543d; }
        .statut-expediee { background: #bee3f8; color: #1e4a6b; }
        .statut-livree { background: #fefcbf; color: #744210; }
        
        .commande-details {
            display: none;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 5px;
            margin-top: 1rem;
        }
        .commande-details.active {
            display: block;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 1rem;
        }
        .details-section {
            background: white;
            padding: 1rem;
            border-radius: 5px;
        }
        .details-section h4 {
            color: #4a5568;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
        }
        .produits-table {
            width: 100%;
            border-collapse: collapse;
        }
        .produits-table th {
            text-align: left;
            padding: 0.5rem;
            background: #edf2f7;
            color: #4a5568;
        }
        .produits-table td {
            padding: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .total-commande {
            text-align: right;
            font-size: 1.2rem;
            font-weight: bold;
            color: #48bb78;
            margin-top: 1rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #5a67d8;
        }
        select {
            padding: 0.5rem;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
            margin-right: 0.5rem;
        }
        .livraison-info {
            background: #ebf4ff;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📦 Gestion des commandes</h1>
        <div>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="deconnexion.php">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <?php if(isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <?php
        // Statistiques
        $stats = [
            'total' => count($commandes),
            'en_attente' => $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'")->fetchColumn(),
            'confirmee' => $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'confirmee'")->fetchColumn(),
            'expediee' => $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'expediee'")->fetchColumn(),
            'livree' => $db->query("SELECT COUNT(*) FROM commandes WHERE statut = 'livree'")->fetchColumn(),
        ];
        ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Total commandes</h3>
                <div class="stat-number"><?= $stats['total'] ?></div>
            </div>
            <div class="stat-card">
                <h3>En attente</h3>
                <div class="stat-number" style="color: #742a2a;"><?= $stats['en_attente'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Confirmées</h3>
                <div class="stat-number" style="color: #22543d;"><?= $stats['confirmee'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Expédiées</h3>
                <div class="stat-number" style="color: #1e4a6b;"><?= $stats['expediee'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Livrées</h3>
                <div class="stat-number" style="color: #744210;"><?= $stats['livree'] ?></div>
            </div>
        </div>

        <div class="commandes-list">
            <?php if(empty($commandes)): ?>
                <p style="text-align: center; padding: 3rem; color: #718096;">Aucune commande pour le moment</p>
            <?php else: ?>
                <?php foreach($commandes as $commande): 
                    // Récupérer les détails de la commande
                    $details = $db->prepare("
                        SELECT cd.*, l.titre 
                        FROM commande_details cd
                        JOIN livres l ON cd.livre_id = l.id
                        WHERE cd.commande_id = ?
                    ");
                    $details->execute([$commande['id']]);
                    $produits = $details->fetchAll();
                ?>
                <div class="commande-card">
                    <div class="commande-header" onclick="toggleDetails(<?= $commande['id'] ?>)">
                        <div class="commande-info">
                            <div class="info-item">
                                <span class="info-label">Commande #</span>
                                <span class="info-value"><?= str_pad($commande['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Date</span>
                                <span class="info-value"><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Client</span>
                                <span class="info-value"><?= htmlspecialchars($commande['nom']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Total</span>
                                <span class="info-value"><?= number_format($commande['total'], 2) ?> €</span>
                            </div>
                        </div>
                        <div class="statut-badge statut-<?= $commande['statut'] ?>">
                            <?= $commande['statut'] ?>
                        </div>
                    </div>

                    <div id="details-<?= $commande['id'] ?>" class="commande-details">
                        <div class="details-grid">
                            <div class="details-section">
                                <h4>👤 Informations client</h4>
                                <p><strong>Nom:</strong> <?= htmlspecialchars($commande['nom']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($commande['email']) ?></p>
                                <p><strong>Téléphone:</strong> <?= htmlspecialchars($commande['telephone']) ?></p>
                            </div>
                            
                            <div class="details-section">
                                <h4>🚚 Adresse de livraison</h4>
                                <p><?= htmlspecialchars($commande['adresse']) ?></p>
                                <p><?= htmlspecialchars($commande['code_postal']) ?> <?= htmlspecialchars($commande['ville']) ?></p>
                            </div>
                        </div>

                        <div class="details-section">
                            <h4>📦 Produits commandés</h4>
                            <table class="produits-table">
                                <thead>
                                    <tr>
                                        <th>Livre</th>
                                        <th>Quantité</th>
                                        <th>Prix unitaire</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($produits as $produit): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($produit['titre']) ?></td>
                                        <td><?= $produit['quantite'] ?></td>
                                        <td><?= number_format($produit['prix_unitaire'], 2) ?> €</td>
                                        <td><?= number_format($produit['prix_unitaire'] * $produit['quantite'], 2) ?> €</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="total-commande">
                                Total: <?= number_format($commande['total'], 2) ?> €
                            </div>
                        </div>

                        <div class="livraison-info">
                            <h4 style="margin-bottom: 1rem;">🚚 Suivi de livraison</h4>
                            <form method="post" style="display: flex; gap: 1rem; align-items: center;">
                                <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                                <select name="statut">
                                    <option value="en_attente" <?= $commande['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="confirmee" <?= $commande['statut'] == 'confirmee' ? 'selected' : '' ?>>Confirmée</option>
                                    <option value="expediee" <?= $commande['statut'] == 'expediee' ? 'selected' : '' ?>>Expédiée</option>
                                    <option value="livree" <?= $commande['statut'] == 'livree' ? 'selected' : '' ?>>Livrée</option>
                                </select>
                                <button type="submit" name="update_statut" class="btn">Mettre à jour le statut</button>
                            </form>
                            <p style="margin-top: 1rem; color: #718096;">
                                <strong>Dernière mise à jour:</strong> <?= date('d/m/Y H:i') ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleDetails(commandeId) {
            var details = document.getElementById('details-' + commandeId);
            details.classList.toggle('active');
        }
    </script>
</body>
</html>
