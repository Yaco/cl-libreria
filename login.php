<?php
session_start();

echo 'access_token: ' . $_SESSION['access_token'];
require 'assets/meli-php-sdk/Meli/meli.php';
require 'includes/config.php';

$meli = new Meli($APP_ID, $SECRET_KEY, $_SESSION['access_token'], $_SESSION['refresh_token']);

if($_GET['code'] || $_SESSION['access_token']) {

	// If code exist and session is empty
	if($_GET['code'] && !($_SESSION['access_token'])) {
		// If the code was in get parameter we authorize
		$user = $meli->authorize($_GET['code'], 'https://'.$DOMAIN.'/login.php');

		// Now we create the sessions with the authenticated user
		$_SESSION['access_token'] = $user['body']->access_token;
		$_SESSION['expires_in'] = time() + $user['body']->expires_in;
		$_SESSION['refresh_token'] = $user['body']->refresh_token;
	} else {
		// We can check if the access token in invalid checking the time
		if($_SESSION['expires_in'] < time()) {
			try {
				// Make the refresh proccess
				$refresh = $meli->refreshAccessToken();

				// Now we create the sessions with the new parameters
				$_SESSION['access_token'] = $refresh['body']->access_token;
				$_SESSION['expires_in'] = time() + $refresh['body']->expires_in;
				$_SESSION['refresh_token'] = $refresh['body']->refresh_token;
			} catch (Exception $e) {
			  	echo "Exception: ",  $e->getMessage(), "\n";
			}
		}
	}

	echo '<h2><a href="/">Empezar a publicar un libro!</a></h2>';

	echo '<pre>';
		print_r($_SESSION);
	echo '</pre>';

} else {
	echo '<a href="' . $meli->getAuthUrl('https://'.$DOMAIN.'/login.php', Meli::$AUTH_URL['MLA']) . '">Click aqui para iniciar sesión en ML</a>';
}
