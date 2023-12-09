<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;

use Dotenv\Dotenv;

class ListUpdate {

	protected ORM $orm;
	protected string $path;
	protected string $table;
	protected Dotenv $dotenv;

	public function __construct(string $table = 'lists') {
		$this->dotenv	=	Dotenv::createImmutable('./');
		$this->dotenv->load();

		$this->orm		=	new ORM($table);
		$this->path		=	$_ENV['STORAGE_PATH'];
	}

}
