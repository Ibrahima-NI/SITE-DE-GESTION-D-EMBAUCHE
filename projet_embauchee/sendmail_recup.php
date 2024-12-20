<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    die("Adresse email invalide.");
}

$email = htmlspecialchars($_POST['email']); // Email de l'utilisateur
$token = bin2hex(random_bytes(20)); // Génère un token aléatoire pour la réinitialisation

// Connexion à la base de données
require_once 'config.php';

// Vérifier si l'utilisateur existe
$query = $conn->prepare('SELECT * FROM embauchee.users WHERE email = :email');
$query->bindValue(':email', $email);
$query->execute();

if ($query->rowCount() == 1) {
    // L'utilisateur existe, enregistrer le token pour réinitialisation
    $insertQuery = $conn->prepare('INSERT INTO embauchee.recup_password (email, token) VALUES (:email, :token)');
    $insertQuery->bindValue(':email', $email);
    $insertQuery->bindValue(':token', $token);
    $insertQuery->execute();

    // Configuration de PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP(); 
        $mail->Host = 'smtp.gmail.com'; // Serveur SMTP
        $mail->SMTPAuth = true; 
        $mail->Username = 'ihcompany.recru@gmail.com'; // Votre adresse Gmail
        $mail->Password = 'psyr whdq vmyw mfff'; // Votre mot de passe d'application Gmail
        $mail->SMTPSecure = 'tls'; 
        $mail->Port = 587; 
        $mail->CharSet = "utf-8";

        // Configuration des détails de l'email
        $mail->setFrom('ihcompany.recru@gmail.com', 'Entreprise d\'embauche'); // Expéditeur
        $mail->addAddress($email); // Destinataire
        $mail->isHTML(true); 

        $mail->Subject = 'Réinitialisation du mot de passe'; // Objet
        $mail->Body = "Bonjour,<br><br>
                       Afin de réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant : <br>
                       <a href='http://localhost/projet_embauchee/new_password.php?email=$email&token=$token'>Réinitialiser mon mot de passe</a><br><br>
                       Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.";

        $mail->send();
        echo "Un email a été envoyé à votre adresse avec les instructions pour réinitialiser votre mot de passe.";
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo;
    }
} else {
    echo "L'adresse email saisie ne correspond à aucun utilisateur de notre espace.";
}
?>
