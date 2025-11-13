<?php
session_start();
include("../konfiguracio.php");
if ($_SESSION["jog"] == "admin") { //Leellenőrzi, hogy admin-e a felhasználó

$kep = $_POST["kep"];
$id = $_POST["id"];
$nev = $_POST["nev"];
$marka = $_POST["marka"];
$ar = $_POST["ar"];
$leiras = $_POST["leiras"];

if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
    // Beállítjuk a kép elérési helyét
    $grandparent_dir = dirname(dirname(__FILE__));
    $target_dir = $grandparent_dir . "/feltoltesek/";
    $file_name = $_FILES["image"]["name"];
    $target_file = $target_dir . $file_name;
    $target_file2 = "/feltoltesek/" . $file_name;

    // Elmentjük a képet a szerverre
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "A ". htmlspecialchars(basename($_FILES["image"]["name"])) . "nevű fájl fel lett töltve.";
        // Eltároljuk a kép elérési útvonalát az adatbázisban

        $stmt = $mysqli->prepare("UPDATE `termekek` SET `nev`=?,`marka`=?,`ar`=?,`leiras`=?,`kep`=? WHERE `id` = ?");
        $stmt->bind_param("ssssss", $nev,$marka,$ar,$leiras,$target_file2,$id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($stmt->affected_rows > 0) {
            header("Location: /admin/");
        }

    } else {// Ha hiba van kiírjuk
        $error_message = error_get_last()["message"];
        echo "Hiba a kép feltöltésekor: " . $error_message;

    }
} else {
    echo "Válassz egy képet!";
}
print_r($stmt);
$stmt->close();
}
?>