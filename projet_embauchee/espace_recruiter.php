<?php
require_once "header.php";
require_once "config.php";
$success = "";
$error = "";
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit(); // Arrêter l'exécution du script
}

// Vérifier le type d'utilisateur pour restreindre l'accès à cette page
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'recruiter') {
    // Si l'utilisateur n'est pas recruiter, il sera rediriger vers la page login
    header("Location: index.php");
    exit();
}


// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Vérifier si l'utilisateur existe dans la table `recruiter`
$query = "SELECT * FROM recruiters WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$recruiter = $stmt->fetch();




//Enregistrement du recruiter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $company_name = $_POST['company_name'];
    $job_title = $_POST['job_title'];
    $website = $_POST['website'] ?? null;
    $phone = $_POST['phone'];

    if (empty($company_name) || empty($job_title) || empty($phone)) {
        $error = "Il y'a des champs vides";
    }
    // Insérer les informations dans la table `recruiter`
    $query = "INSERT INTO recruiters (user_id, company_name, job_title, website, phone) VALUES (:user_id, :company_name, :job_title, :website, :phone)";
    $stmt = $conn->prepare($query);

    $stmt->execute([
        'user_id' => $user_id,
        'company_name' => $company_name,
        'job_title' => $job_title,
        'website' => $website,
        'phone' => $phone
    ]);
    // Rediriger vers l'espace recruteur
    header("Location: espace_recruiter.php?message=profil_complet");
    exit();
    $success = 'Bravo ! Vous êtes maintenant Recruiter.';
}





$message = "";

// Suppression d'un job
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $query = "DELETE FROM jobs WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $delete_id]);

        $message = '<div class="alert alert-success">Le job a été supprimé avec succès.</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la suppression : ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}



// Supposons que l'utilisateur connecté est identifié par son ID stocké dans une session
// Récupérer l'ID de l'utilisateur connecté
//$user_id = $_SESSION['user_id'];


// Requête pour récupérer l'ID du recruteur depuis la table recruiters
$stmt = $conn->prepare("SELECT id FROM recruiters WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$recruiter = $stmt->fetch(PDO::FETCH_ASSOC);

if ($recruiter) {
    // Si le recruteur existe, récupérez son ID
    $recruiter_id = $recruiter['id'];







    // Variables pour la recherche et la pagination
    $search = $_GET['search'] ?? '';
    $items_per_page = 5;
    $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    try {
        // Récupérer le nombre total de jobs pour la pagination
        $count_stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM jobs 
        WHERE recruiter_id = :recruiter_id AND title LIKE :search
    ");
        $count_stmt->execute([
            ':recruiter_id' => $recruiter_id,
            ':search' => "%$search%",
        ]);
        $total_jobs = $count_stmt->fetchColumn();

        // Calcul du nombre total de pages
        $total_pages = ceil($total_jobs / $items_per_page);

        // Préparer la requête pour les jobs avec la pagination et la recherche
        $stmt = $conn->prepare("
        SELECT 
            jobs.id, 
            jobs.title, 
            jobs.created_at, 
            jobs.salary, 
            jobs.job_type, 
            COUNT(applications.id) AS total_applications 
        FROM jobs
        LEFT JOIN applications ON jobs.id = applications.job_id
        WHERE jobs.recruiter_id = :recruiter_id AND jobs.title LIKE :search
        GROUP BY jobs.id
        ORDER BY jobs.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
        $stmt->bindValue(':recruiter_id', $recruiter_id, PDO::PARAM_INT);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des jobs : " . htmlspecialchars($e->getMessage()));
    }




    try {
        // Compter le nombre de jobs publiés par le recruteur
        $stmtJobs = $conn->prepare("
        SELECT COUNT(*) AS total_jobs 
        FROM jobs 
        WHERE recruiter_id = :recruiter_id
    ");
        $stmtJobs->bindParam(':recruiter_id', $recruiter_id, PDO::PARAM_INT);
        $stmtJobs->execute();
        $totalJobs = $stmtJobs->fetch(PDO::FETCH_ASSOC)['total_jobs'];

        // Compter le nombre total de candidatures pour les jobs publiés par le recruteur
        $stmtApplications = $conn->prepare("
        SELECT COUNT(applications.id) AS total_applications
        FROM applications
        INNER JOIN jobs ON applications.job_id = jobs.id
        WHERE jobs.recruiter_id = :recruiter_id
    ");
        $stmtApplications->bindParam(':recruiter_id', $recruiter_id, PDO::PARAM_INT);
        $stmtApplications->execute();
        $totalApplications = $stmtApplications->fetch(PDO::FETCH_ASSOC)['total_applications'];
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des données : " . htmlspecialchars($e->getMessage()));
    }
}

?>


<main>




    <?php if (!$recruiter): ?>
        <div class="container">

            <div class="card p-3 mb-5">
                <div class="alert alert-info" role="alert">
                    Veuillez compléter votre profil pour accéder à votre espace recruteur.
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form action="espace_recruiter.php" method="post">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Nom de l'entreprise</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="job_title" class="form-label">Titre du poste</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" required>
                    </div>
                    <div class="mb-3">
                        <label for="website" class="form-label">Site web de l'entreprise (optionnel)</label>
                        <input type="url" class="form-control" id="website" name="website">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Numéro de téléphone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- Espace de travail recruteur -->


        <div class="container">

            <div class="text-center pt-lg-8">
                <h3 class="text-center">Bienvenue dans votre espace recruteur</h3>
            </div>


            <div class="row my-5">

                <div class="col-12 col-md-4 text-align-center">
                    <a href="ajouter_jobs.php" class="btn btn-primary my-3">Ajouter un jobs</a>
                    <a class="my-2 btn btn-success" href="liste_candidatures_recruter.php">Gestions des candidatures</a>
                    <div class="card bg-light mt-2 text-center">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-4 p-2">
                                <img src="images/programme.png" class="img-fluid" width="60px" alt="...">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <?= htmlspecialchars($totalJobs) ?> Jobs Publié
                                    </h6>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-light mt-2 text-center">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-4 p-2">
                                <img src="images/candidat.png" class="img-fluid" width="60px" alt="...">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <?= htmlspecialchars($totalApplications) ?> Candidature reçue
                                    </h6>

                                </div>
                            </div>
                        </div>
                    </div>





                </div>
                <div class="col-12 col-md-8">



                    <h6 class="text-center my-5">Liste des jobs publiés</h6>

                    <div class="mt-3">
                        <form class="row g-2 d-flex mx-lg-7" action="" method="get">
                            <div class="col-md-9 col-12">
                                <input type="text" class="form-control border border-dark" name="search" placeholder="Rechercher un titre de poste" value="<?= htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3 col-12">
                                <div class="d-grid">
                                    <button class="btn btn-dark" type="submit" name="rechercher">Rechercher</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table my-5">
                            <thead class="bg-info text-dark rounded">
                                <tr class="text-center">
                                    <th>Titre du poste</th>
                                    <th>Date de publication</th>
                                    <th>Nombre de candidature</th>
                                    <th colspan="2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($jobs)): ?>
                                    <?php foreach ($jobs as $job): ?>
                                        <tr class="text-center">
                                            <td><?= htmlspecialchars($job['title']); ?></td>
                                            <td><?= htmlspecialchars($job['created_at']); ?></td>
                                            <td><?= htmlspecialchars($job['total_applications']); ?> candidatures</td>
                                            <td>
                                                <!-- Bouton de suppression -->
                                                <a href="espace_recruiter.php?delete_id=<?= htmlspecialchars($job['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce job ?');">Supprimer</a>
                                                <a href="ajouter_jobs.php?edit_id=<?= $job['id'] ?>" class="btn btn-warning btn-sm">Modifier</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Aucun job trouvé.</td>
                                    </tr>
                                <?php endif; ?>

                            </tbody>
                        </table>
                    </div>




                </div>
            </div>
        </div>



    <?php endif ?>


</main>

<?php require_once "footer.php"; ?>