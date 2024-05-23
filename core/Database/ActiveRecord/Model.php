<?php

namespace Core\Database\ActiveRecord;

use Core\Database\Database;
use Lib\Paginator;

abstract class Model
{
    /** @var array<string, string> */
    protected array $errors = [];
    protected ?int $id = null;

    private array $attributes = [];

    protected static $table = null;
    protected static $columns = [];

    /**
     * Model constructor.
     */
    public function __construct($params = [])
    {
        // Initialize attributes with null from database columns
        foreach (static::$columns as $column) {
            $this->attributes[$column] = null;
        }

        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }

    /* ------------------- MAGIC METHODS ------------------- */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        if (array_key_exists($property, $this->attributes)) {
            return $this->attributes[$property];
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
            return $this;
        }

        if (array_key_exists($property, $this->attributes)) {
            $this->attributes[$property] = $value;
            return $this;
        }

        return $this;
    }

    /* ------------------- VALIDATIONS METHODS ------------------- */
    public function isValid(): bool
    {
        $this->errors = [];

        $this->validates();

        return empty($this->errors);
    }

    public function newRecord(): bool
    {
        return $this->id === null;
    }

    public function hasErrors(): bool
    {
        return empty($this->errors);
    }

    public function errors(string $index): string | null
    {
        if (isset($this->errors[$index])) {
            return $this->errors[$index];
        }

        return null;
    }

    public abstract function validates(): void;

    /* ------------------- DATABASE METHODS ------------------- */
    public function save(): bool
    {
        if ($this->isValid()) {
            $pdo = Database::getDatabaseConn();
            if ($this->newRecord()) {
                $table = static::$table;
                $attributes = implode(', ', static::$columns);
                $values = ':' . implode(', :', static::$columns);

                $sql = <<<SQL
                    INSERT INTO {$table} ({$attributes}) VALUES ({$values});
                SQL;

                $stmt = $pdo->prepare($sql);
                foreach (static::$columns as $column) {
                    $stmt->bindValue($column, $this->$column);
                }

                $stmt->execute();

                $this->id = (int) $pdo->lastInsertId();
            } else {
                $table = static::$table;

                $sets = array_map(function ($column) {
                    return "{$column} = :{$column}";
                }, static::$columns);
                $sets = implode(', ', $sets);

                $sql = <<<SQL
                    UPDATE {$table} set {$sets} WHERE id = :id;
                SQL;

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $this->id);

                foreach (static::$columns as $column) {
                    $stmt->bindValue($column, $this->$column);
                }

                $stmt->execute();
            }
            return true;
        }
        return false;
    }

    public function destroy()
    {
        $table = static::$table;

        $sql = <<<SQL
            DELETE FROM {$table} WHERE id = :id;
        SQL;

        $pdo = Database::getDatabaseConn();

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $this->id);

        $stmt->execute();

        return ($stmt->rowCount() != 0);
    }

    public static function findById(int $id): static|null
    {
        $pdo = Database::getDatabaseConn();

        $attributes = implode(', ', static::$columns);
        $table = static::$table;

        $sql = <<<SQL
            SELECT id, {$attributes} FROM {$table} WHERE id = :id;
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return null;
        }

        $row = $stmt->fetch();

        return new static($row);
    }

    public static function all(): array
    {
        $models = [];

        $attributes = implode(', ', static::$columns);
        $table = static::$table;

        $sql = <<<SQL
            SELECT id, {$attributes} FROM {$table};
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $resp = $stmt->fetchAll();

        foreach ($resp as $row) {
            $models[] = new static($row);
        }

        return $models;
    }

    public static function paginate(int $page = 1, int $per_page = 10): Paginator
    {
        return new Paginator(
            class: static::class,
            page: $page,
            per_page: $per_page,
            table: static::$table,
            attributes: static::$columns
        );
    }

    public static function where($conditions)
    {
        $table = static::$table;
        $attributes = implode(', ', static::$columns);

        $sql = <<<SQL
            SELECT id, {$attributes} FROM {$table} WHERE 
        SQL;

        $sqlConditions = array_map(function ($column) {
            return "{$column} = :{$column}";
        }, array_keys($conditions));

        $sql .= implode(' AND ', $sqlConditions);

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);

        foreach ($conditions as $column => $value) {
            $stmt->bindValue($column, $value);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll();

        $models = [];
        foreach ($rows as $row) {
            $models[] = new static($row);
        }
        return $models;
    }

    public static function findBy($conditions)
    {
        $resp = self::where($conditions);
        if (isset($resp[0]))
            return $resp[0];

        return null;
    }
}
