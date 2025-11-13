<?php
session_start();
include("../konfiguracio.php");
$name = $_POST["name"];
$quantity = $_POST["quantity"];

$endprice = 0;
$baseprice = 0;
$id = "";
//Abban az esetben ha a kosár már nem üres, akkor frissítjük a tartalmát és a mennyiségeket.
//Ellenkező esetben az alapértelmezett adatokkal feltöltjük a szükséges mezőket.
if (!empty($_SESSION["cart"]["items"])) {
    $_SESSION["cart"]["items"] = json_decode((json_encode(array_values($_SESSION["cart"]["items"]), true)), true);
    $total = 0;
    for ($i = 0; $i < $_SESSION["cart"]["num_of_items"]; $i++) {
        $total += $_SESSION["cart"]["items"][$i]["Item_Total"];
    }
    $_SESSION["cart"]["Total"] = $total;
   
} else {
    $_SESSION["cart"]["num_of_items"] = 0;
    $_SESSION["cart"]["Total"] = 0;
  
}

//Lekérjük a Termék nevéhez tartozó adatokat
        $stmt = $mysqli->prepare("SELECT * FROM termekek WHERE nev = ? LIMIT 1");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $arr = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $arr = $arr[0];
        $stmt->close();

        $defprice = $arr["ar"];
        $endprice += $defprice;
        $baseprice = $defprice;
        $id = $arr["id"];
       
if ($_SESSION["cart"]["num_of_items"] == 0) {
    //Abban az esetben ha a kosárban még nem található termék, a 0.dik elemet állítjuk be.
    $_SESSION["cart"]["Total"] = ($endprice * intval($quantity));
    $_SESSION["cart"]["num_of_items"] = 1;
    $_SESSION["cart"]["items"]["0"]["name"] = $name;

  
    $_SESSION["cart"]["items"]["0"]["quantity"] = intval($quantity);
    $_SESSION["cart"]["items"]["0"]["id"] = $id;
    $_SESSION["cart"]["items"]["0"]["Item_Total"] = ($endprice * intval($quantity));
    $_SESSION["cart"]["items"]["0"]["Item_Price_default"] = $baseprice;
} else {
    $changed = false;
    //Abban az esetben ha a kosárban már található termék, kiszámoljuk hogy hanyadik a következő elem és feltöltjük a szükséges adatokkal, illetve frissítjük a végösszeget.
    for ($x = 0; $x < $_SESSION["cart"]["num_of_items"]; $x++) {
        if ($_SESSION["cart"]["items"][$x]["name"] == $name && $_SESSION["cart"]["items"][$x]["id"] == $id) {
            //Itt azt ellenőrizzük hogy az adott termék már szerepel-e az adatbázisban, és ha igen akkor a mennyiségét növeljük.
        
                $_SESSION["cart"]["items"][$x]["quantity"] = intval($_SESSION["cart"]["items"][$x]["quantity"]) + intval($quantity);
                $_SESSION["cart"]["items"][$x]["Item_Total"] = (intval($_SESSION["cart"]["items"][$x]["Item_Price_default"]) * intval($_SESSION["cart"]["items"][$x]["quantity"]));
                $changed = true;
        }
    }
    if ($changed == false) {
        //Itt ha nem tartalmaz ilyen terméket a kosár, hozzáadjuk.
        $i = $_SESSION["cart"]["num_of_items"] + 1;
        $_SESSION["cart"]["Total"] = $_SESSION["cart"]["Total"] + ($endprice * intval($quantity)) + ($box * intval($quantity));
        $_SESSION["cart"]["num_of_items"] = $i;
        $_SESSION["cart"]["items"][$i]["name"] = $name;
       
     $_SESSION["cart"]["items"][$i]["quantity"] = intval($quantity);
        
        $_SESSION["cart"]["items"][$i]["id"] = $id;
        $_SESSION["cart"]["items"][$i]["Item_Total"] = ($endprice * intval($quantity)) + ($box * intval($quantity));
        $_SESSION["cart"]["items"][$i]["Item_Price_default"] = $baseprice;
    }
}

if (!empty($_SESSION["cart"]["items"])) {
    //Újra végigfuttatjuk a kosár frissítő kódot.
    $_SESSION["cart"]["items"] = json_decode((json_encode(array_values($_SESSION["cart"]["items"]), true)), true);
    $total = 0;
    for ($i = 0; $i < $_SESSION["cart"]["num_of_items"]; $i++) {
        $total += $_SESSION["cart"]["items"][$i]["Item_Total"];
    }
    $_SESSION["cart"]["Total"] = $total;
    
} else {
    $_SESSION["cart"]["num_of_items"] = 0;
    $_SESSION["cart"]["Total"] = 0;
}

header('Content-Type: application/json');
//Végül kiiratjuk JSON formátumban a kosarat
echo json_encode($_SESSION["cart"]);
?>