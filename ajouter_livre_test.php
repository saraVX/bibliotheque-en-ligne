<?php
require_once 'config.php';

$livres = [
    ['titre' => 'Le Petit Prince', 'auteur' => 'Antoine de Saint-Exupéry', 'prix' => 15.99, 'description' => 'Un conte poétique'],
    ['titre' => '1984', 'auteur' => 'George Orwell', 'prix' => 18.50, 'description' => 'Une dystopie'],
    ['titre' => 'Harry Potter', 'auteur' => 'J.K. Rowling', 'prix' => 22.00, 'description' => 'La magie'],
    ['titre' => 'Le Seigneur des Anneaux', 'auteur' => 'J.R.R. Tolkien', 'prix' => 29.99, 'description' => 'Fantasy'],
    ['titre' => "L'Alchimiste", 'auteur' => 'Paulo Coelho', 'prix' => 16.90, 'description' => 'Philosophique']
];

$stmt = $db->prepare("INSERT INTO livres (titre, auteur, prix, description) VALUES (?, ?, ?, ?)");

foreach($livres as $livre) {
    $stmt->execute([$livre['titre'], $livre['auteur'], $livre['prix'], $livre['description']]);
}

echo "Livres ajoutés avec succès !";
?>
