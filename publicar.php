<?php
session_start();

echo 'access_token: ' . $_SESSION['access_token'];

require 'assets/meli-php-sdk/Meli/meli.php';
require 'includes/config.php';

$meli = new Meli($APP_ID, $SECRET_KEY);

if($_GET['code'] || $_SESSION['access_token']) {

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

	// We construct the item to POST
	$item = array(
		"title" => "Item de Prueba – Por favor, NO OFERTAR",
		"category_id" => "MLA1227",
		"price" => 10,
		"currency_id" => "ARS",
		"available_quantity" => 1,
		"buying_mode" => "buy_it_now",
		"listing_type_id" => "bronze",
		"condition" => "new",
		"description" => "Item:, NO OFERTAR",
	    "attributes" => array(
	        array(
	        	"id" => "AUTHOR",
	        	"value_name" => "Autor"
	        ),
	        array(
	        	"id" => "PUBLISHER",
	        	"value_name" => "Editorial"
	        ),
	        array(
	        	"id" => "FORMAT",
	        	"value_name" => "Digital"
	        ),
	        array(
	        	"id" => "LANGUAGE",
	        	"value_name" => "Español"
	        ),
	        array(
	        	"id" => "ISBN",
	        	"value_name" => "0123456789"
	        ),

	    ),
		"pictures" => array(
			array(
				"source" => "https://upload.wikimedia.org/wikipedia/commons/f/fd/Ray_Ban_Original_Wayfarer.jpg"
			),
			array(
				"source" => "https://upload.wikimedia.org/wikipedia/commons/a/ab/Teashades.gif"
			)
		)
	);

	// We call the post request to list a item
	echo '<pre>';
	print_r($meli->post('/items', $item, array('access_token' => $_SESSION['access_token'])));
	echo '</pre>';
} else {

	echo '<a href="' . $meli->getAuthUrl('https://'.$DOMAIN.'/login.php', Meli::$AUTH_URL['MLA']) . '">Login using MercadoLibre oAuth 2.0</a>';
}
