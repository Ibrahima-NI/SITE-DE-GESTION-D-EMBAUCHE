<?php
require_once "header.php";
require_once "config.php"; // Connexion à la base de données

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom = trim($_POST['nom']);
  $phone = trim($_POST['phone']);
  $sujet = trim($_POST['sujet']);
  $message = trim($_POST['message']);

  // Validation des champs
  if (empty($nom) || empty($phone) || empty($sujet) || empty($message)) {
    $errorMessage = 'Tous les champs sont obligatoires.';
  } else {
    try {
      $stmt = $conn->prepare("INSERT INTO contacts (nom, phone, sujet, message) VALUES (?, ?, ?, ?)");
      $stmt->execute([$nom, $phone, $sujet, $message]);
      $successMessage = 'Votre message a été envoyé avec succès. Nous vous répondrons bientôt.';
    } catch (PDOException $e) {
      $errorMessage = 'Erreur lors de l\'envoi du message : ' . $e->getMessage();
    }
  }
}
?>

<div class="container mt-5">
  <h2 class="text-center">Contactez-nous</h2>
  <p class="text-center">Si vous avez des questions ou des préoccupations, veuillez remplir le formulaire ci-dessous.</p>

  <!-- Affichage des messages d'alerte -->
  <?php if (!empty($successMessage)): ?>
    <div class="alert alert-success"><?= $successMessage; ?></div>
  <?php endif; ?>

  <?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger"><?= $errorMessage; ?></div>
  <?php endif; ?>

  <!-- Formulaire de contact -->
  <form method="POST" action="contact.php" class="mt-4">
    <div class="row">
      <!-- Nom -->
      <div class="col-12 mb-3">
        <label for="nom" class="form-label">Nom complet</label>
        <input type="text" name="nom" id="nom" class="form-control" placeholder="Entrez votre nom" required>
      </div>

      <!-- Téléphone et Sujet -->
      <div class="col-md-6 mb-3">
        <label for="phone" class="form-label">Téléphone</label>
        <input type="text" name="phone" id="phone" class="form-control" placeholder="Entrez votre numéro de téléphone" required>
      </div>
      <div class="col-md-6 mb-3">
        <label for="sujet" class="form-label">Sujet</label>
        <input type="text" name="sujet" id="sujet" class="form-control" placeholder="Entrez le sujet" required>
      </div>

      <!-- Message -->
      <div class="col-12 mb-3">
        <label for="message" class="form-label">Message</label>
        <textarea name="message" id="message" class="form-control" rows="5" placeholder="Écrivez votre message ici" required></textarea>
      </div>

      <!-- Bouton d'envoi -->
      <div class="col-12 text-center">
        <button type="submit" class="btn btn-primary">Envoyer le message</button>
      </div>
    </div>
  </form>
</div>


<?php require_once "footer.php"; ?>