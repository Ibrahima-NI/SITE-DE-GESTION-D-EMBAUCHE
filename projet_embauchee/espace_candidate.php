<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<?php
require_once "header.php";
require_once "config.php";

// Rediriger si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Restreindre l'accès aux candidats uniquement
if ($_SESSION['role'] !== 'candidate') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations du candidat
$query = "SELECT * FROM candidates WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$candidat = $stmt->fetch();

// Récupérer les statistiques
try {
    // Total des candidatures
    $stmtTotalApplications = $conn->prepare("SELECT COUNT(*) AS total_applications FROM applications WHERE candidate_id = :candidate_id");
    $stmtTotalApplications->execute(['candidate_id' => $user_id]);
    $totalApplications = $stmtTotalApplications->fetch(PDO::FETCH_ASSOC)['total_applications'];

    // Total des candidatures acceptées
    $stmtAcceptedApplications = $conn->prepare("SELECT COUNT(*) AS accepted_applications FROM applications WHERE candidate_id = :candidate_id AND status = 'accepter'");
    $stmtAcceptedApplications->execute(['candidate_id' => $user_id]);
    $acceptedApplications = $stmtAcceptedApplications->fetch(PDO::FETCH_ASSOC)['accepted_applications'];
} catch (PDOException $e) {
    die("Erreur lors de la récupération des statistiques : " . htmlspecialchars($e->getMessage()));
}

// Récupérer les candidatures
$candidaturesQuery = "
    SELECT 
        a.id AS application_id, 
        r.company_name AS company_name, 
        j.title AS job_title, 
        a.cv_file, 
        a.cover_letter_file, 
        a.status, 
        a.applied_at
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN recruiters r ON j.recruiter_id = r.id
    WHERE a.candidate_id = :candidate_id
    ORDER BY a.applied_at DESC
";
$stmt = $conn->prepare($candidaturesQuery);
$stmt->execute(['candidate_id' => $user_id]);
$candidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
    <?php if (!$candidat): ?>
        <div class="container">
            <div class="card p-3 my-5">
                <div class="alert alert-info">Veuillez compléter votre profil pour accéder à votre espace candidat.</div>
                <form action="espace_candidate.php" method="post">
                    <div class="mb-3">
                        <label for="skills" class="form-label">Compétences</label>
                        <input type="text" class="form-control" id="skills" name="skills" required>
                    </div>
                    <div class="mb-3">
                        <label for="experience" class="form-label">Expérience</label>
                        <input type="text" class="form-control" id="experience" name="experience" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center pt-lg-8">
            <h3>Bienvenue dans votre espace candidat</h3>
        </div>
        <div class="container">
            <div class="row">
                <!-- Profil -->
                <div class="col-lg-4">
                    <div class="card alert alert-danger">
                        <div class="card-header">
                            <h5 class="text-center">Votre profil</h5>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li><strong>Nom :</strong> <?= $_SESSION['nom'] ?></li>
                                <li><strong>Email :</strong> <?= $_SESSION['email'] ?></li>
                                <li><strong>Rôle :</strong> <?= $_SESSION['role'] ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="col-lg-8">
                    <div class="card bg-light mt-2 text-center">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-4 p-2">
                                <img src="images/programme.png" class="img-fluid" width="60px" alt="Candidatures">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <?= htmlspecialchars($totalApplications) ?> Candidatures envoyées
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light mt-2 text-center">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-4 p-2">
                                <img src="images/candidat.png" class="img-fluid" width="60px" alt="Acceptées">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <?= htmlspecialchars($acceptedApplications) ?> Candidatures acceptées
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des candidatures -->
                    <h2 class="mt-5">Mes Candidatures</h2>
                    <?php if (empty($candidatures)): ?>
                        <div class="alert alert-info">Vous n'avez pas encore postulé à un emploi.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table mt-4">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th>#</th>
                                        <th>Entreprise</th>
                                        <th>Offre</th>
                                        <th>CV</th>
                                        <th>Lettre</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    <?php foreach ($candidatures as $candidature): ?>
                                        <tr>
                                            <td><?= $candidature['application_id'] ?></td>
                                            <td><?= htmlspecialchars($candidature['company_name']) ?></td>
                                            <td><?= htmlspecialchars($candidature['job_title']) ?></td>
                                            <td><a href="<?= htmlspecialchars($candidature['cv_file']) ?>" target="_blank">Voir</a></td>
                                            <td><a href="<?= htmlspecialchars($candidature['cover_letter_file']) ?>" target="_blank">Voir</a></td>
                                            <td>
                                                <?php 
                                                $status = $candidature['status'];
                                                $badgeClass = $status === 'accepter' ? 'success' : ($status === 'refuser' ? 'danger' : 'info');
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($status)) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($candidature['applied_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once "footer.php"; ?>
