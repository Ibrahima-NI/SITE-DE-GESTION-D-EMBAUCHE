<?php
require_once "header.php";
require_once "config.php";

?>
<title>Réinitialisation</title>

<body>



<?php
if (isset($_POST['password_forget'])) {
    function token_random_string($leng = 20) {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < $leng; $i++) { 
            $token .= $str[rand(0, strlen($str) - 1)];
        }
        return $token;
    }

    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $message = "Renter une adresse email valide";
    } else {
        require('config.php');

        $requete = $conn->prepare('SELECT * FROM embauchee.users WHERE email = :email');
        $requete->bindValue(':email', $_POST['email']);
        $requete->execute();

        $result = $requete->fetch();
        $nombre = $requete->rowCount();

        if ($nombre != 1) {
            $message = "L'adresse email saisie ne correspond pas à aucun utilisateur de notre espace";
        } else {
            if ($result['email_verified'] != 1) {
                $token = token_random_string(20);

                $update = $conn->prepare('UPDATE embauchee.users SET security_token = :security_token WHERE email = :email');
                $update->bindValue(':security_token', $token);
                $update->bindValue(':email', $_POST['email']);
                $update->execute();

                           
            }else{
                $token = token_random_string(20);
                $requete1 = $conn->prepare('SELECT * FROM embauchee.recup_password WHERE email=:email');
                $requete1->bindValue(':email', $_POST['email']);
                $requete1->execute();

                $nombre1 = $requete1->rowCount();

                if($nombre1 == 0){
                    $requete2 = $conn->prepare('INSERT INTO embauchee.recup_password(email,token) VALUES (:email,:token)');
                    $requete2->bindValue(':email',$_POST['email']);
                    $requete2->bindValue(':token',$token);
                    $requete2->execute();


                }else{
                    $requete3 = $conn->prepare('UPDATE embauchee.recup_password SET token=:token WHERE email=:email');
                    $requete3->bindValue(':token',$token);
                    $requete3->bindValue(':email',$_POST['email']);
                    $requete3->execute();
                }

                require_once 'sendmail_recup.php';

            }
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
                        <h1>Mot de passe oublié</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Pageheader end-->
<?php  
$success = "";
$error = "";?>
    <!--Sign up start-->
    <section>
        <h6 class="text-center text-black pt-5">
        Merci d'entrer votre adresse email ci-dessous, nous vous enverrons des instructions pour réinitialiser votre mot de passe.
        </h6>
        <div class="container">
            <div class="row justify-content-center mb-6">
                <div class="col-xl-5 col-lg-6 col-md-8 col-12">
                     <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?php echo $success ?></div>
                    <?php endif; ?> 
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <form class="needs-validation mb-6" method="post" action="sendmail_recup.php">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email :</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="form-group">
                            <input type="submit" name="password_forget" class="btn btn-warning" value="Réinitialiser mon mot de passe">
                              </div>
                                
                            </form>


                        </div>
                    </div>

                   
                </div>
            </div>

        </div>
    </section>
    <!--Sign up end-->

</main>
</body>
<?php require_once "footer.php"; ?>