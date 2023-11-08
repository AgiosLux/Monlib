<?php

require_once "vendor/autoload.php";
// error_reporting(0);

use Monlib\Http\Router;
use Monlib\Http\Response;

use Dotenv\Dotenv;

$xUrl		=	explode('/', $_GET['url']);
$dotenv		=	Dotenv::createImmutable('./')->load();
$router		=	new Router($_ENV['URL_ROOT']);

$router->post('/api/lists/create', [
	function () {
		$listsCreate	=	new Monlib\Controllers\Lists\ListsCreate;
		return new Response(200, $listsCreate->uploadAndCreate());
	}
]);

$router->get('/api/lists/{username}/{list_id}/{section}', [
	function ($username, $list_id, $section) {
		$listsRead	=	new Monlib\Controllers\Lists\ListsRead($username, $list_id);

		switch (strtolower($section)) {
			case 'raw':
				return new Response(0, $listsRead->raw());

			case 'inspect':
				return new Response(0, $listsRead->inspect());

			default:
				return new Response(0, $listsRead->get());
		}
	}
]);

$router->run()->sendResponse();
