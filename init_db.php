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
    
    // Table livres
    $db->exec("CREATE TABLE livres (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titre TEXT NOT NULL,
        auteur TEXT NOT NULL,
        prix DECIMAL(10,2) NOT NULL,
        stock INTEGER DEFAULT 10,
        description_courte TEXT,
        description_longue TEXT,
        image TEXT DEFAULT 'default.jpg'
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
    
    // Ajouter des livres
    $livres = [
        [
            'Le Petit Prince',
            'Antoine de Saint-Exupéry',
            15.99,
            15,
            'Un conte poétique et philosophique sur l\'amitié et le sens de la vie',
            "Le Petit Prince est une œuvre poétique et philosophique sous l'apparence d'un conte pour enfants. Publié en 1943, c'est l'histoire d'un pilote tombé en panne dans le désert du Sahara, où il rencontre un étrange petit garçon venu d'une autre planète. À travers leurs conversations, le Petit Prince raconte ses voyages et ses rencontres avec des personnages aussi absurdes qu'attachants : un roi, un vaniteux, un buveur, un allumeur de réverbères, un géographe... Chaque rencontre est une allégorie qui dénonce les travers des adultes et leur monde. Le livre aborde des thèmes universels comme l'amour, l'amitié, la solitude et le sens de la vie. La célèbre maxime 'On ne voit bien qu'avec le cœur, l'essentiel est invisible pour les yeux' résume la philosophie de cette œuvre intemporelle qui a touché des millions de lecteurs à travers le monde."
        ],
        [
            '1984',
            'George Orwell',
            18.50,
            8,
            'Une dystopie glaçante sur le totalitarisme et la surveillance de masse',
            "1984 est un roman d'anticipation publié en 1949 qui décrit un monde terrifiant où un régime totalitaire, dirigé par le 'Parti' et son chef emblématique 'Big Brother', contrôle chaque aspect de la vie des citoyens. L'histoire suit Winston Smith, un employé du ministère de la Vérité qui commence secrètement à remettre en question le système. Dans ce monde, l'histoire est constamment réécrite, la langue est appauvrie (novlangue), et la pensée elle-même est contrôlée par la 'police de la pensée'. Les écrans de surveillance sont omniprésents, et les relations amoureuses sont interdites. Le roman explore des thèmes comme la manipulation de l'information, la perte de liberté individuelle, et le pouvoir de la vérité. Des expressions comme 'Big Brother vous regarde' ou 'la guerre, c'est la paix' sont devenues des références culturelles majeures. Une œuvre visionnaire qui reste plus pertinente que jamais à l'ère du numérique."
        ],
        [
            'Harry Potter à l\'école des sorciers',
            'J.K. Rowling',
            22.00,
            20,
            'Le début des aventures du plus célèbre sorcier de notre époque',
            "Harry Potter à l'école des sorciers est le premier tome de la saga qui a enchanté des millions de lecteurs. L'histoire commence avec Harry, un orphelin maltraité par son oncle et sa tante, qui découvre le jour de ses 11 ans qu'il est en réalité un sorcier. Invité à intégrer l'école de sorcellerie Poudlard, il quitte le monde des Moldus pour un univers fascinant peuplé de créatures magiques, de sorts et de potions. À Poudlard, Harry se lie d'amitié avec Ron Weasley et Hermione Granger, et ensemble ils vont vivre leur première grande aventure en découvrant les secrets de l'école et en affrontant les forces du mal. Le livre introduit des personnages devenus mythiques : Dumbledore, Hagrid, Severus Rogue, et bien sûr le terrible Voldemort. Plus qu'une simple histoire de magie, c'est un récit sur l'amitié, le courage, et la lutte entre le bien et le mal. J.K. Rowling crée un monde riche et cohérent qui a donné naissance à un phénomène culturel mondial."
        ],
        [
            'Le Seigneur des Anneaux',
            'J.R.R. Tolkien',
            29.99,
            5,
            'L\'épopée fantastique qui a redéfini le genre',
            "Le Seigneur des Anneaux est l'œuvre magistrale de J.R.R. Tolkien qui a posé les fondations de la fantasy moderne. L'histoire se déroule en Terre du Milieu, un monde peuplé d'humains, d'elfes, de nains et de hobbits. Frodon Sacquet, un jeune hobbit, hérite d'un anneau magique qui se révèle être l'Anneau Unique, forgé par le Seigneur des Ténèbres Sauron pour conquérir le monde. Avec ses compagnons, Frodon entreprend un périlleux voyage pour détruire l'anneau dans les flammes de la Montagne du Destin, le seul endroit où il peut être anéanti. Le livre suit leur périple à travers la Comté, la Vieille Forêt, les mines de la Moria, et jusqu'à la séparation de la Communauté. Tolkien crée un univers d'une richesse inouïe avec ses langues, son histoire et sa mythologie. Le thème central est la lutte du bien contre le mal, mais aussi la tentation du pouvoir et la force de l'amitié face à l'adversité."
        ],
        [
            'L\'Alchimiste',
            'Paulo Coelho',
            16.90,
            12,
            'Un conte philosophique sur la poursuite de ses rêves',
            "L'Alchimiste est un conte philosophique magnifique qui a touché le cœur de millions de lecteurs. L'histoire suit Santiago, un jeune berger andalou qui rêve de trouver un trésor caché au pied des pyramides d'Égypte. Quittant son troupeau et sa vie confortable, il embarque pour un voyage qui le mènera bien au-delà de la quête matérielle. Tout au long de son périple à travers le désert, Santiago rencontre des personnages fascinants : un roi mystérieux, un marchand de cristal, un Anglais passionné d'alchimie, et une belle femme du désert nommée Fatima. Chaque rencontre lui enseigne une leçon sur la vie, l'amour et la poursuite de ce que l'auteur appelle 'la Légende Personnelle'. Le livre explore des thèmes universels comme le destin, les signes que la vie nous envoie, et l'importance d'écouter son cœur. Paulo Coelho nous livre une fable inspirante sur le courage de suivre ses rêves et la découverte que le véritable trésor se trouve souvent dans le voyage lui-même."
        ]
    ];
    
    $stmt = $db->prepare("INSERT INTO livres (titre, auteur, prix, stock, description_courte, description_longue) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach($livres as $livre) {
        $stmt->execute($livre);
    }
    
    echo "✅ Base de données créée avec succès !\n";
    echo "📚 " . count($livres) . " livres ajoutés\n";
    echo "👤 Admin: admin@bibliotheque.com / admin123\n";
    
} catch(PDOException $e) {
    die("❌ Erreur: " . $e->getMessage());
}
?>
