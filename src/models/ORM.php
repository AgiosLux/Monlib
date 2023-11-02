<?php

namespace Monlib\Models;

use PDO;

class ORM {
    protected string $table;
    protected PDO $pdo;

    public function __construct(PDO $pdo, string $table) {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function find($id) {
        $query = "SELECT * FROM $this->table WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->bindParam(':id', $id);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function all() {
        $query = "SELECT * FROM $this->table";
        $statement = $this->pdo->query($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));
        $query = "INSERT INTO $this->table ($columns) VALUES ($values)";
        $statement = $this->pdo->prepare($query);
        foreach ($data as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        $statement->execute();
        return $this->pdo->lastInsertId();
    }

    public function update($id, array $data) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(', ', $set);
        $query = "UPDATE $this->table SET $set WHERE id = :id";
        $data['id'] = $id;
        $statement = $this->pdo->prepare($query);
        $statement->execute($data);
        return $statement->rowCount();
    }

    public function delete($id) {
        $query = "DELETE FROM $this->table WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->bindParam(':id', $id);
        $statement->execute();
        return $statement->rowCount();
    }
}
