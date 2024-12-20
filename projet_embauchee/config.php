<?php


// Connexion à la base des donner
$host = 'localhost';
$dbname = 'embauchee';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection a la base de donner echouer : " . $e->getMessage());
}
