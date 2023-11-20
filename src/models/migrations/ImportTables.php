<?php

namespace Monlib\Models\Migrations;

use Monlib\Models\Database;

use Exception;
use PDOException;
use InvalidArgumentException;

class ImportTables {

	private $pdo;
	private Database $database;

	public function __construct() {
		$this->database	=	new Database;
		$this->pdo		=	$this->database->getPDO();    
	}

	public function createTablesFromFile(string $filePath) {
		$json	=	file_get_contents($filePath);
		if ($json === false) { throw new InvalidArgumentException('Unable to read JSON file'); }

		$tables	=	json_decode($json, true);
		if (!$tables || !is_array($tables)) { throw new InvalidArgumentException('Invalid JSON format'); }

		foreach ($tables as $table) { $this->createTable($table); }
	}

	private function createTable(array $data) {
		if (empty($data['table_name']) || empty($data['columns'])) { throw new InvalidArgumentException('Invalid table data'); }

		$columns	=	$data['columns'];
		$tableName	=	$data['table_name'];
		$collate	=	isset($data['collate']) ? "COLLATE {$data['collate']}" : "";
		$tableType	=	isset($data['table_type']) ? "ENGINE={$data['table_type']}" : "";
		
		$sql		=	"CREATE TABLE IF NOT EXISTS $tableName (";

		foreach ($columns as $column) {
			$columnName			=	$column['name'];
			$columnType			=	$column['type'];
			$defaultValue		=	$column['default'];
			$isUnique			=	!empty($column['unique']);
			$isPrimaryKey		=	!empty($column['primary_key']);
			$isNull				=	$column['null'] ? "NULL" : "NOT NULL";
			
			if ($column['default_current_timestamp']) {
				$defaultValue	=	"DEFAULT CURRENT_TIMESTAMP";
			} else {
				$defaultValue	=	$defaultValue !== null ? "DEFAULT '{$defaultValue}'" : "";
			}
	
			$sql				.=	"$columnName $columnType $defaultValue $isNull";
	
			if ($isPrimaryKey) {
				$sql			.=	" PRIMARY KEY";
			}
	
			if ($isUnique) {
				$sql			.=	" UNIQUE";
			}
	
			$sql				.=	",";
		}
	
		$sql					=	rtrim($sql, ',') . ") $collate $tableType";
	
		$this->pdo->exec($sql);
		echo "Table '$tableName' was created with successfully.\n";
	}

	public function importFromFile(string $database, string $file) {
		$path	=	"migrations/" . $database . "_" . $file . ".json";
		
		try {
			$this->createTablesFromFile($path);
		} catch (PDOException $e) {
			echo 'Connection error: ' . $e->getMessage();
		} catch (Exception $e) {
			echo 'Import file error: ' . $e->getMessage();
		}
	}

}
