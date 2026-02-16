<?php
session_start();
require_once 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $check = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $check->execute([$email]);
    
    if($check->fetch()) {
        $error = "Cet email est déjà utilisé";
    } else {
        $stmt = $db->prepare("INSERT INTO utilisateurs (nom, email, password, role) VALUES (?, ?, ?, 'user')");
        
        if($stmt->execute([$nom, $email, $password])) {
            header('Location: connexion.php?success=1');
            exit();
        } else {
            $error = "Erreur lors de l'inscription";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Bibliothèque</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .register-card {
            background: var(--white);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: scaleIn 0.5s ease;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header h1 {
            font-size: 2rem;
            font-weight: 300;
            color: var(--text);
        }
        
        .register-header h1 span {
            color: var(--accent);
            font-weight: 700;
        }
        
        .register-header p {
            color: var(--text-light);
            margin-top: 0.5rem;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon input {
            padding-left: 3rem;
        }
        
        .input-icon::before {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            z-index: 1;
        }
        
        .input-icon.user::before {
            content: '👤';
        }
        
        .input-icon.email::before {
            content: '✉️';
        }
        
        .input-icon.password::before {
            content: '🔒';
        }
        
        .password-requirements {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.3rem;
        }
        
        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .terms {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .terms a {
            color: var(--accent);
            text-decoration: none;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <h1>📚 <span>Biblio</span>thèque</h1>
            <p>Créez votre compte</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="error-message">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group input-icon user">
                <input type="text" name="nom" placeholder="Nom complet" required>
            </div>
            
            <div class="form-group input-icon email">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            
            <div class="form-group input-icon password">
                <input type="password" name="password" placeholder="Mot de passe" required>
                <div class="password-requirements">
                    Minimum 6 caractères
                </div>
            </div>
            
            <label class="terms">
                <input type="checkbox" required> J'accepte les <a href="#">conditions d'utilisation</a>
            </label>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 1rem;">S'inscrire</button>
        </form>
        
        <div class="login-link">
            Déjà inscrit? <a href="connexion.php">Connectez-vous</a>
        </div>
    </div>
</body>
</html>
