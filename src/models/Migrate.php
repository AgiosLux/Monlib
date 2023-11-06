<?php

namespace Monlib\Models;
use Monlib\Models\Database;

use PDO;
use PDOException;

class Migration {
	private PDO $pdo;
	protected Database $database;

	public function __construct() {
		$this->database		=   new Database;
		$this->pdo      	=   $this->database->getPDO();
	}

	public function createTable(string $tableName, string $columns): void {
		$sql	=	"CREATE TABLE IF NOT EXISTS $tableName ($columns)";

		try {
			$this->pdo->exec($sql);
			echo "Table $tableName created successfully." . PHP_EOL;
		} catch (PDOException $e) {
			echo "Error creating table: " . $e->getMessage() . PHP_EOL;
		}
	}

	public function runMigration(string $migrationSQL): void {
		try {
			$this->pdo->exec($migrationSQL);

			echo "Migration executed successfully." . PHP_EOL;
		} catch (PDOException $e) {
			echo "Error executing migration: " . $e->getMessage() . PHP_EOL;
		}
	}

}
