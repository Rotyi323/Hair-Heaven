<?php
session_start();
include("../konfiguracio.php");

if ($_SESSION["jog"] == "admin") { //Leellenőrzi hogy admin-e

$kep = $_POST["image"];
$nev = $_POST["nev"];
$marka = $_POST["marka"];
$ar = $_POST["ar"];
$leiras = $_POST["leiras"];


if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {

    // Beállítja a feltöltések mappát, hogy oda menjenek fel a termékek képei
    $grandparent_dir = dirname(dirname(__FILE__));
    $target_dir = $grandparent_dir . "/feltoltesek/";
    $file_name = $_FILES["image"]["name"];
    $target_file = $target_dir . $file_name;
    $target_file2 = "/feltoltesek/" . $file_name;

    // Elmenti a képet

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "A  ". htmlspecialchars(basename($_FILES["image"]["name"])) . " kép felkerült a szerverre.";
        
        // Eltárolja a kép elérési útvonalát az adatbázisban
        $stmt = $mysqli->prepare("INSERT INTO `termekek`(`nev`, `marka`, `ar`, `leiras`, `kep`) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $nev,$marka,$ar,$leiras,$target_file2);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($stmt->affected_rows > 0) {
            header("Location: /admin/");
        }

    } else {
        $error_message = error_get_last()["message"];
        echo "Hiba a fájl feltöltésekor: " . $error_message;
    }
} else {
    echo "Válassza ki a feltölteni kívánt képet.";
}

print_r($stmt);
$stmt->close();
}
?>