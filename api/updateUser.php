<?php
session_start(); 
include("../konfiguracio.php");
if($_SESSION["jog"] == "admin"){ //Jog módosítása
    $jog = $_POST["jog"];
    $id = $_POST["userid"];
    $update = $mysqli->prepare("UPDATE users SET jog = ? WHERE id = ?");
    $update->bind_param("ss", $jog, $id);
    $update->execute();
    if($_SESSION["id"] == $id){
        $_SESSION["jog"] = $jog;
    }
    header("Location: /admin");
}
?>