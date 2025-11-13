<?php
session_start();
header('Content-Type: application/json');

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
echo json_encode(($_SESSION["cart"]));
?>