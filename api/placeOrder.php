<?php
session_start();
include("../konfiguracio.php");

$name = $_POST["nev"];
$email = $_POST["email"];
$cim = $_POST["cim"];
$orszag = $_POST["orszag"];
$megye = $_POST["megye"];
$irszam = $_POST["irszam"];
$fizetesiMod = $_POST["paymentMethod"];
$error = "";

$cart = json_encode($_SESSION["cart"], true); // Session-ből kiszedi a kosarat amit átalakít egy json-re
if($_SESSION["cart"]["num_of_items"] > 0){ //Ellenőrizzük, hogy a kosár nem-e üres

if($name == "" || $email == "" || $cim == "" || $orszag == "" || $megye == "" || $irszam == ""){ //Ellenőrzi, hogy a mezők nem üresek-e
    $success = false;
    $error = "Minden mező megadása kötelező!"; //Ha valami üres akkor kiírja hogy minden mező kötelező
    
}else{ //Előkészíti, az adatbázisba való feltöltést
    $order = $mysqli->prepare("INSERT INTO megrendelesek (kosar, nev, email, cim, orszag, megye, iranyitoszam, fizetesimod) VALUES (?,?,?,?,?,?,?,?)");
    $order->bind_param("ssssssss", $cart, $name, $email, $cim, $orszag, $megye, $irszam, $fizetesiMod);
    $order->execute();
    if($order->affected_rows > 0){
        $success = true;
        unset($_SESSION["cart"]);
    }else{
        $success = false;
        $error = "Váratlan hiba történt!";
    }
}

}else{
    $success = false; //Ha a kosár üres akkor hibát ír
    $error = "A kosár nem lehet üres!";
}

$res = array("success"=>$success, "error"=>$error);
header('Content-type: application/json');
echo json_encode($res);
?>