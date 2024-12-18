<?php
require_once "header.php";
require_once "config.php";

// Vérifier si l'utilisateur est déjà connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Vérifier le type d'utilisateur pour restreindre l'accès à cette page
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'recruiter') {
    // Si l'utilisateur n'est pas recruiter, il sera rediriger vers la page login
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Récupérer le nom et le type d'un job.

$requette = $conn->prepare("SELECT title, job_type FROM jobs Where id=:id");
$requette->execute([':id' => $_GET['job_id']]);
$jobb = $requette->fetch(PDO::FETCH_ASSOC);




// Vérifiez que l'ID du job est passé dans l'URL
if (!isset($_GET['job_id'])) {
    echo "Aucun job sélectionné.";
    exit();
}
// Vérifiez que l'ID du job est passé dans l'URL
if (!isset($_GET['job_id'])) {
    echo "Aucun job sélectionné.";
    exit();
}

$job_id = $_GET['job_id'];

try {

    // Récupérer les candidatures pour ce job avec les informations des utilisateurs et des candidats
    $stmt = $conn->prepare("
        SELECT applications.id AS application_id, users.name AS candidate_name,applications.cv_file as cv_file ,applications.cover_letter_file as letter_file, candidates.phone, candidates.skills, candidates.experience, applications.status
        FROM applications
        LEFT JOIN users ON applications.candidate_id = users.id
        LEFT JOIN candidates ON users.id = candidates.user_id
        WHERE applications.job_id = :job_id
    ");
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();

    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mettre à jour le statut de la candidature
    if (isset($_POST['application_id']) && isset($_POST['status'])) {
        $application_id = $_POST['application_id'];
        $status = $_POST['status'];

        try {
            $updateStmt = $conn->prepare("UPDATE applications SET status = :status WHERE id = :application_id");
            $updateStmt->bindParam(':status', $status);
            $updateStmt->bindParam(':application_id', $application_id);
            $updateStmt->execute();
            $success = "<div class='alert alert-success'>Le statut a été mis à jour. <a href=\"gestion_candidature_details.php?job_id=$job_id\"> Voir le candidature.</a></div>";
        } catch (PDOException $e) {
            $error = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
        }
    }
}




?>




<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h2>Liste des candidatures pour le job n° : <?= $_GET['job_id'] ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->

    <div class="container mt-3">
        <h4>Titre du job : <span class="text-primary"><?= $jobb['title'] ?></span></h4>
        <h4>Type du job : <span class="text-danger"><?= $jobb['job_type'] ?></span></h4>

        <?php if ($error): ?>
            <?php echo $error; ?>
        <?php elseif ($success): ?>
            <?php echo $success; ?>
        <?php endif; ?>
        <table class="table table-bordered table-info mt-4">
            <thead class="text-center">
                <tr>
                    <th><strong>Candidat</strong></th>
                    <th><strong>Téléphone du candidat</strong></th>
                    <th><strong>CV</strong></th>
                    <th><strong>Lettre de motivation</strong></th>
                    <th><strong>Statut</strong></th>
                    <th><strong>Actions</strong></th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php foreach ($applications as $application): ?>
                    <tr>
                        <td><?= htmlspecialchars($application['candidate_name']); ?></td>
                        <td><?= htmlspecialchars($application['phone']); ?></td>
                        <td><a href="<?= htmlspecialchars($application['cv_file']); ?>" target="_blank">Ouvrir le CV</a></td>
                        <td><a href="<?= htmlspecialchars($application['letter_file']); ?>" target="_blank">Voir le lettre</a></td>
                        <td>
                            <form method="POST">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option class="btn btn-light" value="en cours" <?= $application['status'] == 'en cours' ? 'selected' : ''; ?>>En cours</option>
                                    <option class="btn btn-light" value="accepter" <?= $application['status'] == 'accepter' ? 'selected' : ''; ?>>Accepter</option>
                                    <option class="btn btn-light" value="refuser" <?= $application['status'] == 'refuser' ? 'selected' : ''; ?>>Refuser</option>
                                </select>
                                <input type="hidden" name="application_id" value="<?= $application['application_id']; ?>">
                            </form>
                        </td>
                        <td>
                            <!-- Formulaire pour supprimer une candidature -->
                            <form method="post" action="" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette candidature ?');">
                                <input type="hidden" name="candidature_id" value="">
                                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<?php require_once "footer.php"; ?>