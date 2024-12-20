<?php
require_once "header.php";
require_once "config.php";

// Vérifier si l'utilisateur est déjà connecté
if (!isset($_SESSION['user_id'])) {
    // Détruire la session
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session
    // Rediriger vers la page de connexion
    header("Location: login.php?message=deconnected");
    exit();
}

// Vérifier le type d'utilisateur pour restreindre l'accès à cette page
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    // Si l'utilisateur n'est pas admin, il sera rediriger vers la page login
    header("Location: login.php");
    exit();
}


// Variables de messages
$successMessage = '';
$errorMessage = '';

// Ajouter un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = htmlspecialchars($_POST['role']);

    try {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        $successMessage = "Utilisateur ajouté avec succès.";
    } catch (PDOException $e) {
        $errorMessage = "Erreur lors de l'ajout de l'utilisateur : " . htmlspecialchars($e->getMessage());
    }
}

// Supprimer un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $user_id = intval($_GET['delete_id']); // ID de l'utilisateur à supprimer

    try {

        // Vérifier si l'utilisateur est un `candidate`
        $stmtCandidate = $conn->prepare("SELECT id FROM candidates WHERE user_id = :user_id");
        $stmtCandidate->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmtCandidate->execute();
        $candidate = $stmtCandidate->fetch(PDO::FETCH_ASSOC);

        if ($candidate) {
            $candidate_id = $candidate['id'];

            // Supprimer toutes les candidatures liées à ce candidate
            $stmtDeleteApplications = $conn->prepare("DELETE FROM applications WHERE candidate_id = :candidate_id");
            $stmtDeleteApplications->bindParam(':candidate_id', $candidate_id, PDO::PARAM_INT);
            $stmtDeleteApplications->execute();

            // Supprimer le candidate
            $stmtDeleteCandidate = $conn->prepare("DELETE FROM candidates WHERE id = :candidate_id");
            $stmtDeleteCandidate->bindParam(':candidate_id', $candidate_id, PDO::PARAM_INT);
            $stmtDeleteCandidate->execute();
        }

        // Vérifier si l'utilisateur est un `recruiter`
        $stmtRecruiter = $conn->prepare("SELECT id FROM recruiters WHERE user_id = :user_id");
        $stmtRecruiter->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmtRecruiter->execute();
        $recruiter = $stmtRecruiter->fetch(PDO::FETCH_ASSOC);

        if ($recruiter) {
            $recruiter_id = $recruiter['id'];

            // Supprimer toutes les candidatures liées aux jobs de ce recruiter
            $stmtDeleteJobApplications = $conn->prepare("
                DELETE applications FROM applications
                INNER JOIN jobs ON applications.job_id = jobs.id
                WHERE jobs.recruiter_id = :recruiter_id
            ");
            $stmtDeleteJobApplications->bindParam(':recruiter_id', $recruiter_id, PDO::PARAM_INT);
            $stmtDeleteJobApplications->execute();

            // Supprimer tous les jobs de ce recruiter
            $stmtDeleteJobs = $conn->prepare("DELETE FROM jobs WHERE recruiter_id = :recruiter_id");
            $stmtDeleteJobs->bindParam(':recruiter_id', $recruiter_id, PDO::PARAM_INT);
            $stmtDeleteJobs->execute();

            // Supprimer le recruiter
            $stmtDeleteRecruiter = $conn->prepare("DELETE FROM recruiters WHERE id = :recruiter_id");
            $stmtDeleteRecruiter->bindParam(':recruiter_id', $recruiter_id, PDO::PARAM_INT);
            $stmtDeleteRecruiter->execute();
        }

        // Supprimer l'utilisateur
        $stmtDeleteUser = $conn->prepare("DELETE FROM users WHERE id = :user_id");
        $stmtDeleteUser->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmtDeleteUser->execute();



        echo "<div class='alert alert-success'>L'utilisateur et toutes ses données associées ont été supprimés.</div>";
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollBack();
        echo "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// if (isset($_GET['delete_id'])) {
//     $id = intval($_GET['delete_id']);
//     try {
//         $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
//         $stmt->execute([$id]);
//         $successMessage = "Utilisateur supprimé avec succès.";
//     } catch (PDOException $e) {
//         $errorMessage = "Erreur lors de la suppression de l'utilisateur : " . htmlspecialchars($e->getMessage());
//     }
// }

// Récupérer la liste des utilisateurs
try {
    $stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des utilisateurs : " . htmlspecialchars($e->getMessage()));
}
?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Gestions des utilisateurs</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->


    <div class="container mt-5">
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage; ?></div>
        <?php elseif ($errorMessage): ?>
            <div class="alert alert-danger"><?= $errorMessage; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Formulaire d'ajout d'utilisateur -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Ajouter un utilisateur</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Rôle</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="candidate">Candidat</option>
                                    <option value="recruiter">Recruteur</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <button type="submit" name="add_user" class="btn btn-primary">Ajouter</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Liste des utilisateurs -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Liste des Utilisateurs</div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['name']); ?></td>
                                        <td><?= htmlspecialchars($user['email']); ?></td>
                                        <td><?= htmlspecialchars($user['role']); ?></td>
                                        <td>
                                            <input type="checkbox" id="status-<?= $user['id']; ?>" name="status" value="1" data-user-id="<?= $user['id']; ?>" <?= ($user['status'] == 1) ? 'checked' : ''; ?> <?= ($user['role'] === 'admin') ? 'disabled' : ''; ?>>
                                        </td>


                                        <td>
                                            <a href="voir_user.php?id=<?= $user['id'] ?>" class="btn btn-info btn-sm">Voir</a>
                                            <a href="gestions_users.php?delete_id=<?= $user['id']; ?>" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Code JavaScript pour envoyer la requête AJAX lorsqu'on change l'état de la case
        document.querySelectorAll('input[type="checkbox"][data-user-id]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        var status = this.checked ? 1 : 0;
        var userId = this.getAttribute('data-user-id');

        // Requête AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_status_user.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status === 200) {
                alert("Le statut de l'utilisateur a été mis à jour.");
            } else {
                alert("Erreur lors de la mise à jour du statut.");
            }
        };

        xhr.send("status=" + status + "&id=" + userId);
    });
});

    </script>


</main>

<?php require_once "footer.php"; ?>