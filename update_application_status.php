<?php
require_once 'config.php'; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = intval($_POST['application_id']);
    $status = $_POST['statu'];

    if ($applicationId > 0 && in_array($status, [0, 1])) {
        try {
            $stmt = $conn->prepare("UPDATE applications SET statu = ? WHERE id = ?");
            $stmt->execute([$status, $applicationId]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Paramètres invalides.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
