<?php
session_start();

$index = $_GET["index"];
unset($_SESSION["cart"]["items"][$index]);//Kiszedi a kosárból a terméket
$_SESSION["cart"]["items"] =json_decode((json_encode(array_values($_SESSION["cart"]["items"]),true)),true);
$_SESSION["cart"]["num_of_items"] = $_SESSION["cart"]["num_of_items"] -1;
$response = json_encode(array("Success" => true,));

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
header('Location: /kosar.php');
?>