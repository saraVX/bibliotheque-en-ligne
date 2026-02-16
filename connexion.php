<?php
session_start();
require_once 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_role'] = $user['role'];
        
        if($user['role'] == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: index.php');
        }
        exit();
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Bibliothèque</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: var(--white);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: scaleIn 0.5s ease;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            font-size: 2rem;
            font-weight: 300;
            color: var(--text);
        }
        
        .login-header h1 span {
            color: var(--accent);
            font-weight: 700;
        }
        
        .login-header p {
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
        
        .input-icon.email::before {
            content: '✉️';
        }
        
        .input-icon.password::before {
            content: '🔒';
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        
        .remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
        }
        
        .forgot-link {
            color: var(--accent);
            text-decoration: none;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        .register-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        .register-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .demo-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .demo-info p {
            margin: 0.3rem 0;
        }
        
        .demo-info strong {
            color: var(--accent);
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
    <div class="login-card">
        <div class="login-header">
            <h1>📚 <span>Biblio</span>thèque</h1>
            <p>Connectez-vous à votre espace</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="error-message">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group input-icon email">
                <input type="email" name="email" placeholder="Votre email" required>
            </div>
            
            <div class="form-group input-icon password">
                <input type="password" name="password" placeholder="Votre mot de passe" required>
            </div>
            
            <div class="remember-forgot">
                <label class="remember">
                    <input type="checkbox"> Se souvenir de moi
                </label>
                <a href="#" class="forgot-link">Mot de passe oublié?</a>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Se connecter</button>
        </form>
        
        <div class="register-link">
            Pas encore de compte? <a href="inscription.php">Inscrivez-vous</a>
        </div>
        
        <div class="demo-info">
            <p><strong>🔑 Compte admin:</strong> admin@bibliotheque.com / admin123</p>
            <p><strong>👤 Compte test:</strong> test@test.com / test123 (à créer)</p>
        </div>
    </div>
</body>
</html>
