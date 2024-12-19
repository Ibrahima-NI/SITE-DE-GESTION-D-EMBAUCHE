<style>
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh; /* Assure que le corps de la page occupe au moins toute la hauteur de la fenêtre */
        margin: 0;
    }

    main {
        flex: 1; /* Prend l'espace restant entre le header (s'il y en a) et le footer */
    }

    footer {
        background-color: lightgray;
        text-align: center;
        padding: 10px;
    }
</style>


<div class="mt-5">
    <footer class="mt-5 mt-5 mt-lg-8 bg-light text-dark rounded text-light p-2 text-center">
        <p class="mt-2" style="color: black; font-weight: bold;" >Copyright &copy; <?= date('Y') ?> IH_entreprise| all right réserved</p>
    </footer>
</div>
</body>


<!-- Libs JS -->
<script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/simplebar/dist/simplebar.min.js"></script>
<script src="assets/libs/headhesive/dist/headhesive.min.js"></script>

<!-- Theme JS -->
<script src="assets/js/theme.min.js"></script>

<script src="assets/libs/scrollcue/scrollCue.min.js"></script>
<script src="assets/js/vendors/scrollcue.js"></script>

</html>