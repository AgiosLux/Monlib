<?php

require_once "vendor/autoload.php";
error_reporting(0);

use Monlib\Utils\View;
use Monlib\Http\Router;
use Monlib\Http\Response;
use Monlib\Controllers\Pages\Home;

use Dotenv\Dotenv;

Dotenv::createImmutable('./')->load();
$xUrl		=	explode('/', $_GET['url']);
$pages		=	new Router($_ENV['URL_ROOT']);

View::init([
	"URL"	=>	$_ENV['URL_ROOT'],
]);

$pages->get('/', [
	function () {
		return new Response(200, Home::getHome());
	}
]);

$pages->run()->sendResponse();