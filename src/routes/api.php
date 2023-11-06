<?php

require_once "vendor/autoload.php";
error_reporting(0);

use Monlib\Http\Router;
use Monlib\Http\Response;

use Dotenv\Dotenv;

$xUrl			=	explode('/', $_GET['url']);
$dotenv			=	Dotenv::createImmutable('./')->load();
$router			=	new Router($_ENV['URL_ROOT']);

$listsCreate	=	new Monlib\Controllers\Lists\ListsCreate;

$router->post('/api/lists/create', [
	function () use ($listsCreate) {
		return new Response(200, $listsCreate->uploadAndCreate());
	}
]);

$router->run()->sendResponse();
