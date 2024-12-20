<?php
require_once "header.php";
require_once "config.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: connexion.php");
    exit(); // Arrêter l'exécution du script
}

// Vérifier le type d'utilisateur pour restreindre l'accès à cette page
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    // Si l'utilisateur n'est pas admin, il sera rediriger vers la page login
    header("Location: login.php");
    exit();
}

$message = "";

// Variables pour stocker les statistiques
$totalJobs = 0;
$totalApplications = 0;
$jobs = [];


// Récupérer le nombre total de jobs publiés
try {
    $stmt = $conn->query("SELECT COUNT(*) AS total_jobs FROM jobs");
    $totalJobs = $stmt->fetch(PDO::FETCH_ASSOC)['total_jobs'];
} catch (PDOException $e) {
    die("Erreur lors de la récupération du nombre de jobs : " . htmlspecialchars($e->getMessage()));
}

// Récupérer le nombre total de candidatures passées
try {
    $stmt = $conn->query("SELECT COUNT(*) AS total_applications FROM applications ");
    $totalApplications = $stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];
} catch (PDOException $e) {
    die("Erreur lors de la récupération du nombre de candidatures : " . htmlspecialchars($e->getMessage()));
}

// Récupérer  le nombre total de contacts
$query_count_contacts = $conn->prepare("SELECT COUNT(*) AS total_contacts FROM contacts");
$query_count_contacts->execute();
$result_contacts = $query_count_contacts->fetch(PDO::FETCH_ASSOC);

// Nombre total de contacts
$total_contacts = $result_contacts['total_contacts'];



// Récupérer la liste des jobs publiés avec le nombre de candidatures associées
$searchTitle = isset($_GET['title']) ? htmlspecialchars(trim($_GET['title'])) : '';
$searchCategory = isset($_GET['category_id']) ? (int)$_GET['category_id'] : '';
$limit = 5; // Nombre de jobs par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Préparer les conditions pour le filtrage
$conditions = [];
$params = [];

if ($searchTitle) {
    $conditions[] = "jobs.title LIKE :title";
    $params[':title'] = '%' . $searchTitle . '%';
}

if ($searchCategory) {
    $conditions[] = "jobs.category_id = :category_id";
    $params[':category_id'] = $searchCategory;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Récupérer la liste des jobs avec pagination
try {
    $stmt = $conn->prepare("
        SELECT 
            jobs.id, 
            jobs.title, 
            jobs.salary, 
            jobs.created_at, 
            jobs.job_type, 
            recruiters.company_name as recruiter, 
            categories.name AS category_name, 
            COUNT(applications.id) AS total_applications 
        FROM jobs
        LEFT JOIN categories ON jobs.category_id = categories.id
        LEFT JOIN applications ON jobs.id = applications.job_id
        LEFT JOIN recruiters ON recruiters.id = jobs.recruiter_id
        $where
        GROUP BY jobs.id
        ORDER BY jobs.created_at DESC
        LIMIT :limit OFFSET :offset
    ");

    // Ajouter les paramètres pour la requête
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer le total pour la pagination
    $stmtTotal = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM jobs
        LEFT JOIN categories ON jobs.category_id = categories.id
        $where
    ");
    foreach ($params as $key => $value) {
        $stmtTotal->bindValue($key, $value);
    }
    $stmtTotal->execute();
    $totalJobs = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalJobs / $limit);

    // Récupérer la liste des catégories pour la liste déroulante
    $stmtCategories = $conn->query("SELECT id, name FROM categories");
    $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des jobs : " . htmlspecialchars($e->getMessage()));
}



// Suppression d'un job
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $query = "DELETE FROM jobs WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $delete_id]);

        $message = '<div class="alert alert-success">Le job a été supprimé avec succès.</div>';
        header('location:espace_admin.php');
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la suppression : ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Administration</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->



    <div class="container">

        <div class="text-center">
            <a href="gestions_categories.php" class="btn btn-danger my-2">Gestions des categories</a>
            <a href="gestions_users.php" class="btn btn-warning my-2">Gestions des utilisateurs</a>
            <a href="gestions_candidatures.php" class="btn btn-info my-2">Gestions des candidatures</a>
            <a href="gestions_contacts.php" class="btn btn-secondary my-2">Gestions des contacts</a>
        </div>
        <div class="row my-5">
            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                <div class="card alert alert-danger text-center">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-4">
                            <img src="images/jobs.png" class="img-fluid" width="60px" alt="...">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <?= $totalJobs; ?> jobs publiés au total
                                </h6>

                            </div>
                        </div>
                    </div>

                </div>

            </div>


            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                <div class="card alert alert-info text-center">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-4">
                            <img src="images/candidat.png" class="img-fluid" width="60px" alt="...">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <?= $totalApplications; ?> Total de candidature
                                </h6>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                <div class="card alert alert-primary text-center">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-4">
                            <img src="images/contact.png" class="img-fluid" width="60px" alt="...">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <?= $total_contacts ?> Contacts
                                </h6>
                            </div>
                        </div>
                    </div>


                </div>
            </div>



        </div>


        <div class="my-3 text-center">
            <h2>Liste des jobs publiés</h2>
        </div>

        <!-- Formulaire de recherche -->
        <form method="GET" class="row mb-4">
            <div class="col-md-6 mt-1">
                <input type="text" name="title" value="<?= htmlspecialchars($searchTitle) ?>" class="form-control" placeholder="Rechercher par titre">
            </div>
            <div class="col-md-4 mt-1">
                <select name="category_id" class="form-select">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $searchCategory == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mt-1">
                <button type="submit" class="btn btn-primary w-100">Rechercher</button>
            </div>
        </form>
        <?php if ($message): ?>
            <?= $message; ?>
        <?php endif; ?>



        <?php if (empty($jobs)): ?>
            <div class="alert alert-info">Aucun job publié pour le moment.</div>
        <?php else: ?>
            <div class="table-responsive my-3">
                <table class="table  table-hover">
                    <thead class="text-center bg-info">
                        <tr>
                            <th>Titre du jobs</th>
                            <th>Entreprise Publié</th>
                            <th>Categorie</th>
                            <th>Date</th>
                            <th>Salaire</th>
                            <th>Type</th>
                            <th>Nombre de candidature reçus</th>
                            <th colspan="3">Action</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?= $job['title'] ?></td>
                                <td><?= $job['recruiter'] ?></td>
                                <td><?= $job['category_name'] ?></td>
                                <td><?= $job['created_at'] ?></td>
                                <td><?= $job['salary'] ?></td>
                                <td><?= $job['job_type'] ?></td>
                                <td><?= $job['total_applications']; ?></td>
                                <td>
                                <td>
                                    <!-- Bouton de suppression -->
                                    <a href="espace_admin.php?delete_id=<?= htmlspecialchars($job['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce job ?');">Supprimer</a>
                                </td>
                                </td>
                            </tr>
                        <?php endforeach; ?>


                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?title=<?= urlencode($searchTitle) ?>&category_id=<?= $searchCategory ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif ?>
    </div>

</main>

<?php require_once "footer.php"; ?>