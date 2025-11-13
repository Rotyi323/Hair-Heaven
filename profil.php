<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
if ($_SESSION["belepve"] == false) {
    header("Location: /");
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Ikon-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Bootstrap & CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

    <title>Profil szerkesztés</title>
</head>

<body>
    <?php include("navbar.php"); ?>

    <!-- Form kezdet -->
    <form action="/endpoints/modify.php" method="post" autocomplete="off" id="modify">
        <div class="col-xl-9 col-lg-9 col-md-12 col-sm-12 col-12 mx-auto">
            <div class="card h-50">
                <div class="card-body">
                    <div class="row gutters">
                        <!-- Form fejléc-->
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            <h4 class="mb-2 text-primary text-center">Adatok módosítása</h4>
                        </div>
                        <!-- Felhasználónév-->
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                            <div class="form-group">
                                <label for="username">Felhasználónév</label>
                                <input type="text" class="form-control" maxlength="100" id="username"  name="username"
                                    placeholder="Felhasználónév" value="<?php echo $_SESSION["nev"]; ?>">
                            </div>
                            <!-- Email-->
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" maxlength="100" name="email" placeholder="Email"
                                    value="<?php echo $_SESSION["email"]; ?>">
                            </div>
                        </div>
                        <!-- Jelsző-->
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12 mx-auto">
                            <div class="form-group">
                                <label for="pw">Jelszó</label>
                                <input type="password" class="form-control" id="pw" maxlength="100" name="pw"
                                    placeholder="Adjon meg egy új jelszót ha szükséges" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="row gutters">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            <div class="text-right mt-3">
                                <button type="submit" id="submit" name="submit" class="btn btn-primary">Módosítás</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        //A felhasználó módósítához ajax  lekérést küldünk a szükséges adatokkal.
        $(document).ready(function () {
            $("#modify").on("submit", function (e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "/endpoints/modify.php",
                    data: $("#modify").serialize(),
                    dataType: "json",
                    encode: true,
                }).done(function (res) {
                    if (res.success === true) {
                        alert("Sikeres módosítás");
                        location.reload();
                    } else {
                        alert("Hiba: " + res.error);
                    }
                });
            });
        });
    </script>

    <script src="bootstrap.bundle.min.js"></script>

</body>
</html>