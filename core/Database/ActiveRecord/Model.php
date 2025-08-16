<?php

namespace Core\Database\ActiveRecord;

use Core\Database\Database;
use Core\Database\QueryBuilder\SQLQueryBuilder;
use Lib\Paginator;
use Lib\StringUtils;
use PDO;
use ReflectionMethod;

/**
 * Class Model
 * @package Core\Database\ActiveRecord
 * @property int $id
 */
abstract class Model
{
    /** @var array<string, string> */
    protected array $errors = [];
    protected ?int $id = null;

    /** @var array<string, mixed> */
    private array $attributes = [];

    protected static string $table = '';
    /** @var array<int, string> */
    protected static array $columns = [];

    /**
     * @param array<string, mixed> $params
     */
    public function __construct($params = [])
    {
        // Initialize attributes with null from database columns
        foreach (static::$columns as $column) {
            $this->attributes[$column] = null;
        }

        foreach ($params as $property => $value) {
            $this->__set($property, $value);
        }
    }

    /* ------------------- MAGIC METHODS ------------------- */
    private static function makeQueryBuilder(): SQLQueryBuilder
    {
       return Database::getQueryBuilder();
    }

    public function __get(string $property): mixed
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        if (array_key_exists($property, $this->attributes)) {
            return $this->attributes[$property];
        }

        $method = StringUtils::lowerSnakeToCamelCase($property);
        if (method_exists($this, $method)) {
            $reflectionMethod = new ReflectionMethod($this, $method);
            $returnType = $reflectionMethod->getReturnType();

            $allowedTypes = [
                'Core\Database\ActiveRecord\BelongsTo',
                'Core\Database\ActiveRecord\HasMany',
                'Core\Database\ActiveRecord\BelongsToMany'
            ];

            if ($returnType !== null && in_array($returnType->getName(), $allowedTypes)) {
                return $this->$method()->get();
            }
        }

        throw new \Exception("Property {$property} not found in " . static::class);
    }

    public function __set(string $property, mixed $value): void
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
            return;
        }

        if (array_key_exists($property, $this->attributes)) {
            $this->attributes[$property] = $value;
            return;
        }

        throw new \Exception("Property {$property} not found in " . static::class);
    }

    public static function table(): string
    {
        return static::$table;
    }

    /**
     * @return array<int, string>
     */
    public static function columns(): array
    {
        return static::$columns;
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
        return !empty($this->errors);
    }

    public function errors(string $index = null): string | null
    {
        if (isset($this->errors[$index])) {
            return $this->errors[$index];
        }

        return null;
    }

    public function addError(string $index, string $value): void
    {
        $this->errors[$index] = $value;
    }

    public function validates(): void {}

    /* ------------------- DATABASE METHODS ------------------- */
    public function save(): bool
    {
        if ($this->isValid()) {
            $pdo = Database::getDatabaseConn();
            if ($this->newRecord()) {
                $table = static::$table;
                $attributes = implode(', ', static::$columns);
                $values = ':' . implode(', :', static::$columns);

                $data = [];
                foreach (static::$columns as $column) {
                    $data[$column] = $this->$column;
                }

                $builder = Database::getQueryBuilder();
                $sql = $builder
                  ->insert($table, $data)
                  ->getSQL();
                $params = $builder->getParams();
                $stmt = $pdo->prepare($sql);
                foreach ($params as $param => $value) {
                    $stmt->bindValue($param, $value);
                }

                $stmt->execute();

                $this->id = (int) $pdo->lastInsertId();
            } else {
                $table = static::$table;

                $data = [];
                foreach (static::$columns as $column) {
                    $data[$column] = $this->$column;
                }

                $builder = Database::getQueryBuilder();
                $sql = $builder
                  ->update($table, $data)
                  ->where(['id' => $this->id])
                  ->getSQL();
                $params = $builder->getParams();
                $stmt = $pdo->prepare($sql);
                foreach ($params as $param => $value) {
                    $stmt->bindValue($param, $value);
                }

                $stmt->execute();
            }
            return true;
        }
        return false;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(array $data): bool
    {
        $table = static::$table;

        $data = [];
        foreach (static::$columns as $column) {
          $data[$column] = $this->$column;
        }

        $builder = Database::getQueryBuilder();
        $sql = $builder
          ->update($table, $data)
          ->where(['id' => $this->id])
          ->getSQL();
        $params = $builder->getParams();
        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();
        return ($stmt->rowCount() !== 0);
    }

    public function destroy(): bool
    {
        $table = static::$table;

        $builder = Database::getQueryBuilder();
        $sql = $builder
          ->delete($table)
          ->where(['id' => $this->id])
          ->getSQL();
        $params = $builder->getParams();
        $pdo = Database::getDatabaseConn();

        $stmt = $pdo->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();

        return ($stmt->rowCount() != 0);
    }

    public static function findById(int $id): static|null
    {
        $pdo = Database::getDatabaseConn();
        $table = static::$table;
        $builder = static::makeQueryBuilder();
        $sql = $builder
          ->select($table, array_merge(['id'], static::$columns))
          ->where(['id' => $id])
          ->getSQL();
        $params = $builder->getParams();
        $stmt = $pdo->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return null;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return new static($row);
    }

    /**
     * @return array<static>
     */
    public static function all(): array
    {
        $models = [];

        $attributes = implode(', ', static::$columns);
        $table = static::$table;
        $builder = static::makeQueryBuilder();
        $sql = $builder
          ->select($table, array_merge(['id'], static::$columns))
          ->getSQL();
        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $resp = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resp as $row) {
            $models[] = new static($row);
        }

        return $models;
    }

    public static function paginate(int $page = 1, int $per_page = 10, string $route = null): Paginator
    {
        return new Paginator(
            class: static::class,
            page: $page,
            per_page: $per_page,
            table: static::$table,
            attributes: static::$columns,
            route: $route
        );
    }

    /**
     * @param array<string, mixed> $conditions
     * @return array<static>
     */
    public static function where(array $conditions): array
    {
        $table = static::$table;
        $builder = static::makeQueryBuilder();
        $sql = $builder
          ->select($table, array_merge(['id'], static::$columns))
          ->where($conditions)
          ->getSQL();
        $params = $builder->getParams();
        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $models = [];
        foreach ($rows as $row) {
            $models[] = new static($row);
        }
        return $models;
    }

    /**
     * @param array<string, mixed> $conditions
     */
    public static function findBy($conditions): ?static
    {
        $resp = self::where($conditions);
        if (isset($resp[0]))
            return $resp[0];

        return null;
    }

    /**
     * @param array<string, mixed> $conditions
     */
    public static function exists($conditions): bool
    {
        $resp = self::where($conditions);
        return !empty($resp);
    }

    /* ------------------- RELATIONSHIPS METHODS ------------------- */

    public function belongsTo(string $related, string $foreignKey): BelongsTo
    {
        return new BelongsTo($this, $related, $foreignKey);
    }

    public function hasMany(string $related, string $foreignKey): HasMany
    {
        return new HasMany($this, $related, $foreignKey);
    }

    public function BelongsToMany(string $related, string $pivot_table, string $from_foreign_key, string $to_foreign_key): BelongsToMany
    {
        return new BelongsToMany($this, $related, $pivot_table, $from_foreign_key, $to_foreign_key);
    }
}
