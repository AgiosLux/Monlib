<?php

require_once "vendor/autoload.php";
// error_reporting(0);

use Monlib\Http\Router;
use Monlib\Http\Response;

use Dotenv\Dotenv;

Dotenv::createImmutable('./')->load();
$router		=	new Router($_ENV['URL_ROOT']);
$xUrl		=	explode('/', $_GET['urlRooter']);

$router->post('/api/account/login', [
	function () {
		$login	=	new Monlib\Controllers\Account\Login;
		return new Response(0, $login->loginAccount(), 'application/json');
	}
]);

$router->post('/api/account/register', [
	function () {
		$register	=	new Monlib\Controllers\Account\Register;
		return new Response(0, $register->createAccount(), 'application/json');
	}
]);

$router->get('/api/account/logoff', [
	function () {
		$login	=	new Monlib\Controllers\Account\Login;
		return new Response(0, $login->doLogoff(), 'application/json');
	}
]);

$router->get('/api/account/check-logged', [
	function () {
		$login	=	new Monlib\Controllers\Account\Login;
		return new Response(0, $login->checkUserLogged(), 'application/json');
	}
]);

$router->get('/api/tools/inspect', [
	function () {
		$inspect	=	new Monlib\Controllers\Tools\Inspect($_GET['url']);
		return new Response(0, $inspect->getData(), 'application/json');
	}
]);

$router->post('/api/lists/create', [
	function () {
		$listsCreate	=	new Monlib\Controllers\Lists\ListsCreate;
		return new Response(0, $listsCreate->uploadAndCreate(), 'application/json');
	}
]);

$router->get('/api/lists/{username}/{listID}/{section}', [
	function ($username, $listID, $section) {
		$listsRead	=	new Monlib\Controllers\Lists\ListsRead($username, $listID);

		switch (strtolower($section)) {
			case 'raw':
				return new Response(0, $listsRead->raw(), 'application/json');

			case 'inspect':
				return new Response(0, $listsRead->inspect(), 'application/json');

			case 'details':
				return new Response(0, $listsRead->get(), 'application/json');

			default:
				return new Response(404, 'URL not found', 'application/json');
		}
	}
]);

$router->run()->sendResponse();
