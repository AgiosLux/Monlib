<?php

namespace Monlib\Models;
use Monlib\Models\Database;

use PDO;
use PDOException;

class SQLImporter {
	private PDO $pdo;
	protected Database $database;

	public function __construct() {
		$this->database	=	new Database;
		$this->pdo		=	$this->database->getPDO();
	}

	public function importSQLFile(string $sqlFile): bool {
		try {
			$sql			=	file_get_contents($sqlFile);
			$statements		=	explode(";", $sql);

			foreach ($statements as $statement) {
				if (!empty(trim($statement))) {
					$this->pdo->exec($statement);
				}
			}

			return true;
		} catch (PDOException $e) {
			echo "Error importing the SQL file: " . $e->getMessage();
			return false;
		}
	}

}
