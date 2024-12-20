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
// Récupérer l'ID de l'utilisateur connecté
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
        header("Location: espace_recruiter.php");
        exit();
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération de l'ID recruteur : " . htmlspecialchars($e->getMessage()));
}



$message = "";
$edit_mode = false; // Variable pour savoir si on est en mode modification
$title = $description = $location = $salary = "";

// Vérification si on est en mode modification
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_mode = true;

    // Récupération des données du job à modifier
    $query = "SELECT * FROM jobs WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $edit_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($job) {
        $title = $job['title'];
        $description = $job['description'];
        $location = $job['location'];
        $salary = $job['salary'];
    } else {
        $message = '<div class="alert alert-danger">Aucun job trouvé avec cet ID.</div>';
        $edit_mode = false;
    }
}

// Traitement du formulaire pour ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $jobs_type = trim($_POST['jobs_type']);
    $categorie_id = trim($_POST['categorie']);
    $salary = trim($_POST['salary']);

    if (empty($title) || empty($description) || empty($location) || empty($salary)) {
        $message = '<div class="alert alert-danger">Tous les champs sont requis.</div>';
    } else {
        try {
            if ($edit_mode) {
                // Mise à jour d'un job existant
                $query = "UPDATE jobs SET title = :title, description = :description, location = :location, salary = :salary, job_type =:jobs_type, category_id =:categorie WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':location' => $location,
                    ':salary' => $salary,
                    ':id' => $edit_id,
                    ':categorie' => $categorie_id,
                    ':jobs_type' => $jobs_type
                ]);
                $message = '<div class="alert alert-success">Le job a été mis à jour avec succès.</div>';
            } else {
                // Ajout d'un nouveau job
                $query = "INSERT INTO jobs (title, description, location, salary,job_type,recruiter_id,created_at,category_id) VALUES (:title, :description, :location, :salary,:job_type,:recruiter_id, NOW(),:categorie)";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':location' => $location,
                    ':salary' => $salary,
                    ':job_type' => $jobs_type,
                    ':categorie' => $categorie_id,
                    ':recruiter_id' => $recruiter_id
                ]);
                $message = '<div class="alert alert-success">Le job a été ajouté avec succès.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}


// Récupération des catégories existantes
try {
    $categoriesQuery = "SELECT * FROM categories";
    $stmtCategories = $conn->query($categoriesQuery);
    $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur : ' . htmlspecialchars($e->getMessage()));
}

?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <?php if ($edit_mode): ?>
                            <h1>Modifier un job</h1>

                        <?php else : ?>
                            <h1>Ajouter un job</h1>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->



    <div class="container">
        <div class="row justify-content-center mb-6">
            <div class="col-xl-5 col-lg-6 col-md-8 col-12">
                <?= $message; ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <form class="needs-validation mb-6" method="post">
                            <div class="mb-3">
                                <label for="title" class="form-label">Titre du Job</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($title); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Choisisez une categorie</label>
                                        <select class="form-select" name="categorie">
                                            <?php foreach ($categories as $categories): ?>
                                                <option value="<?= $categories['id'] ?>"><?= $categories['name'] ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>

                                </div>

                                <div class="col-12 col-sm-6">
                                    <div class="mb-3">
                                        <label for="location" class="form-label">Localisation</label>
                                        <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($location); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Type des jobs</label>
                                        <select class="form-select" name="jobs_type">

                                            <option value="CDI">CDI</option>
                                            <option value="CDD">CDD</option>
                                            <option value="consultant">Consultant</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6">

                                    <div class="mb-3">
                                        <label for="salary" class="form-label">Salaire</label>
                                        <input type="number" class="form-control" id="salary" name="salary" value="<?= htmlspecialchars($salary); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($description); ?></textarea>
                            </div>





                            <div class="mb-3">
                                <input type="hidden" class="form-control" id="recruiter_id" name="recruiter_id" value="<?= $_SESSION['user_id'] ?>" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Enregistrer le Job</button>
                        </form>


                    </div>
                </div>


            </div>
        </div>

    </div>


</main>

<?php require_once "footer.php"; ?>