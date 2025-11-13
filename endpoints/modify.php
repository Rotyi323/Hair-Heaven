<?php
session_start();
include("../konfiguracio.php");

$success = false;
$username = $_POST["username"] ? $_POST["username"] : $_SESSION["nev"]; // Ha a felhasználó nem ad meg nevet akkor azt használja ami bejelentkezéskor el lett tárolva
$pw = $_POST["pw"] ? $_POST["pw"] : ""; // Ha nem lett megadva jelszó akkor üresen lesz beküldve
$email = $_POST["email"];
$error = "";
$id = $_SESSION["id"];

if($_SESSION["email"] == $email){ // Ha a megadott email ugyan az amivel be van lépve akkor ez fut le (nem kell módosítani az emailt)
    if($pw == ""){ // ha a jelszó is üres akkor nem fogja módosítani a jelszót csak a felhasználónevet
        $edit = $mysqli->prepare("UPDATE users SET username = ? WHERE id = ?");
        $edit->bind_param("ss", $username, $id);
        $edit->execute();
        $success = true;
        $_SESSION["nev"] = $username;

    }else{ // Ha a jelszó meg van adva akkor hash-eli a jelszót és azt is frissíti
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $edit = $mysqli->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $edit->bind_param("sss", $username, $hash, $id );
        $edit->execute();
        $success = true;
        $_SESSION["nev"] = $username;

    }


}else{ // Ha változott az email is akkor megnézi, hogy foglalt-e az email
    $check = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
    $check -> bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if($result->num_rows == 0){ // Ha nem foglalt és nem változott a jelszó
        if($pw == ""){
            $edit = $mysqli->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $edit->bind_param("sss", $username, $email, $id);
            $edit->execute();
            $success = true;
            $_SESSION["nev"] = $username;
            $_SESSION["email"] = $email;

        }else{ // Ha nem foglalt és változott a jelszó
            $edit = $mysqli->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $edit ->bind_param("ssss", $username, $email, $id, $hash);
            $edit->execute();
            $success = true;
            $_SESSION["nev"] = $username;
            $_SESSION["email"] = $email;

        }
    }else{
        $success = false;
        $error = "Az email foglalt";
    }

}

$res = array("success"=>$success, "error"=>$error);
header('Content-type: application/json');
echo json_encode($res);
?>