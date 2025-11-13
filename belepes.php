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

  <title>Bejelentkezés</title>
  <style>
      input {
      margin: 0.7rem;
      text-align: center;
      margin-left: -0.15rem;
    }
    form {
      margin-top: 80px;
    }
    .belep {
      width: 100%;
      max-width: 330px;
      padding: 15px;
      margin: auto;
    }

    #Cim {
      font-size: 2.1rem;
      padding-bottom: 0.65rem;
      padding-top: 1rem;
    }

    label {
      text-align: center;
    }
    h5{
      margin:0.8rem;
      font-size: 1.25rem;
    }
  </style>
</head>


<body class="text-center">
<?php include("navbar.php"); ?>

<!--Form kezdet-->
  <main class="belep">
    <form action="/endpoints/login.php" method="POST">

      <h2 class="h3 mb-3 fw-normal" id="Cim"><b>Jelentkezzen be!</b></h2>
      <!-- Email -->
      <div class="form-floating">
        <input type="email" class="form-control" id="email" name="email" placeholder="név@példa.com" required>
        <label for="email">Email cím</label>
      </div>
      <!-- Jelszó -->
      <div class="form-floating">
        <input type="password" class="form-control" id="Jelszó" name="password" placeholder="Jelszó" required>
        <label for="Jelszó">Jelszó</label>
      </div>
      
      <button class="w-100 btn btn-lg btn-primary text-center" type="submit"><b>Bejelentkezés</b></button>

      
      <i>
        <h5>Még nincs fiókja? <a href="regisztracio.php">Regisztráció</a></h5>
      </i>

      <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">© 2023 TelefonFix</p>
      </footer>
    </form>
  </main>

</body>
</html>