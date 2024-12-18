<?php

require_once "config.php";

if (isset($_POST['status']) && isset($_POST['id'])) {
    $status = $_POST['status'];
    $userId = $_POST['id'];



    // Mettre à jour le statut de l'utilisateur dans la base de données
    $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $userId]);

    // Réponse AJAX
    echo "Statut mis à jour avec succès";
}
