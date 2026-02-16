<?php
try {
    $db = new PDO('sqlite:bibliotheque.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Table utilisateurs
    $db->exec("CREATE TABLE utilisateurs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'user',
        date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Table livres avec catégorie
    $db->exec("CREATE TABLE livres (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titre TEXT NOT NULL,
        auteur TEXT NOT NULL,
        categorie TEXT NOT NULL,
        prix DECIMAL(10,2) NOT NULL,
        stock INTEGER DEFAULT 10,
        description_courte TEXT,
        description_longue TEXT
    )");
    
    // Table panier
    $db->exec("CREATE TABLE panier (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        livre_id INTEGER NOT NULL,
        quantite INTEGER DEFAULT 1,
        date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Table commandes
    $db->exec("CREATE TABLE commandes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
        total DECIMAL(10,2) NOT NULL,
        statut TEXT DEFAULT 'en_attente',
        adresse TEXT NOT NULL,
        ville TEXT NOT NULL,
        code_postal TEXT NOT NULL,
        telephone TEXT NOT NULL
    )");
    
    // Table détails commandes
    $db->exec("CREATE TABLE commande_details (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        commande_id INTEGER NOT NULL,
        livre_id INTEGER NOT NULL,
        quantite INTEGER NOT NULL,
        prix_unitaire DECIMAL(10,2) NOT NULL
    )");
    
    // Créer un admin
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO utilisateurs (nom, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Administrateur', 'admin@bibliotheque.com', $admin_password, 'admin']);
    
    // LIVRES PAR CATÉGORIE (2 livres par catégorie)
    $livres = [
        // CLASSIQUES
        [
            'Le Petit Prince',
            'Antoine de Saint-Exupéry',
            'classique',
            15.99,
            15,
            'Un conte poétique et philosophique sur l\'amitié et le sens de la vie',
            "Le Petit Prince est une œuvre poétique et philosophique sous l'apparence d'un conte pour enfants."
        ],
        [
            '1984',
            'George Orwell',
            'classique',
            18.50,
            8,
            'Une dystopie glaçante sur le totalitarisme et la surveillance de masse',
            "1984 est un roman d'anticipation qui décrit un monde terrifiant où le régime totalitaire contrôle tout."
        ],
        
        // FANTASY
        [
            'Harry Potter à l\'école des sorciers',
            'J.K. Rowling',
            'fantasy',
            22.00,
            20,
            'Le début des aventures du plus célèbre sorcier de notre époque',
            "Harry Potter découvre à 11 ans qu'il est un sorcier et intègre l'école de Poudlard."
        ],
        [
            'Le Seigneur des Anneaux',
            'J.R.R. Tolkien',
            'fantasy',
            29.99,
            5,
            'L\'épopée fantastique qui a redéfini le genre',
            "Frodon Sacquet hérite d'un anneau magique et entreprend un périlleux voyage pour le détruire."
        ],
        
        // SCIENCE-FICTION
        [
            'Dune',
            'Frank Herbert',
            'sf',
            24.99,
            10,
            'Chef-d\'œuvre de la science-fiction sur la planète Arrakis',
            "Sur la planète désertique Arrakis, le jeune Paul Atréides se retrouve au cœur d'un conflit galactique."
        ],
        [
            'Fondation',
            'Isaac Asimov',
            'sf',
            21.50,
            7,
            'Le cycle de Fondation, une des plus grandes sagas de la SF',
            "Hari Seldon prédit l'effondrement de l'Empire Galactique et crée deux Fondations."
        ],
        
        // HORREUR
        [
            'Ça',
            'Stephen King',
            'horreur',
            26.90,
            6,
            'Le chef-d\'œuvre de Stephen King sur une entité maléfique',
            "Dans la ville de Derry, une entité maléfique se réveille tous les 27 ans pour semer la terreur."
        ],
        [
            'Shining',
            'Stephen King',
            'horreur',
            19.99,
            4,
            'L\'histoire glaçante d\'un hôtel isolé',
            "Jack Torrance devient gardien d'un hôtel isolé où des forces maléfiques se manifestent."
        ],
        
        // PHILOSOPHIE
        [
            'L\'Alchimiste',
            'Paulo Coelho',
            'philosophie',
            16.90,
            12,
            'Un conte philosophique sur la poursuite de ses rêves',
            "Santiago, un jeune berger, part à la recherche d'un trésor au pied des pyramides d'Égypte."
        ],
        [
            'Le Monde de Sophie',
            'Jostein Gaarder',
            'philosophie',
            23.50,
            9,
            'Un roman qui initie à la philosophie',
            "Sophie, 14 ans, reçoit d'étranges lettres qui l'initient à l'histoire de la philosophie."
        ]
    ];
    
    $stmt = $db->prepare("INSERT INTO livres (titre, auteur, categorie, prix, stock, description_courte, description_longue) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach($livres as $livre) {
        $stmt->execute($livre);
    }
    
    echo "✅ BASE DE DONNÉES CRÉÉE AVEC SUCCÈS !\n";
    echo "📚 " . count($livres) . " livres ajoutés\n";
    echo "📖 Catégories : classique, fantasy, sf, horreur, philosophie\n";
    echo "👤 Admin: admin@bibliotheque.com / admin123\n";
    echo "🚀 Le site est prêt !\n";
    
} catch(PDOException $e) {
    die("❌ Erreur: " . $e->getMessage());
}
?>
