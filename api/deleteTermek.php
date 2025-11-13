<?php
session_start();
include("../konfiguracio.php");

if ($_SESSION["jog"] == "admin") {

    $id = $_GET["id"];
    $stmt = $mysqli->prepare("DELETE FROM `termekek` WHERE `id` = ?"); //Kitörli az adott terméket az adatbázisból
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($stmt->affected_rows > 0) {
        header("Location: /admin/");
    }
    print_r($stmt);

} else {
    echo "Nincsenek termékek";
}
?>