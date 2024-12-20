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
$user_id = $_SESSION['user_id'];

try {
    // Requête pour récupérer l'ID du recruteur depuis la table recruiters
    $stmt = $conn->prepare("SELECT id FROM recruiters WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $recruiter = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($recruiter) {
        // Si le recruteur existe, récupérez son ID
        $recruiter_id = $recruiter['id'];
    } else {
        // Si l'utilisateur n'est pas dans la table recruiters
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération de l'ID recruteur : " . htmlspecialchars($e->getMessage()));
}




try {
    // Récupérer les jobs avec des candidatures
    $stmt = $conn->prepare("
        SELECT jobs.id AS job_id, jobs.title AS job_title, COUNT(applications.id) AS total_applications
        FROM jobs
        LEFT JOIN applications ON jobs.id = applications.job_id
        WHERE jobs.recruiter_id = :recruiter_id
        GROUP BY jobs.id
    ");
    $stmt->bindParam(':recruiter_id', $recruiter_id);
    $stmt->execute();

    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

?>

<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Gestion des candidatures réçus</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->


    <div class="container mt-5 text-center">

        <!-- Liste des jobs avec des candidatures -->
        <table class="table table-bordered  mt-4">
            <thead class="table-info">
                <tr>
                    <th>Titre du job</th>
                    <th>Candidatures</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td><?= htmlspecialchars($job['job_title']); ?></td>
                        <td><?= $job['total_applications']; ?> candidatures</td>
                        <td>
                            <a href="gestion_candidature_details.php?job_id=<?= $job['job_id']; ?>" class="btn btn-primary">Voir candidatures</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>



</main>

<?php require_once "footer.php"; ?>