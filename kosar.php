<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

    <title>Kosár</title>

    <style>
        label,
        input {
            font-weight: bold;
            font-size: 20px;
            border: 10px black;
            max-width: 34rem;
        }

        select,
        input[type=text],
        [type=email] {
            box-sizing: border-box !important;
            border: 3px solid rgb(41, 2, 2) !important;
        }

        .keret {
            background-color: #84d6e8;
            border: 0.4rem solid black;
            border-radius: 1rem;
            padding: 1.2rem;
        }
    </style>
</head>

<body>
    <?php include("navbar.php");
    ?>

    <!-- Fejléc -->
    <div class="container">
        <div class="col-12 mx-auto text-center">
            <h2><b>Kosár</b></h2>
        </div>
        <!-- Ablak kezdet -->
        <div class="keret">
            <div class="row">
                <!-- Kosár -->
                <div class="col-md-4 order-md-2 mb-4">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted" style="font-weight: bold;">Ön kosara:</span>
                    </h4>

                    <ul class="list-group mb-3">
                        <?php //Kosár elemeinek megjelenítése, kosár tartalma, mennyiség, ár ezres tagolás, elem törlése
                        
                        for ($i = 0; $i < $_SESSION["cart"]["num_of_items"]; $i++) {
                            echo '
                                  <li class="list-group-item d-flex justify-content-between lh-condensed">
                                   <div>
                                     <h5 class="my-auto">' . $_SESSION["cart"]["items"][$i]["name"] . '</h5>
                                     <small class="text-muted">' . $_SESSION["cart"]["items"][$i]["quantity"] . ' db</small>
                
                                     </div>
                                     <span class="text-muted">' . number_format($_SESSION["cart"]["items"][$i]["Item_Total"], 0, " ", " ") . ' Ft</span> 
                                     <a class="btn btn-danger" href="/api/deleteCartItem.php?index=' . $i . '"><i class="fa fa-trash-o"></i></a>
                                      </li>
                                         ';
                                           } ;
                        ?>

                        <li class="list-group-item d-flex justify-content-between">
                            <span>Összesen</span>
                            <strong>
                                <?php echo (number_format($_SESSION["cart"]["Total"], 0, " ", " ")) ?> Ft
                            </strong>
                        </li>
                    </ul>
                </div>
                <!-- Vásárló adatai -->
                <div class="col-md-8 order-md-1">
                    <h4 class="mb-3" style="font-weight: bold;">Vásárló adatai</h4>
                    <!-- Form kezdet -->
                    <form class="needs-validation" id="order" method="post" action="/api/placeOrder.php" novalidate="">
                        <!-- Név -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName">Teljes név</label>
                                <input type="text" class="form-control" id="nev" name="nev" placeholder="Teszt Elek"
                                    value="" maxlength="100" required>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email">E-mail <span class="text-muted"></span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="TesztElek@példa.com" maxlength="100" required>
                        </div>

                        <!-- Számlázási cím -->
                        <div class="mb-3">
                            <label for="address">Számlázási cím</label>
                            <input type="text" class="form-control" id="cim" name="cim" placeholder="Város utca házszám"
                                maxlength="255" required>
                        </div>

                        <!-- Ország -->
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="country">Ország</label>
                                <input type="text" class="form-control" id="orszag" name="orszag"
                                    placeholder="Magyarország" maxlength="30" required>
                            </div>

                            <!-- Vármegye -->
                            <div class="col-md-4 mb-3">
                                <label for="state">Vármegye</label>
                                <input type="text" class="form-control" id="megye" name="megye"
                                    placeholder="Békés vármegye" maxlength="50" required>
                            </div>

                            <!-- Irányítószám -->
                            <div class="col-md-3 mb-3">
                                <label for="zip">Irányítószám</label>
                                <input type="text" class="form-control" id="irszam" name="irszam" placeholder="5600"
                                    maxlength="10" required>
                            </div>

                        </div>
                        <!-- Fizetési módok -->
                        <hr class="mb-4">
                        <h4 class="mb-3">Fizetési mód</h4>

                        <div class="d-block my-3">
                            <!-- Bankkártya -->
                            <div class="custom-control custom-radio">
                                <input id="credit" name="paymentMethod" type="radio" class="custom-control-input"
                                    value="Bankkártya" checked="" required>
                                <label class="custom-control-label" for="credit">Bankkártya</label>
                            </div>
                            <!-- Hitelkártya -->
                            <div class="custom-control custom-radio">
                                <input id="debit" name="paymentMethod" type="radio" class="custom-control-input"
                                    value="Hitelkártya" required>
                                <label class="custom-control-label" for="debit">Hitelkártya</label>
                            </div>
                            <!-- Paypal -->
                            <div class="custom-control custom-radio">
                                <input id="paypal" name="paymentMethod" type="radio" class="custom-control-input"
                                    value="PayPal" required>
                                <label class="custom-control-label" for="paypal">PayPal</label>
                            </div>
                        </div>

                        <hr class="mb-4">
                        <button class="btn btn-primary btn-lg btn-block" type="submit">Megrendelem &raquo;</button>
                    </form>
                </div>
            </div>
        </div>

        <footer class="my-5 pt-5 text-muted text-center text-small">
            <p class="mb-1">© 2023 TelefonFix</p>
        </footer>

    </div>
    <script src="bootstrap.bundle.min.js"></script>

    <!-- Siker vagy hiba üzenet kiírása Ajaxxal -->
    <script>
        $(document).ready(function () {
            $("#order").on("submit", function (e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "/api/placeOrder.php",
                    data: $("#order").serialize(),
                    dataType: "json",
                    encode: true,
                }).done(function (res) {
                    if (res.success === true) {
                        alert("Sikeres rendelés");
                        location.reload();
                    } else {
                        alert("Hiba: " + res.error);
                    }
                });
            });
        });
    </script>
</body>
</html>