<?php
require_once "header.php";
require_once "config.php";

// Vérifier si l'utilisateur est déjà connecté
if (!isset($_SESSION['user_id'])) {
    // Détruire la session
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    // Rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}
// Vérifier le type d'utilisateur pour restreindre l'accès à cette page
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    // Si l'utilisateur n'est pas admin, il sera rediriger vers la page login
    header("Location: login.php");
    exit();
}

// Vérification si un contact ID est passé via GET
if (isset($_GET['view_id']) && is_numeric($_GET['view_id'])) {
    $view_id = $_GET['view_id'];

    // Récupération des données du contact
    $view_query = $conn->prepare("SELECT * FROM contacts WHERE id = :id");
    $view_query->execute(['id' => $view_id]);
    $contact = $view_query->fetch(PDO::FETCH_ASSOC);

    if (!$contact) {
        // Contact introuvable
        $error_message = "Contact introuvable.";
    }
} else {
    // Rediriger si aucun ID n'est fourni
    header("Location: gestions_contacts.php?error=Aucun contact sélectionné.");
    exit;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger text-center">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php else: ?>
                <!-- Carte des détails -->
                <div class="card shadow-lg">
                    <div class="card-header bg-light text-white">
                        <h4 class="mb-0">Détails du Contact</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Nom :</strong> <?= htmlspecialchars($contact['nom']) ?></p>
                        <p><strong>Téléphone :</strong> <?= htmlspecialchars($contact['phone']) ?></p>
                        <p><strong>Sujet :</strong> <?= htmlspecialchars($contact['sujet']) ?></p>
                        <p><strong>Message :</strong></p>
                        <div class="p-3 bg-light border rounded">
                            <?= nl2br(htmlspecialchars($contact['message'])) ?>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <a href="gestions_contacts.php" class="btn btn-secondary">Retour à la gestion des contacts</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "footer.php"; ?>