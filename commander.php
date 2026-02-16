<?php
session_start();
require_once 'config.php';
require_once 'check_admin.php';
requireUser();

$user_id = $_SESSION['user_id'];

// Récupérer le panier
$panier = $db->prepare("
    SELECT p.*, l.titre, l.prix, l.stock 
    FROM panier p 
    JOIN livres l ON p.livre_id = l.id 
    WHERE p.user_id = ?
");
$panier->execute([$user_id]);
$articles = $panier->fetchAll();

if(empty($articles)) {
    header('Location: panier.php');
    exit;
}

// Vérifier les stocks
foreach($articles as $a) {
    if($a['quantite'] > $a['stock']) {
        $_SESSION['erreur'] = "Le livre '{$a['titre']}' n'a pas assez de stock. Disponible: {$a['stock']}";
        header('Location: panier.php');
        exit;
    }
}

// Récupérer les adresses de l'utilisateur
$adresses = $db->prepare("SELECT * FROM adresses_livraison WHERE user_id = ? ORDER BY est_principale DESC");
$adresses->execute([$user_id]);
$adresses_user = $adresses->fetchAll();

// Récupérer les modes de livraison
$modes_livraison = $db->query("SELECT * FROM modes_livraison WHERE est_actif = 1")->fetchAll();

// Récupérer les méthodes de paiement
$methodes_paiement = $db->query("SELECT * FROM methodes_paiement WHERE est_actif = 1")->fetchAll();

// Calculer le sous-total
$sous_total = 0;
foreach($articles as $a) {
    $sous_total += $a['prix'] * $a['quantite'];
}

// Traitement de la commande
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commander'])) {
    try {
        $db->beginTransaction();
        
        $mode_livraison_id = $_POST['mode_livraison'];
        $mode_paiement = $_POST['mode_paiement'];
        
        // Récupérer les infos de livraison
        $mode = $db->prepare("SELECT * FROM modes_livraison WHERE id = ?");
        $mode->execute([$mode_livraison_id]);
        $mode = $mode->fetch();
        
        $frais_livraison = $mode['prix'];
        $total = $sous_total + $frais_livraison;
        
        // Gérer l'adresse
        if(isset($_POST['nouvelle_adresse'])) {
            // Sauvegarder la nouvelle adresse
            $stmt = $db->prepare("
                INSERT INTO adresses_livraison (user_id, nom_complet, adresse, complement, code_postal, ville, telephone, est_principale)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $_POST['nom_complet'],
                $_POST['adresse'],
                $_POST['complement'] ?? null,
                $_POST['code_postal'],
                $_POST['ville'],
                $_POST['telephone'],
                empty($adresses_user) ? 1 : 0
            ]);
            $adresse_id = $db->lastInsertId();
        } else {
            $adresse_id = $_POST['adresse_existante'];
        }
        
        // Récupérer l'adresse complète pour l'historique
        $adresse = $db->prepare("SELECT * FROM adresses_livraison WHERE id = ?");
        $adresse->execute([$adresse_id]);
        $adresse = $adresse->fetch();
        
        // Créer la commande
        $stmt = $db->prepare("
            INSERT INTO commandes (
                user_id, total, statut, mode_livraison, frais_livraison, 
                adresse_livraison_id, adresse, ville, code_postal, telephone,
                date_livraison_prevue
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Date de livraison prévue selon le mode
        $delai = $mode['delai'];
        $date_prevue = date('Y-m-d H:i:s', strtotime('+' . $delai));
        
        $stmt->execute([
            $user_id,
            $total,
            'en_attente',
            $mode['nom'],
            $frais_livraison,
            $adresse_id,
            $adresse['adresse'] . ($adresse['complement'] ? ' - ' . $adresse['complement'] : ''),
            $adresse['ville'],
            $adresse['code_postal'],
            $adresse['telephone'],
            $date_prevue
        ]);
        
        $commande_id = $db->lastInsertId();
        
        // Ajouter les détails et mettre à jour le stock
        foreach($articles as $article) {
            // Détail de la commande
            $detail = $db->prepare("
                INSERT INTO commande_details (commande_id, livre_id, quantite, prix_unitaire) 
                VALUES (?, ?, ?, ?)
            ");
            $detail->execute([
                $commande_id,
                $article['livre_id'],
                $article['quantite'],
                $article['prix']
            ]);
            
            // Mettre à jour le stock
            $stock = $db->prepare("UPDATE livres SET stock = stock - ? WHERE id = ?");
            $stock->execute([$article['quantite'], $article['livre_id']]);
        }
        
        // Créer la transaction
        $transaction = $db->prepare("
            INSERT INTO transactions (commande_id, user_id, montant, methode_paiement, statut)
            VALUES (?, ?, ?, ?, 'en_attente')
        ");
        $transaction->execute([$commande_id, $user_id, $total, $mode_paiement]);
        
        // Créer le suivi de livraison
        $suivi = $db->prepare("
            INSERT INTO suivi_livraison (commande_id, statut, commentaire)
            VALUES (?, 'commande_recue', 'Commande enregistrée, en attente de confirmation')
        ");
        $suivi->execute([$commande_id]);
        
        // Vider le panier
        $vider = $db->prepare("DELETE FROM panier WHERE user_id = ?");
        $vider->execute([$user_id]);
        
        $db->commit();
        
        // Rediriger vers la page de paiement
        header('Location: paiement.php?commande=' . $commande_id . '&methode=' . $mode_paiement);
        exit;
        
    } catch(Exception $e) {
        $db->rollBack();
        $erreur = "Erreur lors de la commande: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser la commande</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }
        
        .checkout-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }
        
        .section-title {
            font-size: 1.3rem;
            color: var(--text);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
        }
        
        .address-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .address-card:hover {
            border-color: var(--accent);
        }
        
        .address-card.selected {
            border-color: var(--accent);
            background: #f8f9fa;
        }
        
        .address-card input[type="radio"] {
            margin-right: 1rem;
        }
        
        .new-address-form {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .livraison-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .livraison-option:hover {
            border-color: var(--accent);
        }
        
        .livraison-option.selected {
            border-color: var(--accent);
            background: #f8f9fa;
        }
        
        .livraison-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .livraison-nom {
            font-weight: 600;
            color: var(--text);
        }
        
        .livraison-prix {
            font-weight: 700;
            color: var(--success);
        }
        
        .livraison-delai {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .resume-commande {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 2rem;
        }
        
        .resume-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .resume-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text);
            margin: 1rem 0;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--success) 0%, #229954 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .payment-icons {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .payment-icon {
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
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
                <a href="panier.php">Retour au panier</a>
            </div>
        </div>
    </header>

    <div class="checkout-container">
        <?php if(isset($erreur)): ?>
            <div class="error-message"><?= $erreur ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="checkout-grid">
                <!-- Colonne de gauche : Formulaire -->
                <div>
                    <!-- Adresse de livraison -->
                    <div class="checkout-section">
                        <h2 class="section-title">🚚 Adresse de livraison</h2>
                        
                        <?php if(!empty($adresses_user)): ?>
                            <?php foreach($adresses_user as $adresse): ?>
                            <label class="address-card">
                                <input type="radio" name="adresse_existante" value="<?= $adresse['id'] ?>" <?= $adresse['est_principale'] ? 'checked' : '' ?>>
                                <strong><?= htmlspecialchars($adresse['nom_complet']) ?></strong><br>
                                <?= htmlspecialchars($adresse['adresse']) ?><br>
                                <?php if($adresse['complement']): ?>
                                    <?= htmlspecialchars($adresse['complement']) ?><br>
                                <?php endif; ?>
                                <?= htmlspecialchars($adresse['code_postal']) ?> <?= htmlspecialchars($adresse['ville']) ?><br>
                                Tél: <?= htmlspecialchars($adresse['telephone']) ?>
                                <?php if($adresse['est_principale']): ?>
                                    <span style="color: var(--success);">(Principale)</span>
                                <?php endif; ?>
                            </label>
                            <?php endforeach; ?>
                            
                            <p style="text-align: center; margin: 1rem 0;">- OU -</p>
                        <?php endif; ?>
                        
                        <div class="new-address-form">
                            <h3 style="margin-bottom: 1rem;">Nouvelle adresse</h3>
                            <input type="radio" name="adresse_existante" value="new" style="display: none;" checked>
                            
                            <div class="form-group">
                                <label>Nom complet</label>
                                <input type="text" name="nom_complet" placeholder="Prénom et nom" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Adresse</label>
                                <input type="text" name="adresse" placeholder="Numéro et rue" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Complément d'adresse (optionnel)</label>
                                <input type="text" name="complement" placeholder="Appartement, étage, etc.">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Code postal</label>
                                    <input type="text" name="code_postal" required>
                                </div>
                                <div class="form-group">
                                    <label>Ville</label>
                                    <input type="text" name="ville" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Téléphone</label>
                                <input type="tel" name="telephone" placeholder="Pour vous contacter en cas de besoin" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mode de livraison -->
                    <div class="checkout-section">
                        <h2 class="section-title">📦 Mode de livraison</h2>
                        
                        <?php foreach($modes_livraison as $mode): ?>
                        <label class="livraison-option">
                            <input type="radio" name="mode_livraison" value="<?= $mode['id'] ?>" <?= $mode['id'] == 1 ? 'checked' : '' ?> required>
                            <div class="livraison-header">
                                <span class="livraison-nom"><?= htmlspecialchars($mode['nom']) ?></span>
                                <span class="livraison-prix"><?= number_format($mode['prix'], 2) ?> €</span>
                            </div>
                            <div class="livraison-delai"><?= htmlspecialchars($mode['description']) ?> - Délai: <?= $mode['delai'] ?></div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Paiement -->
                    <div class="checkout-section">
                        <h2 class="section-title">💳 Paiement</h2>
                        
                        <div class="payment-icons">
                            <span class="payment-icon">💳 Carte</span>
                            <span class="payment-icon">📱 PayPal</span>
                            <span class="payment-icon">🏦 Virement</span>
                        </div>
                        
                        <?php foreach($methodes_paiement as $methode): ?>
                        <label class="livraison-option">
                            <input type="radio" name="mode_paiement" value="<?= $methode['type'] ?>" <?= $methode['type'] == 'cb' ? 'checked' : '' ?> required>
                            <div class="livraison-header">
                                <span class="livraison-nom"><?= htmlspecialchars($methode['nom']) ?></span>
                                <?php if($methode['frais'] > 0): ?>
                                    <span class="livraison-prix">+<?= number_format($methode['frais'], 2) ?> € de frais</span>
                                <?php endif; ?>
                            </div>
                            <div class="livraison-delai"><?= htmlspecialchars($methode['description']) ?></div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Colonne de droite : Résumé -->
                <div>
                    <div class="resume-commande">
                        <h2 class="section-title">📋 Résumé de la commande</h2>
                        
                        <?php foreach($articles as $article): ?>
                        <div class="resume-item">
                            <span><?= htmlspecialchars($article['titre']) ?> x<?= $article['quantite'] ?></span>
                            <span><?= number_format($article['prix'] * $article['quantite'], 2) ?> €</span>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="resume-item">
                            <span>Sous-total</span>
                            <span><?= number_format($sous_total, 2) ?> €</span>
                        </div>
                        
                        <div class="resume-item" id="frais-livraison-display">
                            <span>Livraison</span>
                            <span id="frais-montant">À choisir</span>
                        </div>
                        
                        <div class="resume-total" id="total-display">
                            Total: <?= number_format($sous_total, 2) ?> €
                        </div>
                        
                        <button type="submit" name="commander" class="btn-checkout">
                            Confirmer la commande
                        </button>
                        
                        <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                            🔒 Paiement 100% sécurisé
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Mise à jour dynamique des frais de livraison
        const fraisLivraison = {
            <?php foreach($modes_livraison as $mode): ?>
            <?= $mode['id'] ?>: <?= $mode['prix'] ?>,
            <?php endforeach; ?>
        };
        
        const sousTotal = <?= $sous_total ?>;
        
        document.querySelectorAll('input[name="mode_livraison"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const frais = fraisLivraison[this.value];
                document.getElementById('frais-montant').textContent = frais.toFixed(2) + ' €';
                const total = sousTotal + frais;
                document.getElementById('total-display').innerHTML = 'Total: ' + total.toFixed(2) + ' €';
            });
        });
        
        // Sélection visuelle des options
        document.querySelectorAll('.livraison-option, .address-card').forEach(option => {
            option.addEventListener('click', function(e) {
                if(e.target.type !== 'radio') {
                    const radio = this.querySelector('input[type="radio"]');
                    if(radio) radio.checked = true;
                }
            });
        });
    </script>
</body>
</html>
