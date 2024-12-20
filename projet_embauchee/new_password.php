<?php
require_once "header.php";
require_once "config.php";
?>
<?php 
if($_GET){
	if(isset($_GET['email'])){
		$email = $_GET['email'];
	}
	if(isset($_GET['token'])){
		$token = $_GET['token'];
	}

if(!empty($email) && !empty($token)){
	require_once('config.php');
	$requete = $conn->prepare('SELECT * FROM embauchee.recup_password WHERE email = :email AND token = :token');

	$requete->bindValue(':email',$email);
	$requete->bindValue(':token',$token);

	$requete->execute();

	$nombre = $requete->rowCount();

	if($nombre!=1)
	{
		header('Location:inscription.php');
	}else
	{
		if(isset($_POST['new_password']))
		{
			if(empty($_POST['password']) || $_POST['password']!=$_POST['password2'])
			{
				$message = "Renter un mot de passe valide";
			}
			else
			{
				$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
				$requete =$conn->prepare('UPDATE embauchee.users SET password=:password WHERE email=:email');
				$requete->bindValue(':email',$email);
				$requete->bindValue(':password',$password);
				$result = $requete->execute();

				if($result){
				echo "<script type =\"text/javascript\">
				alert('Votre mot de passe est bien réinitialiser');
				document.location.href='login.php';
				</script>";

				}else{
					$message = "Votre mot de passe n'a pas été réinitialisé";
					header('Location:connexion.php');
				}

			} 
		}
	}



}

}else{
	header('Location:inscription.php');
}




?>


<main>
    <section class="py-5 py-lg-8">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12 col-12">
                    <div class="text-center">
                        <h1>Nouveau mot de passe</h1>
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
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <form class="needs-validation mb-6" method="post" action="">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Votre nouveau mot de passe :</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password2" class="form-label">Confirmation du mot de passe </label>
                                    <div class="password-field position-relative">
                                        <input type="password" class="form-control fakePassword" id="password2" name="password2" required="">
                                    </div>
                                </div>


                                <div class="d-flex align-items-center justify-content-between">
                                       <input type="submit" name="new_password" class="btn btn-warning btn-md" value="Valider">
                                
                            </form>


                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!--Sign up end-->

</main>

<?php require_once "footer.php"; ?>