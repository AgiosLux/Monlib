<?php

namespace Monlib\Models\Migrations;

use Monlib\Models\Database;

use PDO;
use DateTime;
use PDOException;
use RuntimeException;

class ExportTables {

	protected $pdo;
	private Database $database;

	public function __construct() {
		$this->database	=	new Database;
		$this->pdo		=	$this->database->getPDO();    
	}

	private function exportAllTablesToJson() {
		$allTablesData	=	[];
		$tablesName		=	[];
		$tables			=	$this->getTables();

		foreach ($tables as $tableName) {
			$tableData			=	$this->getTableData($tableName);

			$tablesName[]       =	$tableName;
			$allTablesData[]	=	$tableData;
		}

		$json					=	json_encode($allTablesData, JSON_PRETTY_PRINT);
		if ($json === false) { throw new RuntimeException('Error encoding to JSON.'); }

		$currentDateTime		=	(new DateTime())->format('Y-m-d_H-i-s');
		$outputFilePath			=	"migrations/" . $this->database->getDatabase() . "_" . $currentDateTime . ".json";

		file_put_contents($outputFilePath, $json);

		foreach ($tablesName as $tableName) { echo "Table '$tableName' was export successfully\n"; }
		echo "All tables exported with successfully.\n";
	}

	private function getTables() {
		$sql	=	"SHOW TABLES";
		$stmt	=	$this->pdo->query($sql);
		$tables	=	$stmt->fetchAll(PDO::FETCH_COLUMN);

		return $tables;
	}

	private function getTableData(string $tableName) {
		$tableType			=	$this->getTableType($tableName);
        $tableComment		=	$this->getTableComment($tableName);
		$columns			=	$this->getTableColumns($tableName);
		$collation			=	$this->getTableCollation($tableName);
        $foreignKeys    	=	$this->getTableForeignKeys($tableName);

		return [
			'table_name'	=>	$tableName,
			'collate'		=>	$collation,
			'table_type'	=>	$tableType,
            'foreign_keys'	=>	$foreignKeys,
            'table_comment'	=>	$tableComment,
			'columns'		=>	$columns,
		];
	}

    private function getTableComment(string $tableName) {
        $sql			=	"SHOW TABLE STATUS LIKE :tableName";
        $stmt			=	$this->pdo->prepare($sql);
        $stmt->bindParam(':tableName', $tableName, PDO::PARAM_STR);
        $stmt->execute();

        $tableStatus	=	$stmt->fetch(PDO::FETCH_ASSOC);
        return isset($tableStatus['Comment']) ? $tableStatus['Comment'] : null;
    }

	Private function getTableCollation(string $tableName) {
		$sql			=	"SHOW TABLE STATUS LIKE :tableName";
		$stmt			=	$this->pdo->prepare($sql);
		$stmt->bindParam(':tableName', $tableName, PDO::PARAM_STR);
		$stmt->execute();
		
		$tableStatus	=	$stmt->fetch(PDO::FETCH_ASSOC);
		return isset($tableStatus['Collation']) ? $tableStatus['Collation'] : null;
	}

	private function getTableType(string $tableName) {
		$sql			=	"SHOW TABLE STATUS LIKE :tableName";
		$stmt			=	$this->pdo->prepare($sql);
		$stmt->bindParam(':tableName', $tableName, PDO::PARAM_STR);
		$stmt->execute();

		$tableStatus	=	$stmt->fetch(PDO::FETCH_ASSOC);
		return isset($tableStatus['Engine']) ? $tableStatus['Engine'] : null;
	}

    private function getTableForeignKeys(string $tableName) {
        $sql					=	"SHOW CREATE TABLE $tableName";
        $stmt					=	$this->pdo->query($sql);
        $tableCreate			=	$stmt->fetch(PDO::FETCH_ASSOC);

        $foreignKeys			=	[];

        if (isset($tableCreate['Create Table'])) {
            $tableCreateSql		=	$tableCreate['Create Table'];
            preg_match_all('/FOREIGN KEY \(`(.+?)`\) REFERENCES `(.+?)` \(`(.+?)`\)/', $tableCreateSql, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $foreignKeys[]			=	[
                    'column'			=>	$match[1],
                    'referenced_table'	=>	$match[2],
                    'referenced_column'	=>	$match[3],
                ];
            }
        }

        return $foreignKeys;
    }

	private function getTableColumns(string $tableName) {
		$sql					=	"SHOW COLUMNS FROM $tableName";
		$stmt					=	$this->pdo->query($sql);
		$columns				=	$stmt->fetchAll(PDO::FETCH_ASSOC);

		$formattedColumns		=	[];

		foreach ($columns as $column) {
			$formattedColumn		=	[
				'name'				=>	$column['Field'],
				'type'				=>	$column['Type'],
				'default'			=>	$column['Default'],
				'extra'				=>	$column['Extra'],
				'comment'			=>	$column['Comment'],
				'primary_key'		=>	$column['Key'] === 'PRI',
				'unique'			=>	$column['Key'] === 'UNI',
			];
	
			if (stripos($column['Default'], 'CURRENT_TIMESTAMP') !== false) {
				$formattedColumn['default_current_timestamp']	=	true;
			} else {
				$formattedColumn['default_current_timestamp']	=	false;
			}
	
			if ($column['Collation'] !== null) {
				$formattedColumn['collation']	=	$column['Collation'];
			}
	
			$formattedColumns[]					=	$formattedColumn;
		}

		return $formattedColumns;
	}
	
	public function exportAllTables() {
		try {
			$this->exportAllTablesToJson();
		} catch (PDOException $e) {
			echo 'Connection error: ' . $e->getMessage();
		}
	}

}
