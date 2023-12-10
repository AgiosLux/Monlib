<?php

require_once "vendor/autoload.php";
// error_reporting(0);

use Dotenv\Dotenv;
use Monlib\Http\{Response, Router};

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

$router->get('/api/account/check-logged', [
	function () {
		$login	=	new Monlib\Controllers\Account\Login;
		return new Response(0, $login->checkUserLogged(), 'application/json');
	}
]);

$router->get('/api/account/logoff', [
	function () {
		$login	=	new Monlib\Controllers\Account\Login;
		return new Response(0, $login->doLogoff(), 'application/json');
	}
]);

$router->post('/api/api-keys/create', [
	function () {
		$apiKey	=	new Monlib\Controllers\User\ApiKey;
		return new Response(0, $apiKey->generateNewKey(), 'application/json');
	}
]);

$router->post('/api/api-keys/change-status/{key}', [
	function ($key) {
		$apiKey	=	new Monlib\Controllers\User\ApiKey;
		return new Response(0, $apiKey->changeStatusKey($key), 'application/json');
	}
]);

$router->post('/api/api-keys/edit/{key}', [
	function ($key) {
		$apiKey	=	new Monlib\Controllers\User\ApiKey;
		return new Response(0, $apiKey->editKey($key), 'application/json');
	}
]);

$router->delete('/api/api-keys/delete/{key}', [
	function ($key) {
		$apiKey	=	new Monlib\Controllers\User\ApiKey;
		return new Response(0, $apiKey->deleteKey($key), 'application/json');
	}
]);

$router->get('/api/api-keys/list', [
	function ($key) {
		$apiKey	=	new Monlib\Controllers\User\ApiKey;
		return new Response(0, $apiKey->listAllKeys(), 'application/json');
	}
]);

$router->get('/api/api-keys/get/{key}', [
	function ($key) {
		$apiKey	=	new Monlib\Controllers\User\ApiKey;
		return new Response(0, $apiKey->getKey($key), 'application/json');
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
		$listsRead		=	new Monlib\Controllers\Lists\ListsRead($username, $listID);
		$listsStats		=	new Monlib\Controllers\Lists\ListsStats($username, $listID);
		$listsInspect	=	new Monlib\Controllers\Lists\ListsInspect($username, $listID);
		$listsDownload	=	new Monlib\Controllers\Lists\ListsDownload($username, $listID);

		switch (strtolower($section)) {
			case 'raw':
				return new Response(0, $listsRead->raw(), 'application/json');

			case 'inspect':
				return new Response(0, $listsInspect->inspect(), 'application/json');

			case 'details':
				return new Response(0, $listsRead->get(), 'application/json');

			case 'download':
				return new Response(0, $listsDownload->makeDownload($_GET['no-ignore']), 'application/json');

			case 'stats':
				return new Response(0, $listsStats->getStats($_GET['action']), 'application/json');

			case 'qrcode':
				return new Response(0, $listsRead->qrCode(), 'image/svg+xml');

			default:
				return new Response(404, json_encode('URL not found'), 'application/json');
		}
	}
]);

$router->get('/api/profile/{username}/{section}', [
	function ($username, $section) {
		$listsList	=	new Monlib\Controllers\Lists\ListsList($username);

		switch (strtolower($section)) {
			case 'lists':
				return new Response(200, $listsList->listAllLists($_GET['privacy'], $_GET['offset'], $_GET['limit']), 'application/json');
		}
	}
]);

$router->run()->sendResponse();
