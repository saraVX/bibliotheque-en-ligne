<?php
session_start();
require_once 'config.php';
require_once 'check_admin.php';
requireAdmin();

// AJOUTER UN LIVRE
if(isset($_POST['ajouter'])) {
    try {
        $stmt = $db->prepare("INSERT INTO livres (titre, auteur, categorie, prix, stock, description_courte, description_longue) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $_POST['titre'],
            $_POST['auteur'],
            $_POST['categorie'],
            $_POST['prix'],
            $_POST['stock'],
            $_POST['description_courte'],
            $_POST['description_longue']
        ]);
        
        if($result) {
            $message = "✅ Livre ajouté avec succès !";
            $message_type = "success";
        } else {
            $message = "❌ Erreur lors de l'ajout";
            $message_type = "error";
        }
    } catch(Exception $e) {
        $message = "❌ Erreur: " . $e->getMessage();
        $message_type = "error";
    }
}

// MODIFIER UN LIVRE
if(isset($_POST['modifier'])) {
    try {
        $stmt = $db->prepare("UPDATE livres SET titre=?, auteur=?, categorie=?, prix=?, stock=?, description_courte=?, description_longue=? WHERE id=?");
        $result = $stmt->execute([
            $_POST['titre'],
            $_POST['auteur'],
            $_POST['categorie'],
            $_POST['prix'],
            $_POST['stock'],
            $_POST['description_courte'],
            $_POST['description_longue'],
            $_POST['id']
        ]);
        
        if($result) {
            $message = "✅ Livre modifié avec succès !";
            $message_type = "success";
        } else {
            $message = "❌ Erreur lors de la modification";
            $message_type = "error";
        }
    } catch(Exception $e) {
        $message = "❌ Erreur: " . $e->getMessage();
        $message_type = "error";
    }
}

// SUPPRIMER UN LIVRE
if(isset($_GET['supprimer'])) {
    try {
        $stmt = $db->prepare("DELETE FROM livres WHERE id = ?");
        $result = $stmt->execute([$_GET['supprimer']]);
        
        if($result) {
            header('Location: admin_livres.php?success=1');
            exit;
        }
    } catch(Exception $e) {
        $message = "❌ Erreur: " . $e->getMessage();
        $message_type = "error";
    }
}

// Récupérer tous les livres
$livres = $db->query("SELECT * FROM livres ORDER BY id DESC")->fetchAll();

// Compter par catégorie
$stats = [
    'total' => count($livres),
    'classique' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'classique'")->fetchColumn(),
    'fantasy' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'fantasy'")->fetchColumn(),
    'sf' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'sf'")->fetchColumn(),
    'horreur' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'horreur'")->fetchColumn(),
    'philosophie' => $db->query("SELECT COUNT(*) FROM livres WHERE categorie = 'philosophie'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des livres - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            animation: slideIn 0.3s ease;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent);
        }
        
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }
        
        .form-section h2 {
            margin-bottom: 1.5rem;
            color: var(--text);
            font-weight: 300;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tr:hover td {
            background: #f8f9fa;
        }
        
        .categorie-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .categorie-classique { background: #e3f2fd; color: #0d47a1; }
        .categorie-fantasy { background: #f3e5f5; color: #4a148c; }
        .categorie-sf { background: #e8f5e9; color: #1b5e20; }
        .categorie-horreur { background: #ffebee; color: #b71c1c; }
        .categorie-philosophie { background: #fff3e0; color: #bf360c; }
        
        .btn-edit {
            padding: 0.3rem 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 0.5rem;
        }
        
        .btn-delete {
            padding: 0.3rem 1rem;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-content h2 {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">📚 <span>Admin</span> Livres</div>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="index.php">Site</a>
                <a href="deconnexion.php">Déconnexion</a>
            </div>
        </div>
    </div>

    <div class="admin-container">
        <?php if(isset($message)): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['success'])): ?>
            <div class="message success">
                ✅ Livre supprimé avec succès !
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total livres</h3>
                <div class="stat-number"><?= $stats['total'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Classiques</h3>
                <div class="stat-number"><?= $stats['classique'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Fantasy</h3>
                <div class="stat-number"><?= $stats['fantasy'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Sci-Fi</h3>
                <div class="stat-number"><?= $stats['sf'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Horreur</h3>
                <div class="stat-number"><?= $stats['horreur'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Philo</h3>
                <div class="stat-number"><?= $stats['philosophie'] ?></div>
            </div>
        </div>

        <!-- Formulaire d'ajout -->
        <div class="form-section">
            <h2>➕ Ajouter un nouveau livre</h2>
            <form method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="titre" required>
                    </div>
                    <div class="form-group">
                        <label>Auteur</label>
                        <input type="text" name="auteur" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="categorie" required>
                            <option value="classique">Classique</option>
                            <option value="fantasy">Fantasy</option>
                            <option value="sf">Science-Fiction</option>
                            <option value="horreur">Horreur</option>
                            <option value="philosophie">Philosophie</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Prix (€)</label>
                        <input type="number" step="0.01" name="prix" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" value="10" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description courte</label>
                    <textarea name="description_courte" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Description longue</label>
                    <textarea name="description_longue" rows="5" required></textarea>
                </div>
                
                <button type="submit" name="ajouter" class="btn">Ajouter le livre</button>
            </form>
        </div>

        <!-- Liste des livres -->
        <h2 style="margin: 2rem 0 1rem;">📚 Liste des livres</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($livres as $livre): ?>
                    <tr>
                        <td><?= $livre['id'] ?></td>
                        <td><?= htmlspecialchars($livre['titre']) ?></td>
                        <td><?= htmlspecialchars($livre['auteur']) ?></td>
                        <td>
                            <span class="categorie-badge categorie-<?= $livre['categorie'] ?>">
                                <?= $livre['categorie'] ?>
                            </span>
                        </td>
                        <td><?= number_format($livre['prix'], 2) ?> €</td>
                        <td><?= $livre['stock'] ?></td>
                        <td>
                            <button class="btn-edit" onclick="editBook(<?= htmlspecialchars(json_encode($livre)) ?>)">✏️ Modifier</button>
                            <a href="?supprimer=<?= $livre['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer ce livre ?')">🗑️ Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de modification -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>✏️ Modifier le livre</h2>
            <form method="post">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="titre" id="edit_titre" required>
                    </div>
                    <div class="form-group">
                        <label>Auteur</label>
                        <input type="text" name="auteur" id="edit_auteur" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="categorie" id="edit_categorie" required>
                            <option value="classique">Classique</option>
                            <option value="fantasy">Fantasy</option>
                            <option value="sf">Science-Fiction</option>
                            <option value="horreur">Horreur</option>
                            <option value="philosophie">Philosophie</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Prix (€)</label>
                        <input type="number" step="0.01" name="prix" id="edit_prix" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" id="edit_stock" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description courte</label>
                    <textarea name="description_courte" id="edit_description_courte" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Description longue</label>
                    <textarea name="description_longue" id="edit_description_longue" rows="5" required></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" name="modifier" class="btn">Modifier</button>
                    <button type="button" onclick="closeModal()" class="btn" style="background: var(--danger);">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editBook(livre) {
            document.getElementById('edit_id').value = livre.id;
            document.getElementById('edit_titre').value = livre.titre;
            document.getElementById('edit_auteur').value = livre.auteur;
            document.getElementById('edit_categorie').value = livre.categorie;
            document.getElementById('edit_prix').value = livre.prix;
            document.getElementById('edit_stock').value = livre.stock;
            document.getElementById('edit_description_courte').value = livre.description_courte;
            document.getElementById('edit_description_longue').value = livre.description_longue;
            
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if(event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
