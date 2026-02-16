<?php
require_once 'admin_auth.php';
requireAdmin();

// Statistiques
$total_livres = $db->query("SELECT COUNT(*) FROM livres")->fetchColumn();
$total_utilisateurs = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$total_commandes = $db->query("SELECT COUNT(*) FROM panier")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Bibliothèque</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f5f9;
        }
        .header {
            background: #1e293b;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 1.5rem;
            font-weight: 500;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            opacity: 0.8;
        }
        .header a:hover {
            opacity: 1;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #64748b;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: 600;
            color: #1e293b;
        }
        .nav-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .nav-link {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            color: #1e293b;
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }
        .nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-color: #3b82f6;
        }
        .nav-link h4 {
            margin-bottom: 0.5rem;
            color: #3b82f6;
        }
        .nav-link p {
            color: #64748b;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Administration</h1>
        <div>
            <span>👤 <?= $_SESSION['user_nom'] ?></span>
            <a href="index.php">Site</a>
            <a href="deconnexion.php">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>Livres</h3>
                <div class="number"><?= $total_livres ?></div>
            </div>
            <div class="stat-card">
                <h3>Utilisateurs</h3>
                <div class="number"><?= $total_utilisateurs ?></div>
            </div>
            <div class="stat-card">
                <h3>Articles dans paniers</h3>
                <div class="number"><?= $total_commandes ?></div>
            </div>
        </div>

        <div class="nav-links">
            <a href="admin_livres.php" class="nav-link">
                <h4>📖 Gestion des livres</h4>
                <p>Ajouter, modifier ou supprimer des livres</p>
            </a>
            <a href="admin_utilisateurs.php" class="nav-link">
                <h4>👥 Gestion des utilisateurs</h4>
                <p>Voir et gérer les utilisateurs</p>
            </a>
            <a href="admin_commandes.php" class="nav-link">
                <h4>📦 Commandes</h4>
                <p>Suivre les commandes</p>
            </a>
            <a href="admin_stats.php" class="nav-link">
                <h4>📊 Statistiques</h4>
                <p>Analyses et rapports</p>
            </a>
        </div>
    </div>
</body>
</html>
