<?php

namespace Monlib\Controllers\Lists;

use Monlib\Models\ORM;
use Monlib\Models\Database;

use PDO;
use Dotenv\Dotenv;

class ListUpdate {

	protected string $path;
	protected string $table;
	protected PDO $pdo;
	protected Database $database;
	protected Dotenv $dotenv;
	protected ORM $orm;

	public function __construct(string $table = 'lists') {
		$this->database =	new Database;
		$this->dotenv	=	Dotenv::createImmutable('./');
		$this->dotenv->load();

		$this->table	=	$table;
		$this->path		=	$_ENV['STORAGE_PATH'];
		$this->orm		=	new ORM($this->table);
	}

}