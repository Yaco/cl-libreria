<?php
session_start();
putenv("LANG=es_AR.UTF-8");

require 'assets/meli-php-sdk/Meli/meli.php';
require 'includes/config.php';

$meli = new Meli($APP_ID, $SECRET_KEY);

if($_GET['code'] || $_SESSION['access_token']) {

	// Movemos el archivo


	$parte1 = trim($_POST['autor_apellido'][0]); // primera letra del apellido
	$parte2 = preg_replace('/[^A-Za-z0-9\-_\']/', '',  str_replace(' ', '_', trim($_POST['autor_apellido']))) . '-' . preg_replace('/[^A-Za-z0-9\-_\']/', '',  str_replace(' ', '_', trim($_POST['autor_nombre']))); // apellido-nombres
	$parte3 = preg_replace('/[^A-Za-z0-9\-_\']/', '',  str_replace(' ', '_', $_POST['titulo'])); // apellido-nombre-titulo
	$ext_tmp = explode('.',$_POST['archivo']); // extension del archivo original
	$ext = end($ext_tmp);
	$ext_img_tmp = explode('.',$_POST['portada_url']); // extension de la imagen
	$ext_img = end($ext_img_tmp);

	$destino = strtolower("files/{$parte1}/{$parte2}/{$parte3}/{$parte2}-{$parte3}.{$ext}");
	$destino_opf = strtolower("files/{$parte1}/{$parte2}/{$parte3}/{$parte2}-{$parte3}.opf");
	$destino_img = strtolower("files/{$parte1}/{$parte2}/{$parte3}/{$parte2}-{$parte3}.{$ext_img}");


	// echo $destino;
	if (!is_dir(dirname($destino))) {
	    mkdir(dirname($destino), 0777, true);
	}
	rename($_POST['archivo'], $destino);

	$metadata = shell_exec("ebook-meta {$destino} -t '{$_POST['titulo']}' -a '{$_POST['autor_nombre']} {$_POST['autor_apellido']}' -p '{$_POST['editorial']}' -d '{$_POST['ano']}-01-01' --tags '{$_POST['cat']}'");

	// Genera OPF
	$metadata = shell_exec("ebook-meta {$destino} --to-opf={$destino_opf}");

	// Movemos la imagen de portada

	if ($_POST['portada_url'] == 'tmp/cover.jpg') {
		rename($_POST['portada_url'], $destino_img);
	} else {
		$wget = shell_exec('wget -O ' . $destino_img . ' ' . $_POST['portada_url']);
	}

	// Almcenamos metadatos en archivo

	$metadata = shell_exec("ebook-meta -t {$_POST['titulo']} -a {$_POST['autor_nombre']} {$_POST['autor_apellido']} -p {$_POST['editorial']} -d {$_POST['ano']}-01-01 -l es {$destino}");


	// If the code was in get parameter we authorize

	// $user = $meli->authorize($_SESSION['refresh_token'], 'https://'.$DOMAIN.'/login.php');

	// // Now we create the sessions with the authenticated user
	// $_SESSION['access_token'] = $user['body']->access_token;
	// $_SESSION['expires_in'] = $user['body']->expires_in;
	// $_SESSION['refresh_token'] = $user['body']->refresh_token;

	// We can check if the access token in invalid checking the time
	if($_SESSION['expires_in'] + time() + 1 < time()) {
		try {
			print_r($meli->refreshAccessToken());
		} catch (Exception $e) {
			echo "Exception: ",  $e->getMessage(), "\n";
		}
	}
	$titulo = strtoupper($_POST['titulo']);
	// We construct the item to POST
	$item = array(
		"title" => "{$titulo} | Libro digital",
		"category_id" => "{$_POST['categoria']}",
		"price" => $_POST['precio'],
		"currency_id" => "ARS",
		"available_quantity" => 10,
		"buying_mode" => "buy_it_now",
		"listing_type_id" => "bronze",
		"condition" => "new",
		"description" => '<p style="text-align: center;"><strong><span style="font-size: xx-large;"><br /></span></strong></p><p style="text-align: center;"><strong><span style="font-size: xx-large;">'.$titulo.'</span></strong></p><p style="text-align: center;"><span style="font-size: x-large;">'.$_POST['autor_nombre'].' '.$_POST['autor_apellido'].'</span></p><p style="text-align: center;"><span style="font-size: medium;">Editorial: '.$_POST['editorial'].' - Año: '.$_POST['ano'].'</span></p><p style="text-align: center;"></p><p style="text-align: center;"><span style="font-size: medium;">Version Digital</span></p>',
	    "attributes" => array(
					array(
						"id" => "EAN",
						"value_name" => "9780471117094"
					),
	        array(
	        	"id" => "AUTHOR",
	        	"value_name" => "{$_POST['autor_nombre']} {$_POST['autor_apellido']}"
	        ),
	        array(
	        	"id" => "PUBLISHER",
	        	"value_name" => "{$_POST['editorial']}"
	        ),
	        array(
	        	"id" => "FORMAT",
	        	"value_name" => "Digital"
	        ),
	        array(
	        	"id" => "LANGUAGE",
	        	"value_name" => "Español"
	        ),
	        // array(
	        // 	"id" => "ISBN",
	        // 	"value_name" => "0123456789"
	        // ),

	    ),
		"pictures" => array(
			array(
				"source" => "https://{$DOMAIN}/{$destino_img}"
			)
		)
	);

	// Publicamos el item
	$ml_return = $meli->post('/items', $item, array('access_token' => $_SESSION['access_token']));

	echo "<center>";
	echo "<h2><a href='{$ml_return['body']['permalink']}'>Ir al articulo en ML</a></h2>";
	echo "<h2><a href='/'>Cargar otro libro</a></h2>";
	echo "<h4><a href='{$destino}'>Descargar archivo subido</a></h4>";
	echo "</center>"



	echo "------------------------------------------------";
	echo '<pre>';
	print_r($item);
	echo "------------------------------------------------";

	print_r($ml_return);
	echo '</pre>';
} else {

	echo '<a href="' . $meli->getAuthUrl('https://'.$DOMAIN.'/login.php', Meli::$AUTH_URL['MLA']) . '">Login using MercadoLibre oAuth 2.0</a>';
}
