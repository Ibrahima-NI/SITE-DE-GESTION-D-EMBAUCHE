<?php
require_once "header.php";
require_once "config.php";

// Vérifier si l'utilisateur est déjà connecté
if (!isset($_SESSION['user_id'])) {
    // Détruire la session
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    // Rediriger vers la page de connexion
    header("Location: login.php?message=deconnected");
    exit();
}

// Vérifier le type d'utilisateur pour restreindre l'accès à cette page
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    // Si l'utilisateur n'est pas admin, il sera rediriger vers la page login
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Récupérer de l'utilisateurs
    try {
        $stmt = $conn->query("SELECT * FROM users WHERE id=$user_id");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur lors de la récupération de l'utilisateurs : " . htmlspecialchars($e->getMessage()));
    }
} else {
    header('Location:gestions_users.php');
}



?>



<main>
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                <div class="text-center">
                    <h1>Gestions des utilisateurs</h1>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="card p-3">
                <p>
                    <strong>Nom de l'utilisateur =</strong> <?= $user['name'] ?>
                </p>
                <p>
                    <strong>Email de l'utilisateur =</strong> <?= $user['email'] ?>
                </p>
                <p>
                    <strong>Role =</strong> <?= $user['role'] ?>
                </p>
                <p>
                    <strong>Status du compte =</strong> <?= ($user['status']) ? 'Activer' : 'Désactiver' ?>
                </p>
                <p>
                    <strong>Status de son email =</strong> <?= ($user['email_verified']) ? 'Confirmer' : 'non-Confirmer' ?>
                </p>
            </div>
        </div>
    </div>


    <!--Pageheader end-->
</main>