<?php
require_once "header.php";
require_once "config.php"; // Connexion à la base de données



try {

    // Variables de pagination
    $jobsPerPage = 6; // Nombre de jobs par page
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $jobsPerPage;

    // Récupérer les catégories pour la liste latérale
    $categoryStmt = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

    // Construire la requête SQL pour les jobs
    $searchQuery = "";
    $queryParams = [];
    if (!empty($_GET['title'])) {
        $searchQuery .= " AND jobs.title LIKE :title ";
        $queryParams[':title'] = "%" . $_GET['title'] . "%";
    }
    if (!empty($_GET['category_id'])) {
        $searchQuery .= " AND jobs.category_id = :category_id ";
        $queryParams[':category_id'] = $_GET['category_id'];
    }

    // Récupérer les jobs de la catégorie sélectionnée
    $stmt = $conn->prepare("
        SELECT 
            jobs.id, 
            jobs.title, 
            jobs.description, 
            jobs.created_at,
            jobs.job_type,
            categories.name AS category_name
        FROM jobs
        LEFT JOIN categories ON jobs.category_id = categories.id
        WHERE 1=1 $searchQuery
        ORDER BY jobs.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $jobsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($queryParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compter le nombre total de jobs pour la pagination
    $countStmt = $conn->prepare("
        SELECT COUNT(*) AS total 
        FROM jobs
        WHERE 1=1 $searchQuery
    ");
    foreach ($queryParams as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalJobs = $countStmt->fetchColumn();
    $totalPages = ceil($totalJobs / $jobsPerPage);
} catch (PDOException $e) {
    die("Erreur de connexion : " . htmlspecialchars($e->getMessage()));
}
?>

<main>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Liste des Jobs par Catégorie</h1>
        <div class="row">
            <!-- Section principale (col-md-8) -->
            <div class="col-md-8">
                <!-- Barre de recherche -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <input type="text" name="title" class="form-control" placeholder="Rechercher par titre..."
                            value="<?php echo htmlspecialchars($_GET['title'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="category_id" class="form-select">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                    <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </div>
                </form>

                <!-- Liste des jobs sous forme de cartes -->
                <div class="row">
                    <?php if (!empty($jobs)): ?>
                        <?php foreach ($jobs as $job): ?>
                            <div class="col-md-12 mb-4">
                                <div class="card my-3 mx-2 bg-light border border-dark">
                                    <div class="card-body ">
                                        <h5 class="card-title"><?= htmlspecialchars($job['title']); ?></h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-info mb-2"><?= htmlspecialchars($job['category_name']); ?></span>
                                            <span class="badge bg-primary mb-2 fs-7">Type de job : <?= htmlspecialchars($job['job_type']); ?></span>
                                        </div>
                                        <h6 class="card-subtitle mb-2 text-muted">Publiée le <?= htmlspecialchars(date("d/m/Y", strtotime($job['created_at']))); ?></h6>

                                        <p class="card-text"><?= htmlspecialchars(substr($job['description'], 0, 100)) . '...'; ?></p>
                                        <a href="candidature.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-warning text-dark">Postuler</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-center">Aucun job trouvé.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&title=<?php echo urlencode($_GET['title'] ?? ''); ?>&category_id=<?php echo urlencode($_GET['category_id'] ?? ''); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>

            <!-- Liste des catégories (col-md-4) -->
            <div class="col-md-4">
                <div class="list-group">
                    <h5 class="mb-3 text-center">Liste des Catégories disponible</h5>
                    <?php foreach ($categories as $category): ?>
                        <a href="categories.php?category_id=<?php echo $category['id']; ?>" class="list-group-item list-group-item-action">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>


<?php require_once "footer.php"; ?>