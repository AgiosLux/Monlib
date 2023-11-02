<?php

namespace Monlib\models;
use PDO;
use PDOException;

class SQLImporter {
    
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function importSQLFile($sqlFile) {
        try {
            $sql = file_get_contents($sqlFile);
            $statements = explode(";", $sql);

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
