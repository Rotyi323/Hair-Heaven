<?php
include("../konfiguracio.php");
session_start();

$password = $_POST["password"];
$email = $_POST["email"];

if (!empty($password) && !empty($email)) {
//Lekéri az emailcímhez tartozó adatokat, és meghívjuk a password_verify függvényt.
$check = $mysqli->query("SELECT * FROM users WHERE email = '".$email."' LIMIT 1");
if($check->num_rows > 0){
    while ($row = $check->fetch_assoc()) {
        if(password_verify($password,$row["password"])){
            //A sikeres belépést követően az adatokat eltároljuk a session ben.
            $_SESSION["belepve"] = true;
            $_SESSION["id"] = $row["id"];
            $_SESSION["nev"] = $row["username"];
            $_SESSION["email"] = $row["email"];
            $_SESSION["jog"] = $row["jog"];

            header("Location: /");

        }else{
            echo "Hibás email cím vagy jelszó!";
        }
    }

}else{
    echo "Nem létezik ilyen felhasználó";
}
}else{
    echo "Az összes mező kitöltése kötelező!";
}
?>