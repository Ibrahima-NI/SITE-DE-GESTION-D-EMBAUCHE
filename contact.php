<?php
// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire et les nettoyer
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Valider les champs
    if (empty($name) || empty($email) || empty($message)) {
        echo "Tous les champs sont requis.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Adresse e-mail invalide.";
        exit;
    }

    // Configurer les informations d'envoi
    $to = "ihentreprise04@gmail.com"; // Adresse de réception
    $subject = "Nouveau message de contact de $name";
    $body = "Vous avez reçu un nouveau message via le formulaire de contact.\n\n".
            "Nom: $name\n".
            "E-mail: $email\n\n".
            "Message:\n$message\n";

    $headers = "From: $email" . "\r\n" .
               "Reply-To: $email" . "\r\n" .
               "Content-Type: text/plain; charset=UTF-8";

    // Envoyer l'e-mail
    if (mail($to, $subject, $body, $headers)) {
        echo "Votre message a été envoyé avec succès. Merci de nous avoir contactés.";
    } else {
        echo "Une erreur est survenue lors de l'envoi de votre message. Veuillez réessayer plus tard.";
    }
} else {
    echo "Méthode de requête non prise en charge.";
}
?>

