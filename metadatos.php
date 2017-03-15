<?php
error_reporting(E_ALL & ~E_NOTICE);

//include('assets/simple_html_dom/simple_html_dom.php');

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
  $metadata = shell_exec('ebook-meta --get-cover=tmp/cover.jpg ' . $_GET['file']);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Información bibliográfica</title>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap-theme.min.css" />
    <link rel="stylesheet" href="assets/datatables/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="assets/bootstrap-select/css/bootstrap-select.min.css" />

    <!-- JavaScript -->
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/datatables/js/jquery.dataTables.min.js"></script>
    <script src="assets/datatables/js/dataTables.bootstrap.min.js"></script>
    <script src="assets/bootstrap-select/js/bootstrap-select.min.js"></script>

    <style>
      #portadas img {
        width: 49%;
        box-sizing: border-box;
        cursor: pointer;
      }
    </style>

    <script>
      function adivinar() {
        var titulo = encodeURIComponent($( "#titulo" ).val());
        var autor = encodeURIComponent($( "#autor_nombre" ).val()) + ' ' + encodeURIComponent($( "#autor_apellido" ).val()) ;

        $.getJSON(
           "https://api.mercadolibre.com/sites/MLA/category_predictor/predict?title=" + titulo + " " + autor, function( data ) {
             console.log(data['name']);
             // Actualiza el valor
             $('#cat').selectpicker('val', data['name']);
             var categoria_elegida = $(".bootstrap-select .filter-option .text-muted").html();
             $('#categoria').val(categoria_elegida);

        });
      }

      function portadas() {
        var titulo = encodeURIComponent($( "#titulo" ).val());
        var autor = encodeURIComponent($( "#autor_nombre" ).val()) + ' ' + encodeURIComponent($( "#autor_apellido" ).val()) ;


        $.getJSON(
           "https://www.googleapis.com/customsearch/v1?key=AIzaSyBQ7ia4EDNm7UZ2KoZ9fJavcIik7BKctps&cx=001091051484625242059:2flawceqnem&fields=items/pagemap/cse_image&q=portada " + titulo + " " + autor, function( data ) {
            array = data['items'];
            array.forEach(function(item) {
              var image_url = item['pagemap']['cse_image']['0']['src'];
              $( "#portadas" ).append( '<img src="' + image_url + '" class="img-thumbnail rounded float-left" alt="..." />' );
            });

        });
      }

      function precios() {
        var titulo = encodeURIComponent($( "#titulo" ).val());
        var autor = encodeURIComponent($( "#autor_nombre" ).val()) + ' ' + encodeURIComponent($( "#autor_apellido" ).val()) ;

        $.getJSON(
           "https://api.mercadolibre.com/sites/MLA/search?q=Libro Digital " + titulo + " " + autor, function( data ) {
            //  console.log(data);
             array = data['results'];
             var total = 0;
             $( "#listado" ).empty();

             array.forEach(function(item) {
               console.log(item['price']);

               $( "#listado" ).append( '<tr> <th scope="row"><img src="'+ item['thumbnail']+ '" style="width:64px;" class="img-thumbnail rounded float-left" alt="..." /></th> <td><a href="'+ item['permalink'] +'" target="_blank">'+ item['title'] +'</a></td> <td>'+ item['price'] +'</td> </tr>' );
               total = total + item['price'];

             });


             if ( $.fn.dataTable.isDataTable( '#tabla_precios' ) ) {
              table.ajax.reload();
             } else {
               table = $('#tabla_precios').DataTable( {
                   "order": [[ 2, "asc" ]]
               } );
             }



             var promedio = total / array.length;
             $('#precio_promedio').html(Math.round(promedio));
             $('#precio').val(Math.round(promedio));


           });

      }


      $(document).on('click', '#portadas img' , function() {
        $('#portada_url').val($(this).attr('src'));
        $('#portadas img').css('border','1px solid #ddd') // reset style
        $(this).css('border','3px solid red')
      });
    </script>

  </head>

  <body>
    <div class="container">
      <form action="publicar.php" method="post" enctype="multipart/form-data" class="form-horizontal">
        <fieldset>

          <!-- Form Name -->
          <legend>Cargar un nuevo libro</legend>

          <!-- Text input-->
          <div class="hide form-group">
            <label class="col-md-4 control-label" for="archivo">Archivo</label>
            <div class="col-md-4">
              <input id="archivo" name="archivo" type="text" class="form-control input-md" value="<?php echo $_GET['file']; ?>">
            </div>
          </div>


          <div class="form-group">
            <label class="col-md-4 control-label" for="titulo">Titulo</label>
            <div class="col-md-4">
              <input  maxlength="44" id="titulo" name="titulo" type="text" placeholder="Titulo del libro (max. 44 letras)" class="form-control input-md" required="" value="<?php if ($metadatos['Title']) { echo $metadatos['Title']; } ?>">
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
          <div class="form-group form-inline">
            <label class="col-md-4 control-label" for="autor">Autor</label>
            <div class="col-md-4">

              <input id="autor_apellido" name="autor_apellido" type="text" placeholder="Apellidos" class="form-control input-md" required="" value="<?php if ($metadatos['Author(s)']) { echo $metadatos['Author(s)']; } ?>">,
              <input id="autor_nombre" name="autor_nombre" type="text" placeholder="Nombres" class="form-control input-md" required="" value="">

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
              <input id="categoria" name="categoria" type="text" placeholder="" class="hide form-control input-md" value="">

              <button onClick="adivinar();" type="button" class="btn btn-default">
                <span class="glyphicon glyphicon-star" aria-hidden="true"></span> Adivinar
              </button>

            </div>
          </div>

          <div class="form-group">
            <label class="col-md-4 control-label" for="cat">Portada</label>
            <div class="col-md-4">
              <button onClick="portadas();" type="button" class="btn btn-default">
                <span class="glyphicon glyphicon-star" aria-hidden="true"></span> Obtener mas imagenes
              </button>
              <div  id='portadas'>
                <img src="tmp/cover.jpg" class="img-thumbnail rounded float-left" alt="..." />
              </div>
              <input id="portada_url" name="portada_url" type="text" placeholder="URL de la imagen de portada" class="form-control input-md" required="" value="">
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-4 control-label" for="precio">Precio</label>
            <div class="col-md-4">
              <button data-toggle="modal" data-target="#myModal" onClick="precios();" type="button" class="btn btn-default">
                <span class="glyphicon glyphicon-star" aria-hidden="true"></span> Estimar
              </button>
              <input id="precio" name="precio" type="text" placeholder="Precio del libro" class="form-control input-md" required="" value="">
            </div>
          </div>

          <!-- Modal -->
          <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="myModalLabel">Precio estimado promedio: <span id="precio_promedio"></span></h4>
                </div>
                <div class="modal-body">
                  <div class="panel panel-default"> <div class="panel-heading">Detalle de publicaciones similares</div>
                  <table id="tabla_precios" class="table table-striped table-bordered" cellspacing="0"> <thead> <tr> <th>#</th> <th>Publicacion</th> <th>Precio</th></tr> </thead>
                    <tbody id='listado'>
                    </tbody>
                  </table>
                </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
              </div>
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



// curl  "https://www.googleapis.com/customsearch/v1?key=AIzaSyBQ7ia4EDNm7UZ2KoZ9fJavcIik7BKctps&cx=001091051484625242059:2flawceqnem&fields=items/pagemap/cse_image&q=el+educador+mercenario"
?>
