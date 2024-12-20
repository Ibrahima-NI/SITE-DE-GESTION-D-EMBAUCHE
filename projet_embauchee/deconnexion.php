<?php
// Démarrer la session
session_start();

// Vérifier si une session existe
if (isset($_SESSION['user_id'])) {
    // Détruire toutes les données de la session
    session_unset(); // Libère toutes les variables de session
    session_destroy(); // Détruit la session
}

// Rediriger vers la page de connexion ou la page d'accueil
header("Location: index.php");
exit();
