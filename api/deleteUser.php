<?php 
session_start();
include("../konfiguracio.php");
if($_SESSION["jog"] == "admin"){
    $id = $_GET["id"];
    $delete = $mysqli->prepare("DELETE FROM users WHERE id = ?"); //előkészíti az sql-t ahol az id = ?
    $delete->bind_param("s", $id); 
    $delete->execute(); // Kitörli a felhasználót
    header("Location: /admin.php");
}
?>