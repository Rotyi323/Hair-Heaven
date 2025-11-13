<?php
session_start();
include("../konfiguracio.php");
if ($_SESSION["jog"] != "admin") { //Ellenőrizzük hogy admin-e
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <title>Admin</title>
</head>

<body>
    <?php include("../navbar.php"); ?>

    <div class="main col-10 mx-auto">
        <div class="col-12">
  <!-- Táblázat teteje-->

            <ul class="nav nav-tabs" id="myTab">
                <li class="nav-item">
                    <a href="#felhasznalok" class="nav-link active" data-bs-toggle="tab">Felhasználók</a>
                </li>
                <li class="nav-item">
                    <a href="#megrendelesek" class="nav-link" data-bs-toggle="tab">Megrendelések</a>
                </li>
                <li class="nav-item">
                    <a href="#szerviz" class="nav-link" data-bs-toggle="tab">Szervíz</a>
                </li>
                <li class="nav-item">
                    <a href="#termekek" class="nav-link" data-bs-toggle="tab">Termékek</a>
                </li>
            </ul>

  <!-- Felhasználók tábla -->
   <!-- Felhasználók fejcím -->
            <div class="tab-content">
                <div class="tab-pane fade show active" id="felhasznalok">
                    <div class="card" style="border-top-left-radius: 0;border-top-right-radius: 0;border-top-color: white;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Név</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Jog</th>
                                    <th scope="col">Műveletek</th>
                                </tr>
                            </thead>
                     <!-- Felhasználók törzs -->
                            <tbody>
                                <?php

                                //Az adatbázisból kiszedjük a felhasználók adatait. 
                                $result = $mysqli->query("SELECT * FROM `users`");
                                while ($row = $result->fetch_assoc()) {
                                    $admin = "";
                                    $guest = "";

                                    if ($row["jog"] == "admin") {
                                        $admin = "selected";
                                    } else {
                                        $guest = "selected";
                                    }
                                    echo '
                                    <tr>
                                    <td>' . $row["username"] . '</td>
                                    <td>' . $row["email"] . '</td>
                                    
                                    <form action="/api/updateUser.php" method="post">
                                    <td>
                                    <input type="hidden" name="userid" value="' . $row["id"] . '">
                                    <select name="jog" id="jog">
                                        <option value="guest" ' . $guest . '>guest</option>
                                        <option value="admin" ' . $admin . '>admin</option>
                                    </select>

                                    <td>
                                     <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i></button>
                                      <a class="btn btn-danger" href="/api/deleteUser.php?id=' . $row["id"] . '"><i class="fa fa-trash-o"></i></a></td>
                                </form>            
                                </tr>';
                                }

                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                  <!-- Megrendelések tábla -->
                    <!-- Megrendelések fejcím -->
                <div class="tab-pane col-12 fade" id="megrendelesek">
                    <div class="card" style="border-top-left-radius: 0;border-top-right-radius: 0;border-top-color: white;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Név</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Termékek</th>
                                    <th scope="col">Összeg</th>
                                    <th scope="col">Fizetési mód</th>
                                    <th scope="col">Cím</th>
                                    <th scope="col">Ország</th>
                                    <th scope="col">Megye</th>
                                    <th scope="col">Irányítószám</th>
                                    <th scope="col">Dátum</th>
                                </tr>
                            </thead>
                         <!-- Megrendelések törzs -->
                            <tbody>
                                <?php
                                //Az adatbázisból kiszedjük a megrendelések adatait. 
                                $result = $mysqli->query("SELECT * FROM `megrendelesek`");
                                while ($row = $result->fetch_assoc()) {
                                    $elemek = array();
                                    $kosar = $row["kosar"];
                                    $fizetes = $row["fizetesimod"];
                                    $price = json_decode($row["kosar"], true)["Total"];
                                    $items = json_decode($kosar, true);
                                    foreach ($items["items"] as $key => $value) {
                                        array_push($elemek, $value["name"]);
                                    }

                                    echo '
                                    <tr>
                                        <td>' . $row["nev"] . '</td>
                                        <td>' . $row["email"] . '</td>
                                        <td>' . implode(", ", $elemek) . '</td>
                                        <td class="text-nowrap">' . number_format($price, 0, " ", " ") . ' Ft</td>
                                        <td>' . $fizetes . '</td>
                                        <td>' . $row["cim"] . '</td>
                                        <td>' . $row["orszag"] . '</td>
                                        <td>' . $row["megye"] . '</td>
                                        <td>' . $row["iranyitoszam"] . '</td>
                                        <td>' . $row["datum"] . '</td>
                                
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

   <!-- Szervíz fejcím -->
                <div class="tab-pane fade" id="szerviz">
                    <div class="card" style="border-top-left-radius: 0;border-top-right-radius: 0;border-top-color: white;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Telefon</th>
                                    <th scope="col">Hiba</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Elérhetőség</th>
                                </tr>
                            </thead>

                                  <!-- Szervíz törzs -->
                            <tbody>
                                <?php

                                //Az adatbázisból kiszedjük a szervíz adatait. 
                                $result = $mysqli->query("SELECT `id`, `telefon`, `hiba`, `email`, `elerhetoseg` FROM `szerviz`");
                                while ($row = $result->fetch_assoc()) {
                                    echo '
                                        <tr>
                                            <td>' . $row["telefon"] . '</td>
                                            <td>' . $row["hiba"] . '</td>
                                            <td>' . $row["email"] . '</td>
                                            <td>' . $row["elerhetoseg"] . '</td>

                                        </tr>';
                                }
                                ?>

                            </tbody>
                        </table>
                    </div>
                </div>


  <!-- Termékek fejcím -->
                <div class="tab-pane fade" id="termekek">
                    <div class="card" style="border-top-left-radius: 0;border-top-right-radius: 0;border-top-color: white;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Név</th>
                                    <th scope="col">Márka</th>
                                    <th scope="col">Ár</th>
                                    <th scope="col">Leírás</th>
                                    <th scope="col">Műveletek</th>
                                </tr>
                            </thead>
                              <!-- Termékek törzs -->
                            <tbody>
                                <?php
                                //Az adatbázisból kiszedjük a termékek adatait. 
                                $result = $mysqli->query("SELECT `id`, `nev`, `marka`, `ar`, `leiras`, `kep`, `letrehozva` FROM `termekek`");
                                while ($row = $result->fetch_assoc()) {

                                    echo '
                                        <tr>
                                            <td>' . $row["nev"] . '</td>
                                            <td>' . $row["marka"] . '</td>
                                            <td>' . $row["ar"] . '</td>
                                            <td>' . $row["leiras"] . '</td>

                                            <td><a class="btn btn-primary" href="termekszerkeztes.php?id=' . $row["id"] . '"><i class="fa fa-edit"></i></a>  <a class="btn btn-danger" href="/api/deleteTermek.php?id=' . $row["id"] . '"><i class="fa fa-trash-o"></i></a></td>

                                        </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
  <!-- Termék hozzáadás gomb -->

                        <a class="btn btn-primary" href="termekhozzaadas.php">Termék hozzáadás</a>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/bootstrap.bundle.min.js"></script>
</body>
</html>