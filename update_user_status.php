<?php
require_once "config.php";

// Vérifiez si les données sont bien envoyées via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $status = isset($_POST['status']) ? intval($_POST['status']) : 0;

    // Vérifiez que les données sont valides
    if ($userId > 0) {
        try {
            // Mise à jour du statut de l'utilisateur
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$status, $userId]);

            // Réponse en JSON
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID utilisateur non valide.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
