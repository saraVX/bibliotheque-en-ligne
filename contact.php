<?php
session_start();
require_once 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $sujet = $_POST['sujet'];
    $message = $_POST['message'];
    
    // Ici vous pourriez envoyer un email
    // Pour l'instant on simule l'envoi
    $success = "Message envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.";
    
    // Log pour voir les messages (optionnel)
    $log = "[" . date('Y-m-d H:i:s') . "] De: $nom ($email) - Sujet: $sujet\nMessage: $message\n\n";
    file_put_contents('messages.log', $log, FILE_APPEND);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Bibliothèque</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contact-container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        
        .contact-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: var(--shadow);
            animation: scaleIn 0.5s ease;
        }
        
        .contact-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .contact-header h1 {
            font-size: 2.5rem;
            font-weight: 300;
            color: var(--text);
        }
        
        .contact-header h1 span {
            color: var(--accent);
            font-weight: 700;
        }
        
        .contact-header p {
            color: var(--text-light);
            margin-top: 0.5rem;
        }
        
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
            text-align: center;
        }
        
        .info-item {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            transition: var(--transition);
        }
        
        .info-item:hover {
            transform: translateY(-5px);
        }
        
        .info-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .info-title {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .info-detail {
            color: var(--accent);
            font-size: 0.9rem;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            animation: slideIn 0.3s ease;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--accent);
            outline: none;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .info-note {
            margin-top: 2rem;
            padding: 1rem;
            background: #fff3cd;
            color: #856404;
            border-radius: 10px;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">📚 <span>Biblio</span>thèque</a>
            </div>
            <div class="nav-links">
                <a href="index.php">Accueil</a>
                <a href="contact.php">Contact</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="panier.php">Panier</a>
                    <a href="profil.php">Profil</a>
                    <a href="deconnexion.php">Déconnexion</a>
                <?php else: ?>
                    <a href="connexion.php">Connexion</a>
                    <a href="inscription.php">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="contact-container">
        <div class="contact-card">
            <div class="contact-header">
                <h1>📧 <span>Contactez</span>-nous</h1>
                <p>Une question ? Une suggestion ? Écrivez-nous !</p>
            </div>

            <?php if(isset($success)): ?>
                <div class="success-message">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <div class="contact-info">
                <div class="info-item">
                    <div class="info-icon">📧</div>
                    <div class="info-title">Email</div>
                    <div class="info-detail">contact@bibliotheque.com</div>
                </div>
                <div class="info-item">
                    <div class="info-icon">📞</div>
                    <div class="info-title">Téléphone</div>
                    <div class="info-detail">01 23 45 67 89</div>
                </div>
                <div class="info-item">
                    <div class="info-icon">📍</div>
                    <div class="info-title">Adresse</div>
                    <div class="info-detail">123 Rue des Livres, Paris</div>
                </div>
            </div>

            <form method="post">
                <div class="form-group">
                    <label>Nom complet</label>
                    <input type="text" name="nom" required placeholder="Votre nom">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="votre@email.com">
                </div>
                
                <div class="form-group">
                    <label>Sujet</label>
                    <select name="sujet" required>
                        <option value="">Choisissez un sujet</option>
                        <option value="question">Question sur un livre</option>
                        <option value="commande">Problème de commande</option>
                        <option value="livraison">Question sur la livraison</option>
                        <option value="suggestion">Suggestion</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" required placeholder="Votre message..."></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Envoyer le message</button>
            </form>

            <div class="info-note">
                ⚡ Les messages sont sauvegardés localement. Pour recevoir les emails, configurez un serveur SMTP.
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Bibliothèque - Tous droits réservés</p>
    </footer>
</body>
</html>
