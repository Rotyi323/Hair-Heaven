<?php include("konfiguracio.php");
session_start();
error_reporting(E_ERROR | E_PARSE);
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

    <title>Rendeléseim</title>
</head>

<body>
    <?php include("navbar.php"); ?>

<!-- Táblázat kezdet -->
    <div class="main col-6 mx-auto">
        <div class="m-4">
            <div class="card">
                <table class="table table-striped">
                    <!-- Táblázat fejléc -->
                    <thead>
                        <tr>
                            <th scope="col">Rendelés azonosító</th>
                            <th scope="col">Termékek</th>
                            <th scope="col">Összeg</th>
                            <th scope="col">Fizetési mód</th>
                            <th scope="col">Cím</th>
                        </tr>
                    </thead>
                    <tbody>
 <!-- Táblázat törzs -->
                        <?php
                        // A felhasználó emailcíméhez tartozó összes létező rendelést ki iratjuk, a tartalommal együtt.
                        $result = $mysqli->query("SELECT * FROM `megrendelesek` WHERE email = '" . $_SESSION["email"] . "'");
                        while ($row = $result->fetch_assoc()) {

                            $elemek = array();
                            $id = $row["id"];
                            $cim = $row["cim"];
                            $kosar = $row["kosar"];
                            $fizetes = $row["fizetesimod"];
                            $price = json_decode($row["kosar"], true)["Total"];
                            $items = json_decode($kosar, true);

                            foreach ($items["items"] as $key => $value) {
                                array_push($elemek, $value["name"]);
                            }

                            echo '
                            <tr>
                                <td>' . $id . '</td>
                                <td>' . implode(", ", $elemek) . '</td>
                                <td>' . number_format($price, 0, " ", " ") . ' Ft</td>
                                <td>' . $fizetes . '</td>
                                <td>' . $cim . '</td>
                            </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="/bootstrap.bundle.min.js"></script>
</body>
</html>