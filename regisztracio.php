<?php 
session_start();    
error_reporting(E_ERROR | E_PARSE);
?>

<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Ikon-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Bootstrap & CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        
  <title>Regisztráció</title>
  <style>
    .belep {
      width: 100%;
      max-width: 330px;
      padding: 15px;
      margin: auto;
    }
    .belep input[type="email"] {
      margin-bottom: -1px;
      border-bottom-right-radius: 0;
      border-bottom-left-radius: 0;
    }

    .belep input[type="password"] {
      margin-bottom: 10px;
      border-top-left-radius: 0;
      border-top-right-radius: 0;
    }
    #Cim {
      font-size: 2.1rem;
      padding-bottom: 1rem;
      padding-top: 2.6rem;
    }
    input {
      margin: 0.7rem;
      text-align: center;
      margin-left: -0.15rem;
    }
    h5{
      margin:0.8rem;
      font-size: 1.25rem;
    }
  </style>
</head>

<body  class="text-center">
  <?php include("navbar.php"); 
  ?>

  <!-- Form kezdet -->
  <main class="belep">
    <form action="/endpoints/register.php" method="POST">

      <h2 class="h3 mb-3 fw-normal" id="Cim"><b>Regisztráljon oldalunkra!</b></h2>
<!-- Felhasználónév -->
      <div class="form-floating">
        <input type="username" class="form-control" id="Felhasználónév" name="username" placeholder="Felhasználónév" maxlength="100" required>
        <label for="Felhasználónév">Felhasználónév</label>
      </div>
<!-- Email -->
      <div class="form-floating">
        <input type="email" class="form-control" id="email" name="email" placeholder="név@példa.com" maxlength="100" required>
        <label for="email">Email cím</label>
      </div>
<!-- Jelszó -->
      <div class="form-floating">
        <input type="password" class="form-control" id="Jelszó" name="password" placeholder="Jelszó" maxlength="255" minlength="8" required>
        <label for="Jelszó">Jelszó</label>
      </div>
<!-- Jelszó ismét -->
      <div class="form-floating">
        <input type="password" class="form-control" id="Jelszó ismét" name="password_again" placeholder="Jelszó ismét" maxlength="255" required>
        <label for="Jelszó ismét">Jelszó ismét</label>
      </div>

      <button class="w-100 btn btn-lg btn-primary" type="submit" style="text-align: center;"><b>Regisztrálok!</b></button>

        <i><h5>Van már fiókja? <a href="belepes.php">Belépés</a></h5> </i>
       
      <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">© 2023 TelefonFix</p>
      </footer>
    </form>
  </main>
</body>
</html>