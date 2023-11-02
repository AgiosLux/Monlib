<?php

namespace Monlib\models;

use PDO;
use PDOException;

class Migration {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createTable($tableName, $columns) {
        $sql = "CREATE TABLE IF NOT EXISTS $tableName ($columns)";
        try {
            $this->pdo->exec($sql);
            echo "Tabela $tableName criada com sucesso." . PHP_EOL;
        } catch (PDOException $e) {
            echo "Erro ao criar tabela: " . $e->getMessage() . PHP_EOL;
        }
    }

    public function runMigration($migrationSQL) {
        try {
            $this->pdo->exec($migrationSQL);
            echo "Migração executada com sucesso." . PHP_EOL;
        } catch (PDOException $e) {
            echo "Erro ao executar migração: " . $e->getMessage() . PHP_EOL;
        }
    }
}
