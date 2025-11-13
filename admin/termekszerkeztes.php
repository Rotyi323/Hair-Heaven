<?php
session_start();
include("../konfiguracio.php");
if ($_SESSION["jog"] != "admin") {
    header("Location: /");
}

//Lekérdezzük a szerkeszteni kívánt termék adatait.
$stmt = $mysqli->prepare("SELECT `id`, `nev`, `marka`, `ar`, `leiras`, `kep`, `letrehozva` FROM `termekek` WHERE `id` = ?");
$stmt->bind_param("s", $_GET['id']);
$stmt->execute();
$termek = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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

    <title>Termékszerkesztés</title>
</head>

<body>
    <?php include("../navbar.php"); ?>
    <div class="main col-6 mx-auto">
<!-- Form kezdet-->

        <form action="/api/editProduct.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $termek[0]["id"]; ?>">

        <div class="mb-3">
<!-- Megkeresi a jelenlegi fotót-->
        <label for="kep" class="form-label">Jelenlegi termék fotó</label>
        <img src="/api/kep.php?id=<?php echo $_GET["id"]; ?>" class="img-thumnnail" height="150px">
        </div>    

        <div class="mb-3">
                <label for="image" class="form-label">Termék fotó</label>
                <input class="form-control" type="file" id="image" name="image" accept="image/png, image/gif, image/jpeg"><!-- Támogatott formátumok-->
            </div>

<!-- Megkeresi a jelenlegi termék nevet-->
            <div class="row">
                <div class="mb-3 col-6">
                    <label for="nev" class="form-label">Termék neve</label>
                    <input type="text" class="form-control" id="nev" name="nev" placeholder="" value="<?php echo $termek[0]["nev"]; ?>">
                </div>
<!-- Megkeresi a jelenlegi termék márkáját-->
                <div class="mb-3 col-6">
                    <label for="marka" class="form-label">Termék márkája</label>
                    <input type="text" class="form-control" id="marka" name="marka" placeholder="" value="<?php echo $termek[0]["marka"]; ?>">
                </div>
            </div>

<!-- Megkeresi a jelenlegi termék árát-->
            <div class="mb-3 col-6">
                <label for="ar" class="form-label">Termék ára</label>
                <input type="number" class="form-control" id="ar" name="ar" placeholder="" value="<?php echo $termek[0]["ar"]; ?>">
            </div>
<!-- Megkeresi a jelenlegi termék leírását-->
            <div class="mb-3">
                <label for="leiras" class="form-label">Termék leírása</label>
                <textarea class="form-control" id="leiras" name="leiras" rows="3"><?php echo $termek[0]["leiras"]; ?> </textarea>
            </div>

            <button class="btn btn-primary" type="submit">Mentés</button>
        </form>
    </div>
    <script src="/bootstrap.bundle.min.js"></script>
</body>
</html>