<!DOCTYPE html>
<html lang="hu">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Ikon-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Bootstrap & CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

  <title>Információ</title>

  <style>
    h2 {
      margin-top: 30px;
      text-align: center;
      font-size: 40px;
      text-decoration: underline;
      padding: 20px;
    }
    p {
      text-align: center;
      font-size: 25px;
    }
    table,
    tr,
    td {
      text-align: center;
      vertical-align: center;
      width: 100%;
      max-width: 25rem;
      margin: 0 auto;
      border: 4px solid black;
      font-size: 1.2rem;
      background-color: whitesmoke;
      border-radius: 16px;
    }
    .main {
      max-width: 50rem;
      text-align: center;
      background-color: whitesmoke;
      border: 0.4rem solid black;
      padding: 1.2rem;
      margin: 10px;
      border-radius: 16px;
    }
    .main p {
      font-family: Arial, sans-serif;
      font-size: 1.1rem;
      color: black;
      line-height: 1.3;
      text-align: center;
      background-color: #dedcd9;
      border-radius: 16px;
      padding: 1rem;
      font-weight: 600;
    }
    h3 {
      padding-bottom: 1rem;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <?php include("navbar.php");
  error_reporting(E_ERROR | E_PARSE); 
  ?>

  <b><h2>Információ</h2></b>
<!-- Keret kezdet -->
  <div class="main col-6 mx-auto">
    <h3>Üdvözöljük a <i><a style="color:black;" href="index.php">TelefonFix</a></i> weboldalán! </h3>
    <p>
      Célunk, hogy minőségi termékeket és kiváló szolgáltatást nyújtsunk
      ügyfeleinknek, legyenek azok akár magánszemélyek vagy vállalkozások.
    </p>
    <p>
      A TelefonFix azon kevés telefonboltok közé tartozik, amelyek olyan innovatív megoldásokkal és technológiákkal
      rendelkeznek, amelyek lehetővé teszik, hogy ügyfeleink mindig a legújabb és legjobb készülékeket használják.
    </p>
    <p>
      Csapatunk tagjai nagy tapasztalattal rendelkeznek a mobiltelefonok és kiegészítőik területén, és mindig készen
      állnak arra, hogy segítsenek Önnek a legjobb választás megtalálásában.
    </p>
    <p>
      Emellett, a cégünk fontosnak tartja, hogy környezettudatos vállalkozásként működjön. Ennek érdekében,
      több programot is elindítottunk a különböző elektromos készülékek és eszközök újrahasznosítására.
    </p>
    <p>
      Ha kérdése van, vagy csak egyszerűen tanácsra van szüksége a telefonok vagy kiegészítők terén, ne habozzon
      kapcsolatba lépni velünk. Örömmel segítünk!
    </p>
    <p> <u>Köszönjük, hogy meglátogatta weboldalunkat.</u></p>
  </div>
<!-- Keret vége -->

  <h2 style="margin-bottom: 15px; font-size: 30px; font-weight: bold;"><a id="elerheto">Elérhetőségeink:</a></h2>
<!-- Tábla kezdet -->
  <div class="elerheto" style="text-align: center;">
    <table>
      <tr>
        <td>
          <p><i class="fa fa-phone" style="font-size:24px;color:rgb(94, 255, 0)"></i> <b> Telefon: </b>
        </td>
        <td><a href="#" style="font-weight: bold; color:black;"> <u><i> +36 50 123 4567 </i></u></a></p>
        </td>
      </tr>
      <tr>
        <td>
          <p><i class="fa fa-envelope" style="font-size:24px;color:rgba(0, 195, 255, 0.705)"></i><b> E-mail: </b>
        </td>
        <td><a href="#" style="font-weight: bold; color:black;"> <u><i>telefonfix.doga@gmail.com </i></u></a></p>
        </td>
      </tr>
      <tr>
        <td>
          <p><i class="fa fa-map-marker" style="font-size:24px;color:#f70707d2"></i><b> Cím: </b>
        </td>
        <td><a href="#" style="font-weight: bold; color:black;"><u><i> Békéscsaba, Bartók Béla út 4. </i></u></a></p>
        </td>
      </tr>
    </table>
  </div>
<!-- Tábla vége -->

  <footer class="my-5 pt-5 text-muted text-center text-small">
    <p class="mb-1">© 2023 TelefonFix</p>

  </footer>
  <script src="bootstrap.bundle.min.js"></script>

</body>
</html>