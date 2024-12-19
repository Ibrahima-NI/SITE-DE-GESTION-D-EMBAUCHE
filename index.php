<?php
require_once "header.php";
require_once "config.php";
$user_role = "";
// Exemple de logique pour déterminer l'état de connexion
if (isset($_SESSION['user_id'])) {
  // L'utilisateur est connecté
  $user_role = $_SESSION['role']; // 'recruiter' ou 'candidate'
} else {
  // L'utilisateur n'est pas connecté
  $user_role = 'guest';
}
try {

  // Variables de pagination
  $jobsPerPage = 5; // Nombre de jobs par page
  $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $offset = ($currentPage - 1) * $jobsPerPage;

  // Récupérer les catégories pour la liste déroulante
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

  // Récupérer les jobs avec pagination et recherche
  $stmt = $conn->prepare("
      SELECT 
          jobs.id, 
          jobs.title, 
          jobs.salary, 
          jobs.created_at, 
          jobs.job_type, 
          jobs.description,
          categories.name AS category_name, 
          COUNT(applications.id) AS total_applications 
      FROM jobs
      LEFT JOIN categories ON jobs.category_id = categories.id
      LEFT JOIN applications ON jobs.id = applications.job_id
      WHERE 1=1 $searchQuery
      GROUP BY jobs.id
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
  <!-- <div class="bg-image-header">
    <section class="container py-lg-8 py-5 " data-cue="fadeIn">
      <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10 col-12" data-cues="zoomIn" data-group="page-title" data-delay="700">
          <div class="card text-center d-flex flex-column p-3">
            <div class="d-flex justify-content-center">
              <span class="bg-primary bg-opacity-10 text-primary border-primary border p-2 fs-6 rounded-pill lh-1 d-flex align-items-center">
                <span>Bienvenu à I&H.</span>
              </span>
            </div>
            <div class="d-flex flex-column gap-3 mx-lg-8">
              <h1 class="mb-0 display-5">La plateforme n°1 d'embauche à Djibouti.</h1>
              <p class="mb-0 lead">Découvrez les meilleures opportunités et construisez votre avenir dès maintenant.</p>
            </div>
            <div class="d-flex flex-row gap-2 justify-content-center my-3">
              <a href="https://bit.ly/block-theme" class="btn btn-warning" target="_blank">Trouver un job</a>
              <a href="#demo" class="btn btn-light">Publier un job</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  Pageheader end -->

  <!-- hero section -->
  <section class="py-5 position-relative z-1" data-cue="fadeIn">
    <div class="container pb-xl-8 mb-xl-8">
      <div class="row align-items-center gy-6">
        <div class="col-lg-5 col-xl-5" data-cue="zoomOut">
          <div class="d-flex flex-column gap-4">
            <div class="d-flex flex-row gap-2 align-items-center lh-1">

              <h1 class="fs-5 mb-0 bg-primary bg-opacity-10 text-primary border-primary border p-2 fs-5 rounded-pill lh-1 d-flex align-items-center">Bienvenu à I&H</h1>
            </div>
            <div>
              <h2 class="display-6 mb-3">La plateforme n°1 d'embauche à Djibouti.</h2>
              <p class="lead">Découvrez les meilleures opportunités et construisez votre avenir dès maintenant.</p>
              <ul class="list-unstyled d-flex flex-column gap-2">
                <li class="d-flex flex-row gap-2">
                  <span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle text-success" viewbox="0 0 16 16">
                      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"></path>
                      <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"></path>
                    </svg>
                  </span>
                  <span>Partager vos offres d'emplois</span>
                </li>
                <li class="d-flex flex-row gap-2">
                  <span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle text-success" viewbox="0 0 16 16">
                      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"></path>
                      <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"></path>
                    </svg>
                  </span>
                  <span>Trouver les meilleurs candidat</span>
                </li>
                <li class="d-flex flex-row gap-2">
                  <span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle text-success" viewbox="0 0 16 16">
                      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"></path>
                      <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"></path>
                    </svg>
                  </span>
                  <span>La garantie est au rendez-vous</span>
                </li>
              </ul>
            </div>

            <div class="d-flex flex-row gap-3 align-items-center">
              <?php if ($user_role === 'guest' || $user_role === 'recruiter'): ?>
                <a href="ajouter_jobs.php" class="btn btn-warning mb-3">
                  Partager une offre d'emploi
                </a>
              <?php endif; ?>

              <span>
                <!-- Bouton "Trouver un emploi" -->
                <?php if ($user_role === 'guest' || $user_role === 'candidate'): ?>
                  <a href="categories.php">
                    Trouver un emploi
                  </a>
                <?php endif; ?>
              </span>
            </div>
          </div>
        </div>
        <div class="offset-xl-1 col-xl-6 col-lg-6" data-cue="zoomIn">

          <img src="images/image.webp" alt="" class="rounded-3 img-fluid">



        </div>
      </div>
    </div>
  </section>
  <!-- hero section -->


  <div class="container">
    <div class="row my-5">
      <div class="col-md-8 col-12">
        <h3 class="text-center">Liste des dernieres offres disponibles</h3>
        <div class="mt-3">
          <!-- Formulaire de recherche -->
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
        </div>


        <div class="row">
          <!-- Liste des jobs -->
          <?php if (empty($jobs)): ?>
            <div class="alert alert-info my-2">Aucune offre d'emploi trouvée.</div>
          <?php else: ?>
            <?php foreach ($jobs as $job): ?>
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
            <?php endforeach ?>


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
          <?php endif ?>
        </div>
      </div>



      <div class="col-12 col-md-4">
        <div class="card mt-5 p-3">
          <div class="mt-5">

            <?php if (!empty($categories)): ?>
              <div class="list-group">
                <h5 class="mb-3 text-center">Liste des Catégories disponible</h5>
                <?php foreach ($categories as $category): ?>
                  <a href="categories.php?category_id=<?php echo $category['id']; ?>" class="list-group-item list-group-item-action">
                    <?php echo htmlspecialchars($category['name']); ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php else: ?>

              <div class="alert alert-danger">Aucune catégorie trouvée.</div>

            <?php endif; ?>



          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<!-- Section d'accueil avec l'image
  <img
    src="images/image.webp"
    alt="Réunion d'affaires"
    class="accueil-image" />

  <div class="container accueil">
    <div class="row">
      <div class="col-6 mx-auto">
        <div class="card rounded">
          <div class="card-body">
            <h2>BIENVENU SUR I&H</h2>
            <p>Explorez nos services et découvrez comment et nous pouvons vous accompagner vers succes.</p>

            <a href="connexion.html" class="btn btn-danger">POSTULER MAINTENANT</a>
          </div>
        </div>
      </div>

    </div>

  </div> -->

<!-- Copyright -->
<?php require_once "footer.php"; ?>