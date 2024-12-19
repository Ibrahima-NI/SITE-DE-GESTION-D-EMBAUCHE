<?php
// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer/src/Exception.php";
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";

require_once "header.php";
require_once "config.php";
// Variables for success and error messages
$success = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $confirm_password = $_POST['confirme'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Il y'a des champs vides";
    } elseif ($password !== $confirm_password) {
        $error = "Le mot de passe et son confirmation n'est sont pas egaux.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $verification_code = bin2hex(random_bytes(16));
        $security_token = bin2hex(random_bytes(18));

        try {
            // Insertion dans la base des donner
            $stmt = $conn->prepare("INSERT INTO Users (name, email, password, role, email_verified, verification_code, security_token)
                                    VALUES (:name, :email, :password, :role, 0, :verification_code, :security_token)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashed_password,
                ':role' => $role,
                ':verification_code' => $verification_code,
                ':security_token' => $security_token,
            ]);

            // Send email with PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'embauche.projet.i.h@gmail.com'; // Replace with your email
                $mail->Password = 'ylbf ftcs dpjj jgnx'; // Replace with your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('embauche.projet.i.h@gmail.com', 'Entreprise d\'embauche I&H.');
                $mail->addAddress($email, $name);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Confirmer votre email.';
                $mail->Body    = "
                    <p>Bonjour, $name.<p>
                    <p>Merci de votre inscription. Veuillez confirmer votre email en cliquant sur le lien ci-dessous:</p>
                    <a href='http://localhost/projet_embauchee/login.php?code=$verification_code&token=$security_token'>Confirmer votre email</a>
                ";

                $mail->send();
            } catch (Exception $e) {
                $error = "email non envoyer. {$mail->ErrorInfo}";
            }

            $success = 'Inscription bien reussi. Veuillez confirmer votre email.';
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}



?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Inscription</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->




    <!--Sign up start-->
    <section>
        <div class="container">
            <div class="row justify-content-center mb-6">
                <div class="col-xl-5 col-lg-6 col-md-8 col-12">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <form class="needs-validation mb-6" method="post" action="register.php">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Votre nom :</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email :</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="role">Choisisez votre role :</label>
                                    <select class="form-select form-control" name="role">
                                        <option value="recruiter">Recruiter</option>
                                        <option value="candidate">Candidate</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe :</label>
                                    <div class="password-field position-relative">
                                        <input type="password" class="form-control fakePassword" id="password" name="password" required="">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirme" class="form-label">Confirm Password</label>
                                    <div class="password-field position-relative">
                                        <input type="password" class="form-control fakePassword" id="confirme" name="confirme" required="">

                                    </div>
                                </div>


                                <div class="d-grid">
                                    <button class="btn btn-warning" type="submit" name="submit">S'inscrire</button>
                                </div>
                            </form>


                        </div>
                    </div>

                    <span>
                        Vous avez un compte ?
                        <a href="login.php" class="text-primary">Se connecter.</a>
                    </span>
                </div>
            </div>

        </div>
    </section>
    <!--Sign up end-->

</main>

<?php require_once "footer.php"; ?>