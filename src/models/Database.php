<?php

namespace Monlib\Models;

use PDO;
use PDOException;

use Dotenv\Dotenv;

class Database {
	private PDO $pdo;
	private Dotenv $dotenv;

	private string $url;
	private string $host;
	private string $dbname;
	private string $charset;
	private string $username;
	private string $password;

	private function connect() {
		$this->dotenv->load();

		try {
			$this->pdo	=	new PDO(
				$this->url, 
				$this->username, 
				$this->password
			);

			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new PDOException("Connection failed: " . $e->getMessage());
		}
	}

	public function __construct() {
		$this->dotenv		=	Dotenv::createImmutable('./');
		$this->dotenv->load();
		
		$this->host			=	$_ENV['DATABASE_HOST'];
		$this->dbname		=	$_ENV['DATABASE_DB'];
		$this->username		=	$_ENV['DATABASE_USER'];
		$this->charset		=	$_ENV['DATABASE_CHARSET'];
		$this->password		=	$_ENV['DATABASE_PASSWORD'];
		$this->url			=	$_ENV['DATABASE_DSN'] . ":host={$this->host};dbname={$this->dbname};charset={$this->charset}";
		
		$this->connect();
	}

	public function getUsername() { return $this->username; }

	public function getPassword() { return $this->password; }

	public function getDatabase() { return $this->dbname; }

	public function getHost() { return $this->host; }

	public function getUrl() { return $this->url; }

	public function getPDO() { return $this->pdo; }
	
}
