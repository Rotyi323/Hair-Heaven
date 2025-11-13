<?php include("../konfiguracio.php");
session_start();

$telefon = $_POST["telefon"];
$hiba = $_POST["hiba"];
$email = $_POST["email"];
$elerhetoseg = $_POST["elerhetoseg"];

//Leellenőrzi, hogy a karakterszám nem túl nagy-e az email és elérhetőség mezőben.
//Leellenőrzi, hogy  az adatok megvannak-e adva, ha igen feltölti adatbázisba ha nem akkor hibát ír.
if(strlen($email) >! 100 && strlen($elerhetoseg) >!30){
if(!empty($telefon) && !empty($hiba) && !empty($email) && !empty($elerhetoseg)){

    $stmt2 = $mysqli->prepare("INSERT INTO `szerviz`(`telefon`, `hiba`, `email`, `elerhetoseg`) VALUES (?,?,?,?)");
    $stmt2->bind_param("ssss", $telefon, $hiba, $email, $elerhetoseg);
    $stmt2->execute();

    header("Location: /siker.php");

}else{
    header("Location: /Hiba.php");
}
}
else{
    header("Location: /Hiba.php");
}
?>

