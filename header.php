<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="assets/css/colors.css" rel="stylesheet">

    <!-- Color modes -->
    <script src="assets/js/vendors/color-modes.js"></script>

    <!-- Libs CSS -->
    <link href="assets/libs/simplebar/dist/simplebar.min.css" rel="stylesheet">
    <link href="assets/libs/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Scroll Cue -->
    <link rel="stylesheet" href="assets/libs/scrollcue/scrollCue.css">

    <!-- Box icons -->
    <link rel="stylesheet" href="assets/fonts/css/boxicons.min.css">

    <!-- Theme CSS -->
    <link rel="stylesheet" href="assets/css/theme.min.css">
    <title>Site web d'embauche</title>
</head>



<body>
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light w-100 bg-light text-light">
            <div class="container px-3">
                <a class="navbar-brand" href="index.PHP"><img src="images/LOGO.jpg" class="img-fluid rounded" width="40px" alt=""></a>
                <button class="navbar-toggler offcanvas-nav-btn" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <div class="offcanvas offcanvas-start offcanvas-nav" style="width: 20rem">
                    <div class="offcanvas-header">
                        <a href="index.html" class="text-inverse"><img src="images/LOGO.jpg" class="img-fluid rounded" width="60px" alt=""></a>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body pt-0 align-items-center">
                        <ul class="navbar-nav mx-auto align-items-lg-center">
                            <li class="nav-item mx-2 my-2">
                                <a class="nav-link text-dark fs-5" href="index.php">Accueil</a>
                            </li>

                            <li class="nav-item mx-2 my-2">
                                <a class="nav-link text-dark fs-5" href="services.php">Services</a>
                            </li>

                            <li class="nav-item mx-2 my-2">
                                <a class="nav-link text-dark fs-5" href="propos.php">A propos</a>
                            </li>

                            <li class="nav-item mx-2 my-2">
                                <a class="nav-link text-dark fs-5" href="contact.php">Contact</a>
                            </li>


                            <li class="nav-item mx-2 my-2">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                        <a class="btn btn-light" href="espace_admin.php">Espace membre</a>
                                    <?php endif ?>

                                    <?php if ($_SESSION['role'] == 'recruiter'): ?>
                                        <a class="btn btn-light" href="espace_recruiter.php">Espace membre</a>
                                    <?php endif ?>

                                    <?php if ($_SESSION['role'] == 'candidate'): ?>
                                        <a class="btn btn-light" href="espace_candidate.php">Espace membre</a>
                                    <?php endif ?>

                                <?php endif; ?>

                            </li>
                        </ul>
                        <div class="mt-3 mt-lg-0 d-flex align-items-center">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="deconnexion.php" class="btn btn-danger mx-2 my-2">Deconnexion</a>
                            <?php else : ?>
                                <a href="login.php" class="btn btn-warning mx-2 my-2">Connexion</a>
                            <?php endif ?>

                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>