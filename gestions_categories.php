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
// Variables pour messages de succès ou d'erreur
$successMessage = '';
$errorMessage = '';

// Traitement du formulaire d'enregistrement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $slug = preg_replace('/[^a-z0-9]+/', '-', trim(strtolower(htmlspecialchars(trim($name)))));

    if (!empty($name)) {
        try {
            // Vérification si le slug existe déjà
            $checkQuery = "SELECT COUNT(*) FROM categories WHERE slug = :slug";
            $stmtCheck = $conn->prepare($checkQuery);
            $stmtCheck->execute(['slug' => $slug]);
            $exists = $stmtCheck->fetchColumn();

            if ($exists) {
                $errorMessage = "Le slug existe déjà. Veuillez en choisir un autre.";
            } else {
                // Insertion de la catégorie

                $query = "INSERT INTO categories (name, slug) VALUES (:name, :slug)";
                $stmt = $conn->prepare($query);
                $stmt->execute(['name' => $name, 'slug' => $slug]);
                $successMessage = "Catégorie enregistrée avec succès !";
            }
        } catch (PDOException $e) {
            $errorMessage = "Erreur : " . htmlspecialchars($e->getMessage());
        }
    } else {
        $errorMessage = "Veuillez remplir tous les champs.";
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


// Suppression d'un categorie
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $delete_id]);

        $message = '<div class="alert alert-success">Le categorie a été supprimé avec succès.</div>';
        header('Location:gestions_categories.php');
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
                        <h1>Gestions des categories</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>




    <div class="container">
        <div class="row justify-content-center mb-6">
            <div class="col-xl-5 col-lg-6 col-md-8 col-12">
                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= $successMessage; ?></div>
                <?php elseif ($errorMessage): ?>
                    <div class="alert alert-danger"><?= $errorMessage; ?></div>
                <?php endif; ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <form method="POST" action="gestions_categories.php">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom de la catégorie</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Entrez le nom de la catégorie" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </form>

                    </div>
                </div>


            </div>
        </div>


        <!-- Liste des catégories -->
        <div class="card mt-4">
            <div class="card-header">
                Liste des catégories
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <div class="alert alert-info">Aucune catégorie trouvée.</div>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead class="text-center">
                            <tr>
                                <th>Nom</th>
                                <th>Slug</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= htmlspecialchars($category['name']); ?></td>
                                    <td><?= htmlspecialchars($category['slug']); ?></td>
                                    <td>
                                        <!-- Bouton de suppression -->
                                        <a href="gestions_categories.php?delete_id=<?= $category['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce categorie ?');">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>


</main>

<?php require_once "footer.php"; ?>