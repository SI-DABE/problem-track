<?php

namespace Core\Database;

use Core\Constants\Constants;
use PDO;
use Core\Database\QueryBuilder\SQLQueryBuilder;
use Exception;
use Closure;

class Database
{
    private static ?PDO $connection = null;

    public static function getDatabaseConn(): PDO
    {
        if (self::$connection === null) {
            $user = $_ENV['DB_USERNAME'];
            $pwd  = $_ENV['DB_PASSWORD'];
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $db   = $_ENV['DB_DATABASE'];

            self::$connection = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db, $user, $pwd);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$connection;
    }

    public static function getConn(): PDO
    {
        $user = $_ENV['DB_USERNAME'];
        $pwd  = $_ENV['DB_PASSWORD'];
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];

        $pdo = new PDO('mysql:host=' . $host . ';port=' . $port, $user, $pwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function create(): void
    {
        $sql = 'CREATE DATABASE IF NOT EXISTS ' . $_ENV['DB_DATABASE'] . ';';
        self::getConn()->exec($sql);
    }

    public static function drop(): void
    {
        $sql = 'DROP DATABASE IF EXISTS ' . $_ENV['DB_DATABASE'] . ';';
        self::getConn()->exec($sql);
    }

    public static function migrate(): void
    {
        $sql = file_get_contents(Constants::databasePath()->join('schema.sql'));
        self::getDatabaseConn()->exec($sql);
    }

    public static function exec(string $sql): void
    {
        self::getDatabaseConn()->exec($sql);
    }

    public static function getQueryBuilder(): SQLQueryBuilder
    {
        return new SQLQueryBuilder();
    }

    /**
     * Laravel style query builder
     */
    public static function table(string $table): SQLQueryBuilder
    {
        return (new SQLQueryBuilder())->table($table);
    }

    /**
     * Laravel style insert using QueryBuilder
     */
    public static function insertTable(string $table, array $data): int
    {
        return self::table($table)->insertData($data);
    }

    /**
     * Laravel style update using QueryBuilder  
     */
    public static function updateTable(string $table, array $data, array $where = []): int
    {
        $builder = self::table($table);
        foreach ($where as $field => $value) {
            $builder->where($field, $value);
        }
        return $builder->updateData($data);
    }

    /**
     * Laravel style delete using QueryBuilder
     */
    public static function deleteTable(string $table, array $where = []): int
    {
        $builder = self::table($table);
        foreach ($where as $field => $value) {
            $builder->where($field, $value);
        }
        return $builder->deleteData();
    }

    /**
     * Begin transaction (Laravel style)
     */
    public static function beginTransaction(): void
    {
        self::getDatabaseConn()->beginTransaction();
    }

    /**
     * Commit transaction (Laravel style)
     */
    public static function commit(): void
    {
        self::getDatabaseConn()->commit();
    }

    /**
     * Rollback transaction (Laravel style)
     */
    public static function rollBack(): void
    {
        self::getDatabaseConn()->rollBack();
    }

    /**
     * Execute a closure within a transaction (Laravel style)
     */
    public static function transaction(Closure $callback)
    {
        self::beginTransaction();

        try {
            $result = $callback();
            self::commit();
            return $result;
        } catch (Exception $e) {
            self::rollBack();
            throw $e;
        }
    }

    /**
     * Check if currently in a transaction
     */
    public static function inTransaction(): bool
    {
        return self::getDatabaseConn()->inTransaction();
    }

    /**
     * Execute raw SQL with bindings (Laravel style)
     */
    public static function statement(string $sql, array $bindings = []): bool
    {
        $stmt = self::getDatabaseConn()->prepare($sql);
        return $stmt->execute($bindings);
    }

    /**
     * Execute SELECT statement and return results (Laravel style)
     */
    public static function select(string $sql, array $bindings = []): array
    {
        $stmt = self::getDatabaseConn()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute INSERT statement and return last insert ID (Laravel style)
     */
    public static function insert(string $sql, array $bindings = []): int
    {
        $stmt = self::getDatabaseConn()->prepare($sql);
        $stmt->execute($bindings);
        return (int) self::getDatabaseConn()->lastInsertId();
    }

    /**
     * Execute UPDATE statement and return affected rows (Laravel style)
     */
    public static function update(string $sql, array $bindings = []): int
    {
        $stmt = self::getDatabaseConn()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    /**
     * Execute DELETE statement and return affected rows (Laravel style)
     */
    public static function delete(string $sql, array $bindings = []): int
    {
        $stmt = self::getDatabaseConn()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }
}
