<?php
// Informations de connexion à la base de données
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'projet';

// Connexion à la base de données
$conn = new mysqli($host, $user, $password, $database);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

// Vérifiez si un formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['submit-candidate'])) {
        // Traitement pour le formulaire de candidat
        $nom = $_POST['candidate-lastname'] ?? '';
        $prenom = $_POST['candidate-firstname'] ?? '';
        $email = $_POST['candidate-email'] ?? '';
        $cv = $_FILES['candidate-cv']['tmp_name'] ?? '';

        if (empty($nom) || empty($prenom) || empty($email) || empty($cv)) {
            die("Erreur : Tous les champs sont obligatoires.");
        }

        if ($_FILES['candidate-cv']['error'] === 0) {
            // Lire le contenu du CV
            $cvData = file_get_contents($cv);

            // Préparer et exécuter l'insertion dans la table `candidat`
            $stmt = $conn->prepare("INSERT INTO candidat (nom_c, prenom, email, cv, statut) VALUES (?, ?, ?, ?, 'en cours')");
            $stmt->bind_param("ssss", $nom, $prenom, $email, $cvData);

            if ($stmt->execute()) {
                echo "<script>alert('Inscription du candidat réussie !');</script>";
            } else {
                echo "<script>alert('Erreur lors de l'inscription : " . $stmt->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Erreur : Téléchargement du CV échoué.');</script>";
        }
    } elseif (isset($_POST['submit-company'])) {
        // Traitement pour le formulaire d'entreprise
        $nomEntreprise = $_POST['company-name'] ?? '';
        $emailEntreprise = $_POST['company-email'] ?? '';
        $descriptionPoste = $_POST['job-offer'] ?? '';

        if (empty($nomEntreprise) || empty($emailEntreprise) || empty($descriptionPoste)) {
            die("Erreur : Tous les champs sont obligatoires.");
        }

        // Préparer et exécuter l'insertion dans la table `employer`
        $stmt = $conn->prepare("INSERT INTO employer (nom_e, email, contact) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nomEntreprise, $emailEntreprise, $descriptionPoste);

        if ($stmt->execute()) {
            echo "<script>alert('Demande d'entreprise soumise avec succès !');</script>";
        } else {
            echo "<script>alert('Erreur lors de la soumission : " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Erreur : Formulaire non reconnu.');</script>";
    }
}

// Fermer la connexion
$conn->close();
?>
