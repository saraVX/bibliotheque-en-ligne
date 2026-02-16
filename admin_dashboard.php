<?php
session_start();
require_once 'config.php';
require_once 'check_admin.php';
requireAdmin(); // Seulement les admins peuvent accéder

// Statistiques
$total_livres = $db->query("SELECT COUNT(*) FROM livres")->fetchColumn();
$total_users = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'user'")->fetchColumn();
$total_commandes = $db->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$stock_total = $db->query("SELECT SUM(stock) FROM livres")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
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
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }
        .header a:hover {
            background: #4a5568;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
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
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2d3748;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .menu-item {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            text-decoration: none;
            color: #2d3748;
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
            display: block;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        .menu-item h3 {
            margin-bottom: 0.5rem;
            color: #667eea;
        }
        .menu-item p {
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Administration - Bibliothèque</h1>
        <div>
            <span>👤 <?= htmlspecialchars($_SESSION['user_nom']) ?> (Admin)</span>
            <a href="index.php">Voir le site</a>
            <a href="deconnexion.php">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Livres</h3>
                <div class="stat-number"><?= $total_livres ?></div>
            </div>
            <div class="stat-card">
                <h3>Stock total</h3>
                <div class="stat-number"><?= $stock_total ?></div>
            </div>
            <div class="stat-card">
                <h3>Utilisateurs</h3>
                <div class="stat-number"><?= $total_users ?></div>
            </div>
            <div class="stat-card">
                <h3>Commandes</h3>
                <div class="stat-number"><?= $total_commandes ?></div>
            </div>
        </div>

        <div class="menu-grid">
            <a href="admin_livres.php" class="menu-item">
                <h3>📖 Gestion des livres</h3>
                <p>Ajouter, modifier, supprimer des livres et gérer les stocks</p>
            </a>
            <a href="admin_commandes.php" class="menu-item">
                <h3>📦 Gestion des commandes</h3>
                <p>Voir et gérer les commandes clients</p>
            </a>
            <a href="admin_utilisateurs.php" class="menu-item">
                <h3>👥 Gestion des utilisateurs</h3>
                <p>Gérer les comptes clients</p>
            </a>
        </div>
    </div>
</body>
</html>
