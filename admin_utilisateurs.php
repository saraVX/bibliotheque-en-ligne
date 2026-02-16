<?php
session_start();
require_once 'config.php';
require_once 'check_admin.php';
requireAdmin();

// Supprimer un utilisateur
if(isset($_GET['supprimer'])) {
    // Vérifier que ce n'est pas l'admin principal
    $user = $db->prepare("SELECT email FROM utilisateurs WHERE id = ?");
    $user->execute([$_GET['supprimer']]);
    $user = $user->fetch();
    
    if($user && $user['email'] !== 'admin@bibliotheque.com') {
        $delete = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $delete->execute([$_GET['supprimer']]);
        $message = "Utilisateur supprimé avec succès";
    } else {
        $error = "Impossible de supprimer l'administrateur principal";
    }
}

// Modifier le rôle d'un utilisateur
if(isset($_POST['changer_role'])) {
    if($_POST['email'] !== 'admin@bibliotheque.com') {
        $update = $db->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
        $update->execute([$_POST['role'], $_POST['user_id']]);
        $message = "Rôle modifié avec succès";
    }
}

// Récupérer tous les utilisateurs
$utilisateurs = $db->query("
    SELECT * FROM utilisateurs 
    ORDER BY role DESC, date_inscription DESC
")->fetchAll();

// Statistiques
$stats = [
    'total' => count($utilisateurs),
    'admins' => $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'admin'")->fetchColumn(),
    'users' => $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'user'")->fetchColumn(),
    'nouveaux' => $db->query("SELECT COUNT(*) FROM utilisateurs WHERE date_inscription > date('now', '-7 days')")->fetchColumn()
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des utilisateurs - Admin</title>
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
        .error {
            background: #f56565;
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
        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #4a5568;
            color: white;
            padding: 1rem;
            text-align: left;
        }
        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .role-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .role-admin {
            background: #fefcbf;
            color: #744210;
        }
        .role-user {
            background: #bee3f8;
            color: #1e4a6b;
        }
        .btn {
            padding: 0.3rem 0.8rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-danger {
            background: #f56565;
        }
        .btn-danger:hover {
            background: #e53e3e;
        }
        select {
            padding: 0.3rem;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
        }
        .admin-warning {
            background: #fefcbf;
            color: #744210;
            padding: 0.5rem;
            border-radius: 5px;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👥 Gestion des utilisateurs</h1>
        <div>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="deconnexion.php">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <?php if(isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Total utilisateurs</h3>
                <div class="stat-number"><?= $stats['total'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Administrateurs</h3>
                <div class="stat-number"><?= $stats['admins'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Clients</h3>
                <div class="stat-number"><?= $stats['users'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Nouveaux (7 jours)</h3>
                <div class="stat-number"><?= $stats['nouveaux'] ?></div>
            </div>
        </div>

        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($utilisateurs as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['nom']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="role-badge role-<?= $user['role'] ?>">
                                <?= $user['role'] ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($user['date_inscription'])) ?></td>
                        <td>
                            <?php if($user['email'] !== 'admin@bibliotheque.com'): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="email" value="<?= $user['email'] ?>">
                                    <select name="role">
                                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <button type="submit" name="changer_role" class="btn">Modifier</button>
                                </form>
                                <a href="?supprimer=<?= $user['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
                            <?php else: ?>
                                <span style="color: #718096;">Admin principal</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="admin-warning">
            <strong>⚠️ Note:</strong> L'administrateur principal (admin@bibliotheque.com) ne peut pas être supprimé.
        </div>
    </div>
</body>
</html>
