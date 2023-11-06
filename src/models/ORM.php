<?php

namespace Monlib\Models;

use Monlib\models\Database;

use PDO;

class ORM {
    
    protected PDO $pdo;
    protected string $table;
    protected Database $database;

    public function __construct(string $table) {
        $this->table    =   $table;
        $this->database =   new Database;
        $this->pdo      =   $this->database->getPDO();
    }

    public function select(array $conditions = []) {
        $query = "SELECT * FROM {$this->table}";

        if (!empty($conditions)) {
            $query .= " WHERE ";
            $whereConditions = [];

            foreach ($conditions as $field => $value) {
                $whereConditions[] = "$field = :$field";
            }

            $query .= implode(" AND ", $whereConditions);
        }

        $statement = $this->pdo->prepare($query);

        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $statement->bindValue(":$field", $value);
            }
        }

        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

	public function create(array $data = []) {
		$columns    =   implode(', ', array_keys($data));
		$values     =   ':' . implode(', :', array_keys($data));
		$query      =   "INSERT INTO $this->table ($columns) VALUES ($values)";
		
		$statement = $this->pdo->prepare($query);

		foreach ($data as $key => $value) {
			$statement->bindValue(
				":$key", $value
			);
		}

		$statement->execute();
		return $this->pdo->lastInsertId();
	}
    
    public function delete(array $conditions = []) {
        $query = "DELETE FROM {$this->table}";
    
        if (!empty($conditions)) {
            $query .= " WHERE ";
            $whereConditions = [];
    
            foreach ($conditions as $field => $value) {
                $whereConditions[] = "$field = :$field";
            }
    
            $query .= implode(" AND ", $whereConditions);
        }
    
        $statement = $this->pdo->prepare($query);
    
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $statement->bindValue(":$field", $value);
            }
        }
    
        $statement->execute();
        return $statement->rowCount();
    }

    public function update(array $data = [], array $conditions = []) {
        if (empty($data)) {
            return false;
        }
    
        $query = "UPDATE {$this->table} SET ";
        $setValues = [];
    
        foreach ($data as $field => $value) {
            $setValues[] = "$field = :$field";
        }
    
        $query .= implode(", ", $setValues);
    
        if (!empty($conditions)) {
            $query .= " WHERE ";
            $whereConditions = [];
    
            foreach ($conditions as $field => $value) {
                $whereConditions[] = "$field = :$field";
            }
    
            $query .= implode(" AND ", $whereConditions);
        }
    
        $statement = $this->pdo->prepare($query);
    
        foreach ($data as $field => $value) {
            $statement->bindValue(":$field", $value);
        }
    
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $statement->bindValue(":$field", $value);
            }
        }
    
        $statement->execute();
        return $statement->rowCount();
    }

    public function select_like(array $columns = [], string $searchTerm) {
        if (empty($columns) || empty($searchTerm)) {
            return [];
        }
    
        $query = "SELECT * FROM {$this->table} WHERE ";
        $conditions = [];
    
        foreach ($columns as $column) {
            $conditions[] = "$column LIKE :searchTerm";
        }
    
        $query .= implode(" OR ", $conditions);
    
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':searchTerm', "%$searchTerm%");
    
        $statement->execute();
    
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function count(array $conditions = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
    
        if (!empty($conditions)) {
            $query .= " WHERE ";
            $whereConditions = [];
    
            foreach ($conditions as $field => $value) {
                $whereConditions[] = "$field = :$field";
            }
    
            $query .= implode(" AND ", $whereConditions);
        }
    
        $statement = $this->pdo->prepare($query);
    
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $statement->bindValue(":$field", $value);
            }
        }
    
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
    
        return intval($result['total']);
    }

}
