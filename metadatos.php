<?php
error_reporting(E_ALL & ~E_NOTICE);

session_start();
putenv("LANG=es_AR.UTF-8");
if($_GET['file']) {
  // echo "El archivo a editar es {$_GET['file']}";

  // Usamos ebook-meta para obtener los metadatos, luego los cargamos en un array llamado $metadatos
  $metadata = shell_exec('ebook-meta ' . $_GET['file']);
  if ($a = explode("\n", $metadata)) { // create parts
    foreach ($a as $s) { // each part
      if ($s) {
        if ($pos = strpos($s, ':')) { // key/value delimiter
          $ka[trim(substr($s, 0, $pos))] = trim(substr($s, $pos + strlen(':')));
        } else { // key delimiter not found
          $ka[] = trim($s);
        }
      }
    }
    $metadatos = $ka;
  }
  $metadata = shell_exec('ebook-meta --get-cover=files/cover.jpg ' . $_GET['file']);


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
    <!-- Bootstrap-Select -->
    <link rel="stylesheet" href="assets/bootstrap-select/css/bootstrap-select.min.css" />
    <script src="assets/bootstrap-select/js/bootstrap-select.min.js"></script>

    <script>
    function adivinar() {
      var titulo = encodeURIComponent($( "#titulo" ).val());
      var autor = encodeURIComponent($( "#autor" ).val());

      $.getJSON(
         "https://api.mercadolibre.com/sites/MLA/category_predictor/predict?title=" + titulo + " " + autor, function( data ) {
           console.log(data['name']);
           // Actualiza el valor
           $('#cat').selectpicker('val', data['name']);
           var categoria_elegida = $(".bootstrap-select .filter-option .text-muted").html();

      });
    }


    // adivinar();

    </script>

  </head>

  <body>
    <div class="container">
      <form class="form-horizontal">
        <fieldset>

          <!-- Form Name -->
          <legend>Cargar un nuevo libro</legend>

          <!-- Text input-->
          <div class="form-group">
            <label class="col-md-4 control-label" for="titulo">Titulo</label>
            <div class="col-md-4">
              <input id="titulo" name="titulo" type="text" placeholder="Titulo del libro" class="form-control input-md" required="" value="<?php if ($metadatos['Title']) { echo $metadatos['Title']; } ?>">


            </div>
          </div>

          <!-- Text input-->
          <div class="form-group">
            <label class="col-md-4 control-label" for="editorial">Editorial</label>
            <div class="col-md-4">
              <input id="editorial" name="editorial" type="text" placeholder="La Editorial" class="form-control input-md" required="" value="<?php if ($metadatos['Publisher']) { echo $metadatos['Publisher']; } ?>">

            </div>
          </div>

          <!-- Text input-->
          <div class="form-group">
            <label class="col-md-4 control-label" for="autor">Autor</label>
            <div class="col-md-4">
              <input id="autor" name="autor" type="text" placeholder="Apellido, Nombres" class="form-control input-md" required="" value="<?php if ($metadatos['Author(s)']) { echo $metadatos['Author(s)']; } ?>">
            </div>
          </div>

          <!-- Text input-->
          <div class="form-group">
            <label class="col-md-4 control-label" for="ano">Año</label>
            <div class="col-md-4">
              <input id="ano" name="ano" type="text" placeholder="El año de edición" class="form-control input-md" required="" value="<?php if ($metadatos['Published']) { $date = explode('-',$metadatos['Published']); echo $date[0]; } ?>">
              <span class="help-block"> </span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-4 control-label" for="cat">Categoria</label>
            <div class="col-md-4">
              <select id="cat" class="selectpicker" data-show-subtext="true" data-live-search="true">
                <?php
                include('includes/categorias.php');
                ?>
              </select>
              <button onClick="adivinar();" type="button" class="btn btn-default">
                <span class="glyphicon glyphicon-star" aria-hidden="true"></span> Adivinar
              </button>

            </div>
          </div>

          <div class="form-group">
            <label class="col-md-4 control-label" for="cat">Portada</label>
            <div class="col-md-4">
              <img src="files/cover.jpg" class="img-thumbnail rounded float-left" alt="..." />
            </div>
          </div>



          <hr />


          <div class="form-group">
            <label class="col-md-4 control-label" for="submit"></label>
            <div class="col-md-4">
              <button type="submit" class="btn btn-default">Cargar datos bibliográficos</button>
            </div>
          </div>


        </fieldset>


      </form>


    </div> <!-- FIN:continaer -->
  </body>

</html>



<?php
} else {
  echo "No se indicó el archivo a editar";
}
?>
