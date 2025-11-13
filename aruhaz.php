<?php
session_start();
include("konfiguracio.php");
error_reporting(E_ERROR | E_PARSE);
//Tömb és osztály definiálása
$termek_tarolo = array();
class termekek {
    protected $id;
    protected $nev;
    protected $ar;
    protected $kep;
    protected $leiras;
      function __construct($id,$nev, $ar, $kep, $leiras) //Constructor bevezetése
    {
        $this->id  = $id;
        $this->nev  = $nev;
        $this->ar  = $ar;
        $this->kep  = $kep;
        $this->leiras  = $leiras; 
    }
     function megjelenit() //Funkció definiálása, kártya létrehozása, formázása
    {
            return '<div class="card product py-3 col-sm-12 col-lg-4 col-md-6 d-flex justify-content-center text-center">
                <h4 class="d-none">' . $this->nev . '</h4>
                <img src="/api/kep.php?id=' . $this->id . '"  class="card-img-top" width="270px" height="270px">
                    <div class="card-body">
                        <h4 class="card-title" style="text-align:center;">' . $this->nev . '</h4>
                        <h4 class="card-text"  style="text-align:center;">' . number_format($this->ar, 0, " ", " ") . 'Ft</h4>
                        <p class="fw-bold text-dark" style="text-align:center;">Leírás</p>
                        <p class="card-text">' . $this->leiras . '</p>
                        <a class="btn btn-primary mt-3" data-single-name="' . $this->nev . '" >Kosárba</a>
                    </div>
            </div>';
    }
}        
        $check = $mysqli->query("SELECT * FROM termekek ");
            if ($check->num_rows > 0) {
                while ($row = $check->fetch_assoc()) {
                    $termek = new termekek($row['id'],$row['nev'],$row['ar'],$row['kep'],$row['leiras']);
                    array_push($termek_tarolo,$termek);
                    }
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
     <!-- Ajax -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

    <title>Áruház</title>
    <style>
        .product {
            border: 1px solid #ddd;
            margin: 10px;
            padding: 10px;
            max-width: 300px;
        }
        .product h3 {
            margin: 0 0 5px 0;
        }
        .product p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        h2 {
            text-align: center;
            margin-top: 2rem;
        } 
        input {
            margin: 2rem;
        }
        h5 {
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include("navbar.php"); ?>

    <div class="col-12 mx-auto">
        <h2>Üdvözöljük a TelefonFix áruházában! <br>
            Válasszon a kínálatunkból!
        </h2>
    </div>
    
    <div class="container">
        <div class="row">
              <?php //Funkció meghívása, termékek listázása
                foreach ($termek_tarolo as $termek) {
                    echo $termek->megjelenit();
                }
              ?>
        </div>
    </div>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">© 2023 TelefonFix</p>
    </footer>

    <script>
      //A terméket ajax lekéréssel elhelyezzük a kosárban
        $("[data-single-name]").click(function(e) {
            var name = $(this).attr("data-single-name");
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "/api/addItemToCart.php",
                data: {              
                    name: name,                
                    quantity: 1,    
                },
                dataType: "json",
                encode: true,
            }).done(function(res) {
              alert("A termék bekerült a kosárba!"); 
            });
        });
        
    </script>

    <script src="bootstrap.bundle.min.js"></script>
</body>
</html>