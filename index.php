<?php
session_start();
putenv("LANG=es_AR.UTF-8");

echo 'access_token: ' . $_SESSION['access_token'];

require 'assets/meli-php-sdk/Meli/meli.php';
require 'includes/config.php';

$meli = new Meli($APP_ID, $SECRET_KEY);

if($_GET['code'] || $_SESSION['access_token']) {

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Información bibliográfica</title>

    <!-- Jquery -->
    <script src="assets/jquery/jquery.min.js"></script>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-theme.min.css" />

    <script src="assets/bootstrap/js/bootstrap.min.js"></script>

  </head>


  <body>
    <div class="container">

    <form class="form-horizontal" action="subir.php" method="post" enctype="multipart/form-data">
      <fieldset>

        <!-- Form Name -->
        <legend>Elige el archivo a subir</legend>

        <!-- Text input-->
        <div class="form-group">
          <label class="col-md-4 control-label" for="archivo">Archivo</label>
          <div class="col-md-4">
            <input type="file" name="fileToUpload" id="fileToUpload" class="form-control input-md" value="">
          </div>
        </div>

        <div class="form-group">
          <label class="col-md-4 control-label" for="submit"></label>
          <div class="col-md-4">
            <button type="submit" class="btn btn-default">Subir archivo</button>
          </div>
        </div>


      </fieldset>

    </form>
  </div>

  </body>
</html>

<?php
} else {

	echo '<a href="' . $meli->getAuthUrl('https://'.$DOMAIN.'/login.php', Meli::$AUTH_URL['MLA']) . '">Login using MercadoLibre oAuth 2.0</a>';
}

?>
