<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'site d\'embauche';
$username = 'root'; // Utilisez votre utilisateur MySQL
$password = ''; // Utilisez votre mot de passe MySQL
$conn = new mysqli($host, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué: " . $conn->connect_error);
}

$results = [];
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search-type']) && isset($_GET['search-keyword'])) {
    $search_type = $_GET['search-type'];
    $search_keyword = $_GET['search-keyword'];
    
    if ($search_type == 'candidate') {
        // Rechercher des candidats par nom ou email
        $stmt = $conn->prepare("SELECT * FROM candidat WHERE nom_c LIKE ? OR email LIKE ?");
        $search_term = "%$search_keyword%";
        $stmt->bind_param("ss", $search_term, $search_term);
    } else if ($search_type == 'company') {
        // Rechercher des entreprises par nom ou email
        $stmt = $conn->prepare("SELECT * FROM employer WHERE nom_e LIKE ? OR email LIKE ?");
        $search_term = "%$search_keyword%";
        $stmt->bind_param("ss", $search_term, $search_term);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $results = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche</title>
</head>
<body>
    <h1>Recherche</h1>
    <form action="recherche.php" method="get">
        <label for="search-type">Type de Recherche</label>
        <select id="search-type" name="search-type" required>
            <option value="candidate">Candidat</option>
            <option value="company">Entreprise</option>
        </select><br><br>

        <label for="search-keyword">Mot-clé</label>
        <input type="text" id="search-keyword" name="search-keyword" placeholder="Nom, compétence, entreprise..." required><br><br>

        <button type="submit">Rechercher</button>
    </form>

    <?php if (count($results) > 0): ?>
        <h2>Résultats de la recherche:</h2>
        <ul>
            <?php foreach ($results as $row): ?>
                <li>
                    <?php if ($_GET['search-type'] == 'candidate'): ?>
                        <strong><?php echo htmlspecialchars($row['nom_c']); ?></strong> - <?php echo htmlspecialchars($row['email']); ?>
                    <?php else: ?>
                        <strong><?php echo htmlspecialchars($row['nom_e']); ?></strong> - <?php echo htmlspecialchars($row['contact']); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "GET"): ?>
        <p>Aucun résultat trouvé.</p>
    <?php endif; ?>
</body>
</html>
