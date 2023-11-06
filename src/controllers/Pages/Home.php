<?php

namespace Monlib\Controllers\Pages;
use Monlib\Utils\View;

class Home {

	public static function getHome() {
		return View::render("home", [
			'name'			=>	'Monlib',
			'description'	=>	'teste',
		]);
	}

}