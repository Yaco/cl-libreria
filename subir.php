<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (isset($_POST['btnSubir'])) {

  $target_dir = "tmp/";
  $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
  $uploadOk = 1;
  $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
  // Check if image file is a actual image or fake image
  // if(isset($_POST["submit"])) {
  //     $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  //     if($check !== false) {
  //         echo "File is an image - " . $check["mime"] . ".";
  //         $uploadOk = 1;
  //     } else {
  //         echo "File is not an image.";
  //         $uploadOk = 0;
  //     }
  // }
  // Check if file already exists
  if (file_exists($target_file)) {
      echo "El archivo ya existe.";
      $uploadOk = 0;
  }
  // Allow certain file formats
  if($imageFileType != "pdf" && $imageFileType != "epub" ) {
      echo "No es un PDF ni un EPUB.";
      $uploadOk = 0;
  }
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
      echo "No se subio.";
  // if everything is ok, try to upload file
  } else {
      if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
          echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
          header('Location: /metadatos.php?file='.$target_file);

      } else {
          echo "Sorry, there was an error uploading your file.";
      }
  }
} else {
  $ext_tmp = explode('.',$_POST['fileToDL']); // extension del archivo original
  $ext = end($ext_tmp);

  $target_file = 'tmp/temp.'.$ext;
  $metadata = shell_exec('wget ' . $_POST['fileToDL'] . ' -O '. $target_file);
print_r($metadata);
  if (!file_exists($target_file)) {
      echo "No se descargo el archivo";
      exit();
  }

  $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
  echo $imageFileType;
  if($imageFileType != "pdf" && $imageFileType != "epub" ) {
      echo "No es un PDF ni un EPUB.";
      exit();
  }

  header('Location: /metadatos.php?file='.$target_file);

}


?>
