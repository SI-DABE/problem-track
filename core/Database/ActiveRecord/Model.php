<?php

namespace Core\Database\ActiveRecord;

use Core\Database\Database;
use Lib\Paginator;
use ReflectionClass;

abstract class Model
{
    /** @var array<string, string> */
    private array $errors = [];
    protected ?int $id = null;

    private array $attributes = [];

    protected static $table = null;
    protected static $columns = [];

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
}
