<?php

namespace Monlib\Models;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database {

	private string $host;
	private string $dbname;
	private string $username;
	private string $password;
	private string $charset;
	private PDO $pdo;
	private Dotenv $dotenv;

	public function __construct() {
		$this->dotenv		=	Dotenv::createImmutable('./');
		$this->dotenv->load();
		
		$this->host			=	$_ENV['DATABASE_HOST'];
		$this->dbname		=	$_ENV['DATABASE_DB'];
		$this->username		=	$_ENV['DATABASE_USER'];
		$this->charset		=	$_ENV['DATABASE_CHARSET'];
		$this->password		=	$_ENV['DATABASE_PASSWORD'];

		$this->connect();
	}

	private function connect() {
		$this->dotenv->load();
		$dsn = $_ENV['DATABASE_DSN'] . ":host={$this->host};dbname={$this->dbname};charset={$this->charset}";

		try {
			$this->pdo	=	new PDO($dsn, $this->username, $this->password);
			
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new PDOException("Connection failed: " . $e->getMessage());
		}
	}

	public function getPDO() { return $this->pdo; }
	
}
