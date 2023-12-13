<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Utils\File;
use Monlib\Controllers\Account\Login;
use Monlib\Http\{Response, Callback};

use Dotenv\Dotenv;

class ListUpdate extends Response {

	protected ORM $orm;
	protected Login $login;
	protected string $path;
	protected string $table;
	protected Dotenv $dotenv;
	protected Callback $callback;

	public function __construct(string $table = 'lists') {
		Dotenv::createImmutable('./')->load();

		$this->login	=	new Login;
		$this->callback	=	new Callback;
		$this->orm		=	new ORM($table);
		$this->path		=	$_ENV['STORAGE_PATH'];
	}

}
