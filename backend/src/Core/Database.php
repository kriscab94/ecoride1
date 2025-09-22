<?php

namespace EcoRide\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $host = $_ENV['DB_HOST'] ?? 'mysql';
            $dbname = $_ENV['DB_NAME'] ?? 'ecoride';
            $username = $_ENV['DB_USER'] ?? 'ecoride_user';
            $password = $_ENV['DB_PASS'] ?? 'ecoride_pass';
            
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO {
        return $this->connection;
    }
    
    public function prepare(string $sql) {
        return $this->connection->prepare($sql);
    }
    
    public function query(string $sql) {
        return $this->connection->query($sql);
    }
    
    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }
    
    public function commit(): bool {
        return $this->connection->commit();
    }
    
    public function rollback(): bool {
        return $this->connection->rollback();
    }
}