<?php
session_start();
include("../konfiguracio.php");
//A rendszer nem enged tovább ha nem admin a felhasználót.
if ($_SESSION["jog"] != "admin") {
    header("Location: /");
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Ikon-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Bootstrap & CSS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Termék hozzáadása</title>
</head>

<body>
    <?php include("../navbar.php"); ?>
    <div class="main col-6 mx-auto">

  <!-- Form kezdet, api hivatkozás -->
        <form action="/api/addProduct.php" method="post" enctype="multipart/form-data">
<!-- Termék kép-->
            <div class="mb-3">
                <label for="kep" class="form-label">Termék fotó</label>
                <input class="form-control" type="file" id="kep" name="image" accept="image/png, image/gif, image/jpeg">   <!-- Támogatott kép formátumok -->
            </div>

<!-- Termék név-->
            <div class="row">
                <div class="mb-3 col-6">
                    <label for="nev" class="form-label">Termék neve</label>
                    <input type="text" class="form-control" id="nev" name="nev" placeholder="">
                </div>
<!-- Termék márka-->
                <div class="mb-3 col-6">
                    <label for="marka" class="form-label">Termék márkája</label>
                    <input type="text" class="form-control" id="marka" name="marka" placeholder="">
                </div>
            </div>
<!-- Termék ára-->
            <div class="mb-3 col-6">
                    <label for="ar" class="form-label">Termék ára</label>
                    <input type="number" class="form-control" id="ar" name="ar" placeholder="">
                </div>
<!-- Termék leírása-->
            <div class="mb-3">
                <label for="leiras" class="form-label">Termék leírása</label>
                <textarea class="form-control" id="leiras" name="leiras" rows="3"></textarea>
            </div>

            <button class="btn btn-primary" type="submit">Létrehozás</button>

        </form>
    </div>
    <script src="/bootstrap.bundle.min.js"></script>
</body>
</html>