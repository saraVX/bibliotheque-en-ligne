<?php
session_start();
require_once 'config.php';

// Récupérer tous les livres
$livres = $db->query("SELECT * FROM livres ORDER BY categorie, id")->fetchAll();

// Compter par catégorie
$stats_categories = [
    'classique' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'classique'")->fetchColumn(),
    'fantasy' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'fantasy'")->fetchColumn(),
    'sf' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'sf'")->fetchColumn(),
    'horreur' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'horreur'")->fetchColumn(),
    'philosophie' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'philosophie'")->fetchColumn(),
];

$total_livres = count($livres);
$total_auteurs = $db->query("SELECT COUNT(DISTINCT auteur) FROM livres")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliothèque - L'élégance des mots</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques à l'accueil */
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 3rem;
            animation: fadeInUp 1s ease 0.4s both;
        }
        
        .hero-stat {
            text-align: center;
        }
        
        .hero-stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent);
        }
        
        .hero-stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .featured-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 4rem 0;
            margin: 4rem 0;
        }
        
        .quote {
            text-align: center;
            font-size: 1.5rem;
            font-style: italic;
            color: var(--text-light);
            max-width: 800px;
            margin: 0 auto 3rem;
            position: relative;
        }
        
        .quote::before,
        .quote::after {
            content: '"';
            font-size: 4rem;
            color: var(--accent);
            opacity: 0.3;
            position: absolute;
        }
        
        .quote::before {
            left: -2rem;
            top: -1rem;
        }
        
        .quote::after {
            right: -2rem;
            bottom: -1rem;
        }
        
        .categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }
        
        .category {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            box-shadow: var(--shadow);
            border: 2px solid transparent;
        }
        
        .category:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            border-color: var(--accent);
        }
        
        .category.active {
            border-color: var(--accent);
            background: linear-gradient(135deg, var(--white) 0%, #f8f9fa 100%);
        }
        
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .category-name {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .category-count {
            font-size: 0.8rem;
            color: var(--accent);
            margin-top: 0.3rem;
            font-weight: 500;
        }
        
        .book-card {
            animation: scaleIn 0.5s ease;
            animation-fill-mode: both;
            position: relative;
        }
        
        .category-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent);
            color: var(--white);
            padding: 0.3rem 1rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 10;
        }
        
        <?php foreach($livres as $index => $livre): ?>
        .book-card:nth-child(<?= $index + 1 ?>) {
            animation-delay: <?= $index * 0.05 ?>s;
        }
        <?php endforeach; ?>
        
        .no-results {
            text-align: center;
            padding: 4rem;
            color: var(--text-light);
            font-size: 1.2rem;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <!-- Header élégant -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">📚 <span>Biblio</span>thèque</a>
            </div>
            <div class="nav-links">
                <a href="index.php">Accueil</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="panier.php">Mon Panier</a>
                    <a href="mes_commandes.php">Mes Commandes</a>
                    <a href="profil.php">Mon Profil</a>
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                        <a href="admin_dashboard.php">Administration</a>
                    <?php endif; ?>
                    <span class="user-info">👋 <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
                    <a href="deconnexion.php" class="btn btn-small">Déconnexion</a>
                <?php else: ?>
                    <a href="connexion.php" class="btn btn-small">Connexion</a>
                    <a href="inscription.php" class="btn btn-small btn-accent">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero section élégante -->
    <section class="hero">
        <h1>L'<strong>élégance</strong> des mots</h1>
        <p>Découvrez notre collection soigneusement sélectionnée de livres</p>
        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-number"><?= $total_livres ?></div>
                <div class="hero-stat-label">Livres</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-number"><?= $total_auteurs ?></div>
                <div class="hero-stat-label">Auteurs</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-number">5</div>
                <div class="hero-stat-label">Catégories</div>
            </div>
        </div>
    </section>

    <!-- Catégories avec compteurs -->
    <div class="container">
        <div class="categories">
            <div class="category active" onclick="filterBooks('all')" id="cat-all">
                <div class="category-icon">📚</div>
                <div class="category-name">Tous</div>
                <div class="category-count"><?= $total_livres ?></div>
            </div>
            <div class="category" onclick="filterBooks('classique')" id="cat-classique">
                <div class="category-icon">📖</div>
                <div class="category-name">Classique</div>
                <div class="category-count"><?= $stats_categories['classique'] ?></div>
            </div>
            <div class="category" onclick="filterBooks('fantasy')" id="cat-fantasy">
                <div class="category-icon">🧙</div>
                <div class="category-name">Fantasy</div>
                <div class="category-count"><?= $stats_categories['fantasy'] ?></div>
            </div>
            <div class="category" onclick="filterBooks('sf')" id="cat-sf">
                <div class="category-icon">🚀</div>
                <div class="category-name">Sci-Fi</div>
                <div class="category-count"><?= $stats_categories['sf'] ?></div>
            </div>
            <div class="category" onclick="filterBooks('horreur')" id="cat-horreur">
                <div class="category-icon">👻</div>
                <div class="category-name">Horreur</div>
                <div class="category-count"><?= $stats_categories['horreur'] ?></div>
            </div>
            <div class="category" onclick="filterBooks('philosophie')" id="cat-philosophie">
                <div class="category-icon">🤔</div>
                <div class="category-name">Philo</div>
                <div class="category-count"><?= $stats_categories['philosophie'] ?></div>
            </div>
        </div>
    </div>

    <!-- Citation -->
    <div class="featured-section">
        <div class="container">
            <div class="quote">
                Un livre est un rêve que vous tenez dans vos mains.
            </div>
        </div>
    </div>

    <!-- Livres -->
    <div class="container">
        <div class="section-title">
            <h2>Notre sélection</h2>
        </div>
        
        <div class="books-grid" id="booksGrid">
            <?php foreach($livres as $livre): ?>
            <div class="book-card" onclick="window.location.href='livre.php?id=<?= $livre['id'] ?>'" data-category="<?= $livre['categorie'] ?>">
                <div class="category-badge">
                    <?php 
                    $icones = [
                        'classique' => '📖',
                        'fantasy' => '🧙',
                        'sf' => '🚀',
                        'horreur' => '👻',
                        'philosophie' => '🤔'
                    ];
                    echo $icones[$livre['categorie']] . ' ' . $livre['categorie'];
                    ?>
                </div>
                <div class="book-image">
                    <!-- Image placeholder avec couleur selon catégorie -->
                </div>
                <div class="book-content">
                    <h3 class="book-title"><?= htmlspecialchars($livre['titre']) ?></h3>
                    <div class="book-author"><?= htmlspecialchars($livre['auteur']) ?></div>
                    <p class="book-description">
                        <?= substr(htmlspecialchars($livre['description_courte']), 0, 120) ?>...
                    </p>
                    <div class="book-footer">
                        <span class="book-price"><?= number_format($livre['prix'], 2) ?> €</span>
                        <span class="book-stock">Stock: <?= $livre['stock'] ?></span>
                    </div>
                    <?php if(isset($_SESSION['user_id']) && $livre['stock'] > 0): ?>
                        <button class="btn btn-small btn-success" style="width: 100%; margin-top: 1rem;" onclick="event.stopPropagation(); addToCart(<?= $livre['id'] ?>, this)">
                            <span>Ajouter au panier</span>
                        </button>
                    <?php elseif(!isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-small" style="width: 100%; margin-top: 1rem; background: #95a5a6;" onclick="event.stopPropagation(); window.location.href='connexion.php'">
                            Connectez-vous
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Newsletter -->
    <div class="featured-section">
        <div class="container" style="text-align: center;">
            <h2 style="margin-bottom: 1rem;">Restez informé</h2>
            <p style="margin-bottom: 2rem; color: var(--text-light);">Recevez nos dernières actualités et offres exclusives</p>
            <form style="max-width: 500px; margin: 0 auto; display: flex; gap: 1rem;">
                <input type="email" placeholder="Votre email" style="flex: 1; padding: 1rem; border: 1px solid #e0e0e0; border-radius: 30px;">
                <button type="submit" class="btn">S'abonner</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Bibliothèque - L'élégance des mots. Tous droits réservés.</p>
    </footer>

    <!-- Toast notification -->
    <div id="toast" class="toast"></div>

    <script>
        // Fonction d'ajout au panier avec animation
        function addToCart(bookId, button) {
            event.stopPropagation();
            
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner"></span>';
            button.disabled = true;
            
            fetch('ajouter_panier.php?id=' + bookId)
                .then(response => {
                    if(response.ok) {
                        showToast('✅ Livre ajouté au panier !', 'success');
                        button.innerHTML = '✓ Ajouté !';
                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.disabled = false;
                        }, 2000);
                    } else {
                        throw new Error('Erreur');
                    }
                })
                .catch(error => {
                    showToast('❌ Erreur lors de l\'ajout', 'error');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
        }

        // Fonction de notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + (type === 'success' ? '' : 'error');
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Filtre des livres par catégorie
        function filterBooks(category) {
            const books = document.querySelectorAll('.book-card');
            const categories = document.querySelectorAll('.category');
            let visibleCount = 0;
            
            // Mettre à jour la classe active
            categories.forEach(cat => {
                cat.classList.remove('active');
            });
            document.getElementById('cat-' + (category === 'all' ? 'all' : category)).classList.add('active');
            
            // Filtrer les livres
            books.forEach(book => {
                if(category === 'all' || book.dataset.category === category) {
                    book.style.display = 'block';
                    setTimeout(() => {
                        book.style.opacity = '1';
                        book.style.transform = 'scale(1)';
                    }, 10);
                    visibleCount++;
                } else {
                    book.style.opacity = '0';
                    book.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        book.style.display = 'none';
                    }, 300);
                }
            });
            
            // Afficher message si aucun résultat
            let noResults = document.querySelector('.no-results');
            if(visibleCount === 0) {
                if(!noResults) {
                    noResults = document.createElement('div');
                    noResults.className = 'no-results';
                    noResults.textContent = 'Aucun livre dans cette catégorie';
                    document.getElementById('booksGrid').appendChild(noResults);
                }
            } else if(noResults) {
                noResults.remove();
            }
        }

        // Animation au scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        document.querySelectorAll('.book-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';
            observer.observe(card);
        });

        // Animation des catégories
        document.querySelectorAll('.category').forEach(category => {
            category.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            category.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
