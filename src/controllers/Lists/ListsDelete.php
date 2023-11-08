<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Dotenv\Dotenv;

class ListDelete {

	protected string $path;
	protected string $table;
	protected Dotenv $dotenv;
	protected ORM $orm;

	public function __construct(string $table = 'lists') {
		$this->dotenv	=	Dotenv::createImmutable('./');
		$this->dotenv->load();

		$this->orm		=	new ORM($table);
		$this->path		=	$_ENV['STORAGE_PATH'];
	}

}