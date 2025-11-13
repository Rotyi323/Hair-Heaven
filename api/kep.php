<?php
session_start();
include("../konfiguracio.php");
$id = $_GET["id"];

// A termékek képei közül kiszedi a 3 legfrissebb képet az adatbázisból (Főoldal)
$stmt = $mysqli->prepare("SELECT `kep` FROM `termekek` WHERE `id` = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($stmt->affected_rows > 0) {
     while ($row = $result->fetch_assoc()) {
       if (!empty($row['kep'])) {
        header( "Location: ".$row["kep"]);

        }
    }
}
$stmt->close();
?>