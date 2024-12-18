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


// Suppression de la candidature si demandée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_application_id'])) {
    $applicationId = intval($_POST['delete_application_id']);
    $stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->execute([$applicationId]);
    header("Location: gestions_candidatures.php?message=deleted");
    exit;
}

// Récupération des candidatures
$stmt = $conn->prepare("
    SELECT 
        applications.id, 
        applications.status, 
        applications.statu, 
        applications.cv_file, 
        applications.cover_letter_file, 
        applications.years_experience, 
        jobs.title AS job_title, 
        candidates.user_id AS candidate_user_id
    FROM applications
    LEFT JOIN jobs ON applications.job_id = jobs.id
    LEFT JOIN candidates ON applications.candidate_id = candidates.id
");
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Gestions des Candidatures</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <div class="container mt-5">

        <!-- Message de confirmation -->
        <?php if (isset($_GET['message']) && $_GET['message'] == 'deleted'): ?>
            <div class="alert alert-success">Candidature supprimée avec succès.</div>
        <?php endif; ?>

        <table class="table table-striped table-bordered mt-4">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Années d'expérience</th>
                    <th>CV</th>
                    <th>Lettre de Motivation</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                    <tr>
                        <td><?= htmlspecialchars($application['job_title']); ?></td>
                        <td><?= $application['years_experience']; ?> ans</td>
                        <td><a href="<?= htmlspecialchars($application['cv_file']); ?>" target="_blank">Voir CV</a></td>
                        <td><a href="<?= htmlspecialchars($application['cover_letter_file']); ?>" target="_blank">Voir Lettre</a></td>
                        <td>
                            <?php if ($application['status'] == "refuser") : ?>
                                <span class="badge bg-danger"><?= ucfirst(htmlspecialchars($application['status'])); ?></span>
                            <?php elseif ($application['status'] == "accepter") : ?>
                                <span class="badge bg-success"><?= ucfirst(htmlspecialchars($application['status'])); ?></span>
                            <?php elseif ($application['status'] == "en cours") : ?>
                                <span class="badge bg-info"><?= ucfirst(htmlspecialchars($application['status'])); ?></span>
                            <?php endif ?>
                        </td>
                        <td>
                            <!-- Supprimer candidature -->
                            <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette candidature ?');" style="display:inline;">
                                <input type="hidden" name="delete_application_id" value="<?= $application['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript pour activer/désactiver une candidature -->
    <script>
        $(document).ready(function() {
            $('.status-checkbox').on('change', function() {
                const applicationId = $(this).data('application-id');
                const newStatus = $(this).is(':checked') ? true : false;

                $.ajax({
                    url: 'update_application_status.php', // Script pour mettre à jour le statut
                    method: 'POST',
                    data: {
                        application_id: applicationId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Statut mis à jour avec succès.');
                        } else {
                            alert('Erreur : ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Erreur lors de la mise à jour du statut.');
                    }
                });
            });
        });
    </script>

</main>

<?php require_once "footer.php"; ?>