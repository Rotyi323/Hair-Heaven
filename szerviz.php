<?php
include("konfiguracio.php");
session_start();
error_reporting(E_ERROR | E_PARSE);
$_SESSION["beküldve"] == true;
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Bootstrap5-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ikon-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <title>Szerviz</title>
    <style>
        h2,
        h3 {
            text-align: center;
            margin-top: 2.7rem;
            margin-bottom: 1.5rem;
        }

        label,
        option,
        select {
            font-weight: bold;
            text-align: center;
        }

        .keret {
            background-color: rgb(165, 157, 148);
            border: 0.4rem solid black;
            padding: 1.2rem;
        }

        .button {
            color: black;
            font-weight: bold;
            background-color: #1184DC;
            padding: 1rem 2rem;
            text-align: center;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 8px;
        }
    </style>
</head>

<body>
<div>
    <?php include("navbar.php"); ?>

    <h2>Üdvözöljük a TelefonFix szervizében! <br><i> Mit tehetünk Önért?</i></h2>
    <h3>Válassza ki a javítani kívánt eszközt!</h3>

    <div class="row">
        <!-- Form kezdet -->
        <form action="/endpoints/szerviz.php" method="post">
            <div class="col-12">
                <div class="col-6 mx-auto">
                    <div class="input-group mb-3">
                        <!-- Telefonok -->
                        <label class="input-group-text" for="inputGroupSelect01">Telefonok</label>
                        <select class="form-select" id="telefon" name="telefon" required>
                            <option selected disabled>Válasszon</option>
                            <?php
                            //Kilistázza az adatbázisban található termékeket.
                            $get = $mysqli->query("SELECT * FROM termekek ORDER BY letrehozva DESC");
                            while ($row = $get->fetch_assoc()) {
                                $id = $row["id"];
                                $nev = $row["nev"];
                                $ar = $row["ar"];

                                echo ' <option value="' . $nev . '">' . $nev . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Alkatrészek hibái -->
                <h3>Válassza ki a hibás alkatrészt!</h3>
                <div class="col-6 mx-auto">
                    <div class="input-group mb-3">
                        <select class="form-select" id="hiba" name="hiba" required>
                            <option selected disabled>Válasszon</option>
                            <option value="Kijelző">Kijelző</option>
                            <option value="Csatlakozók">Csatlakozók</option>
                            <option value="Hangszóró">hangszóró</option>
                            <option value="Mikrofon">Mikrofon</option>
                            <option value="Kamera">Kamera</option>
                            <option value="Egyéb">Egyéb</option>
                        </select>
                    </div>
                </div>
                <!-- Elérhetőségek-->
                <h3>Adja meg az elérhetőségét!</h3>
                <div class="col-6 mx-auto">
                    <div class="row">
                        <div class="col-6">
                            <!-- Telefonszám-->
                            <div class="input-group mb-3">
                                <span class="input-group-text">Telefonszám</span>
                                <input type="text" class="form-control" id="elerhetoseg" name="elerhetoseg"
                                    placeholder="" aria-label="Username" aria-describedby="basic-addon1" maxlength="30" required>
                            </div>
                        </div>
                        <!-- Email-->
                        <div class="col-6">
                            <div class="input-group mb-3">
                                <span class="input-group-text">Email cím</span>
                                <input type="email" class="form-control" id="email" name="email" placeholder=""
                                    aria-label="Username" aria-describedby="basic-addon1"  maxlength="100" required>
                            </div>
                        </div>
                    </div>
                </div>
                    <!--Beküldés gomb-->
                <div class="col-6 mx-auto text-center">
                    <button class="btn btn-primary" type="submit">Beküldés</button>
                </div>
            </div>
        </form>
    </div>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">© 2023 TelefonFix</p>
    </footer>

    <script src="bootstrap.bundle.min.js"></script>
    </div>
</body>
</html>