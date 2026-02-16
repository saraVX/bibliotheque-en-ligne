<?php
session_start();
require_once 'config.php';
require_once 'check_admin.php';
requireUser();

$user_id = $_SESSION['user_id'];

// Récupérer les infos de l'utilisateur
$user = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$user->execute([$user_id]);
$user = $user->fetch();

// Mettre à jour le profil
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    $ville = $_POST['ville'];
    $code_postal = $_POST['code_postal'];
    
    // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
    $check = $db->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
    $check->execute([$email, $user_id]);
    
    if(!$check->fetch()) {
        $update = $db->prepare("UPDATE utilisateurs SET nom=?, email=? WHERE id=?");
        $update->execute([$nom, $email, $user_id]);
        $_SESSION['user_nom'] = $nom;
        $success = "Profil mis à jour avec succès";
        
        // Recharger les infos
        $user = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $user->execute([$user_id]);
        $user = $user->fetch();
    } else {
        $error = "Cet email est déjà utilisé";
    }
}

// Changer le mot de passe
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $ancien = $_POST['ancien_password'];
    $nouveau = $_POST['nouveau_password'];
    $confirm = $_POST['confirm_password'];
    
    if(password_verify($ancien, $user['password'])) {
        if($nouveau === $confirm) {
            $new_password = password_hash($nouveau, PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
            $update->execute([$new_password, $user_id]);
            $success = "Mot de passe changé avec succès";
        } else {
            $error = "Les nouveaux mots de passe ne correspondent pas";
        }
    } else {
        $error = "Ancien mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mon Profil</title>
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
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 2rem;
        }
        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a5568;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
            font-size: 1rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover {
            background: #5a67d8;
        }
        .section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .section h2 {
            color: #4a5568;
            margin-bottom: 1rem;
        }
        .nav-links {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .nav-links a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👤 Mon Profil</h1>
        <div>
            <a href="index.php">Accueil</a>
            <a href="panier.php">Panier</a>
            <a href="mes_commandes.php">Mes commandes</a>
            <a href="deconnexion.php">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <?php if(isset($success)): ?>
            <div class="message"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="section">
                <h2>Informations personnelles</h2>
                <form method="post">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <button type="submit" name="update_profil" class="btn">Mettre à jour</button>
                </form>
            </div>

            <div class="section">
                <h2>Changer le mot de passe</h2>
                <form method="post">
                    <div class="form-group">
                        <label>Ancien mot de passe</label>
                        <input type="password" name="ancien_password" required>
                    </div>
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" name="nouveau_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn">Changer le mot de passe</button>
                </form>
            </div>

            <div class="nav-links">
                <a href="mes_commandes.php">📦 Voir mes commandes</a>
            </div>
        </div>
    </div>
</body>
</html>
