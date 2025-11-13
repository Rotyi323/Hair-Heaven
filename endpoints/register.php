<?php
include("../konfiguracio.php");
session_start();

$username = $_POST["username"];
$password = $_POST["password"];
$email = $_POST["email"];

//A jelszót a password_hash fügvénnyel titkosítjuk
$encoded_password = password_hash($password, PASSWORD_DEFAULT);

if(strlen($password) >! 255 &&  (!(strlen($password) < 8)))
{
if (!empty($username) && !empty($password) && !empty($email)) {

    $check = $mysqli->query("SELECT * FROM users WHERE email = '" . $email . "'");
    if ($check->num_rows == 0) {
        //Ha nem létezik még ilyen felhasználó ezzel az email címmel akkor eltároljuk
        $insert = $mysqli->query("INSERT INTO `users`(`username`, `password`, `email`) VALUES ('" . $username . "','" . $encoded_password . "','" . $email . "')");
        header("Location: /belepes.php");

    } else {
        echo "Már létezik ilyen felhasználó";
    }
}else{
    echo "Minden adat megadása kötelező!";
    } //Ha nem teljesül valamelyik feltétel akkor hibát írunk
}
else{
    echo "A jelszó minimum 8 maximum, 255 karaktert kell tartalmazzon!";
}
?>