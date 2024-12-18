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



// Configuration pour la pagination
$limit = 10; // Nombre de contacts par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1; // Page courante
$offset = ($page - 1) * $limit; // Décalage pour la requête SQL

// Récupérer le nombre total de contacts
$total_contacts_query = $conn->query("SELECT COUNT(*) AS total FROM contacts");
$total_contacts = $total_contacts_query->fetch()['total'];
$total_pages = ceil($total_contacts / $limit);

// Récupérer les contacts pour la page courante
$query = $conn->prepare("SELECT * FROM contacts ORDER BY id DESC LIMIT :limit OFFSET :offset");
$query->bindValue(':limit', $limit, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$contacts = $query->fetchAll(PDO::FETCH_ASSOC);

// Suppression d'un contact
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = $conn->prepare("DELETE FROM contacts WHERE id = :id");
    $delete_query->execute(['id' => $delete_id]);
    header("Location: gestions_contacts.php?message=Contact supprimé avec succès");
    exit;
}



?>



<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Gestions des Contacts</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <div class="container mt-5">

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="table-reponsive">
            <!-- Tableau des contacts -->
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Sujet</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($contacts) > 0): ?>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td><?= htmlspecialchars($contact['id']) ?></td>
                                <td><?= htmlspecialchars($contact['nom']) ?></td>
                                <td><?= htmlspecialchars($contact['phone']) ?></td>
                                <td><?= htmlspecialchars($contact['sujet']) ?></td>
                                <td><?= htmlspecialchars(substr($contact['message'], 0, 50)) ?>...</td>
                                <td>
                                    <a href="voir_contact.php?view_id=<?= $contact['id'] ?>" class="btn btn-info btn-sm">Voir</a>
                                    <a href="gestions_contacts.php?delete_id=<?= $contact['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contact ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Aucun contact trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="gestions_contacts.php?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</main>

<?php require_once "footer.php"; ?>