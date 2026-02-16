<?php
try {
    $db = new PDO('sqlite:bibliotheque.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ajouter les tables de livraison et paiement si elles n'existent pas
    
    // Table des adresses de livraison
    $db->exec("CREATE TABLE IF NOT EXISTS adresses_livraison (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        nom_complet TEXT NOT NULL,
        adresse TEXT NOT NULL,
        complement TEXT,
        code_postal TEXT NOT NULL,
        ville TEXT NOT NULL,
        pays TEXT DEFAULT 'France',
        telephone TEXT NOT NULL,
        est_principale INTEGER DEFAULT 0,
        date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Table des modes de livraison
    $db->exec("CREATE TABLE IF NOT EXISTS modes_livraison (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        description TEXT,
        delai TEXT,
        prix DECIMAL(10,2) NOT NULL,
        est_actif INTEGER DEFAULT 1
    )");
    
    // Table des méthodes de paiement
    $db->exec("CREATE TABLE IF NOT EXISTS methodes_paiement (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        type TEXT NOT NULL,
        description TEXT,
        frais DECIMAL(10,2) DEFAULT 0,
        est_actif INTEGER DEFAULT 1
    )");
    
    // Table des transactions
    $db->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        commande_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        montant DECIMAL(10,2) NOT NULL,
        methode_paiement TEXT NOT NULL,
        statut TEXT DEFAULT 'en_attente',
        transaction_id TEXT,
        date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,
        details TEXT
    )");
    
    // Table des suivis de livraison
    $db->exec("CREATE TABLE IF NOT EXISTS suivi_livraison (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        commande_id INTEGER NOT NULL,
        statut TEXT NOT NULL,
        emplacement TEXT,
        date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP,
        commentaire TEXT
    )");
    
    // Ajouter les colonnes à la table commandes si elles n'existent pas
    try {
        $db->exec("ALTER TABLE commandes ADD COLUMN mode_livraison TEXT");
        $db->exec("ALTER TABLE commandes ADD COLUMN frais_livraison DECIMAL(10,2) DEFAULT 0");
        $db->exec("ALTER TABLE commandes ADD COLUMN adresse_livraison_id INTEGER");
        $db->exec("ALTER TABLE commandes ADD COLUMN numero_suivi TEXT");
        $db->exec("ALTER TABLE commandes ADD COLUMN date_livraison_prevue DATETIME");
        $db->exec("ALTER TABLE commandes ADD COLUMN date_livraison_reelle DATETIME");
    } catch(Exception $e) {
        // Les colonnes existent déjà
    }
    
    // Insérer les modes de livraison par défaut
    $modes = [
        ['Standard', 'Livraison en 3-5 jours ouvrés', '3-5 jours', 4.99, 1],
        ['Express', 'Livraison en 24-48h', '24-48h', 9.99, 1],
        ['Point Relais', 'Livraison en point relais (3-5 jours)', '3-5 jours', 2.99, 1],
        ['Chronopost', 'Livraison express à domicile', '24h', 14.99, 1]
    ];
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO modes_livraison (nom, description, delai, prix, est_actif) VALUES (?, ?, ?, ?, ?)");
    foreach($modes as $mode) {
        $stmt->execute($mode);
    }
    
    // Insérer les méthodes de paiement par défaut
    $paiements = [
        ['Carte Bancaire', 'cb', 'Paiement sécurisé par carte bancaire', 0, 1],
        ['PayPal', 'paypal', 'Paiement via votre compte PayPal', 0, 1],
        ['Virement Bancaire', 'virement', 'Paiement par virement bancaire', 0, 1],
        ['À la livraison', 'livraison', 'Paiement à la réception de la commande', 2.99, 1]
    ];
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO methodes_paiement (nom, type, description, frais, est_actif) VALUES (?, ?, ?, ?, ?)");
    foreach($paiements as $paiement) {
        $stmt->execute($paiement);
    }
    
    echo "✅ Base de données mise à jour avec succès !\n";
    echo "📦 Tables de livraison et paiement créées\n";
    
} catch(PDOException $e) {
    die("❌ Erreur: " . $e->getMessage());
}
?>
