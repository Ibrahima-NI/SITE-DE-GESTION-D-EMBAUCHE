<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';


$mail = new PHPMailer(true);//instancier la classe PHPMailer 
$mail->isSMTP();//Specier que PHPMailer utilise le protocole SMTP
$mail->Host = 'smtp.gmail.com';//Specier le serveur gmail
$mail->SMTPAuth = true ; //Activer l'authentification
$mail->Username = 'Entreprise d\'embauche';//le compt utilisateur
$mail->Password = 'fgqx nyts sgwm tlji';//mot de passe 
$mail->SMTPSecure = 'tls'; //un type de cryptage
$mail->Port = 587;//port gmail 
$mail->CharSet = "utf-8";//type de codage 
$mail->setFrom('embauche.projet.i.h@gmail.com','Entreprise d\'embauche');//email de l'expediteur
$mail->addAddress($_POST['email']);//envoi leamil à l'adresse saisie par l'utilisateur
$mail->isHTML(true);//Pour activer l'envoi de mail sous forme html

$mail->Subject = 'Confirmation d\'email';//l'objet de l'email
$mail->Body = 'Afin de valider votre adresse email , mercie de cliquer sur le lien suivant:<a href="http://localhost/formation/espacemembres/verification.php?email='.$_POST['email'].'&token='.$token.'">Confirmation</a>';


$mail->SMTPDebug = 0; //desactiver le debug

if(!$mail->send()){
	$message = "Mail non envoyé";
	echo 'Erreurs:' .$mail->ErrorInfo;
}else{
	$message="Un email vous a été envoiyé , mercie de consulter votre boite email! ";
	
}


?>
