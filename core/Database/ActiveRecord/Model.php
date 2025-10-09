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
            if ($this->newRecord()) {
                return $this->createRecord();
            } else {
                return $this->updateRecord();
            }
        }
        return false;
    }

    private function createRecord(): bool
    {
        $data = $this->getAttributesForSave();
        $id = Database::table(static::$table)->insertData($data);
        $this->id = $id;
        return true;
    }

    private function updateRecord(): bool
    {
        $data = $this->getAttributesForSave();
        $rowsAffected = Database::table(static::$table)
            ->where('id', $this->id)
            ->updateData($data);
        return $rowsAffected > 0;
    }

    private function getAttributesForSave(): array
    {
        $data = [];
        foreach (static::$columns as $column) {
            $data[$column] = $this->$column;
        }
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(array $data): bool
    {
        // Update model attributes
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->$key = $value;
            }
        }

        return $this->updateRecord();
    }

    public function destroy(): bool
    {
        $rowsAffected = Database::table(static::$table)
            ->where('id', $this->id)
            ->deleteData();
        return $rowsAffected > 0;
    }

    public static function findById(int $id): static|null
    {
        $row = Database::table(static::$table)
            ->selectColumns(array_merge(['id'], static::$columns))
            ->where('id', $id)
            ->first();

        return $row ? new static($row) : null;
    }

    /**
     * @return array<static>
     */
    public static function all(): array
    {
        $rows = Database::table(static::$table)
            ->selectColumns(array_merge(['id'], static::$columns))
            ->get();

        return array_map(fn($row) => new static($row), $rows);
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
        $builder = Database::table(static::$table)
            ->selectColumns(array_merge(['id'], static::$columns));

        foreach ($conditions as $field => $value) {
            $builder->where($field, $value);
        }

        $rows = $builder->get();
        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * @param array<string, mixed> $conditions
     */
    public static function findBy($conditions): ?static
    {
        $builder = Database::table(static::$table)
            ->selectColumns(array_merge(['id'], static::$columns));

        foreach ($conditions as $field => $value) {
            $builder->where($field, $value);
        }

        $row = $builder->first();
        return $row ? new static($row) : null;
    }

    /**
     * @param array<string, mixed> $conditions
     */
    public static function exists($conditions): bool
    {
        $builder = Database::table(static::$table);

        foreach ($conditions as $field => $value) {
            $builder->where($field, $value);
        }

        return $builder->exists();
    }

    /**
     * Create new record (Laravel style)
     * @param array<string, mixed> $data
     */
    public static function create(array $data): static
    {
        $model = new static($data);
        $model->save();
        return $model;
    }

    /**
     * Get query builder for this model (Laravel style)
     */
    public static function query(): SQLQueryBuilder
    {
        return Database::table(static::$table)
            ->selectColumns(array_merge(['id'], static::$columns));
    }

    /**
     * Find record or create if not exists (Laravel style)
     * @param array<string, mixed> $conditions
     * @param array<string, mixed> $data
     */
    public static function firstOrCreate(array $conditions, array $data = []): static
    {
        $model = self::findBy($conditions);
        if ($model) {
            return $model;
        }

        return self::create(array_merge($conditions, $data));
    }

    /**
     * Find record or fail (Laravel style)
     * @param int $id
     */
    public static function findOrFail(int $id): static
    {
        $model = self::findById($id);
        if (!$model) {
            throw new \Exception("Model with ID $id not found");
        }
        return $model;
    }

    /**
     * Get count of records (Laravel style)
     */
    public static function count(): int
    {
        return Database::table(static::$table)->count();
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
