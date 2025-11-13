<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Ikon-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Bootstrap & CSS -->
  <link rel="stylesheet" href="bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Hiba</title>
  <style>
    .main {
      max-width: 50rem;
      text-align: center;
      background-color:#f5404f;
      border: 0.4rem solid black;
      padding: 1.2rem;
      margin: 10px;
      border-radius: 16px;
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

  <div class="main col-6 mx-auto">
    <h3>A hibajegy leadása sikertelen!</h3>
    <h4>Kérjük ellenőrizze, hogy az adatok helyesek, és minden esetben ki vannak-e töltve!</h4>

    <script src="/bootstrap.bundle.min.js"></script>
</body>
</html>