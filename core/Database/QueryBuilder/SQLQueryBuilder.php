<?php

declare(strict_types=1);

namespace Core\Database\QueryBuilder;

use Core\Database\Database;
use Exception;
use PDO;
use stdClass;

class SQLQueryBuilder implements QueryBuilderContract
{
  protected stdClass $query;
  protected array $params = [];
  protected int $paramCounter = 0;
  protected ?PDO $connection = null;

  public function __construct(?PDO $connection = null)
  {
    $this->connection = $connection;
    $this->reset();
  }

  protected function reset(): void
  {
    $this->query = new stdClass();
    $this->params = [];
    $this->paramCounter = 0;
  }

  /**
   * Laravel style table method
   */
  public function table(string $table): static
  {
    $this->reset();
    $this->query->table = $table;
    $this->query->type = 'select';
    return $this;
  }

  /**
   * Laravel style select method
   */
  public function selectColumns($columns = ['*']): static
  {
    if (is_string($columns)) {
      $columns = [$columns];
    }
    $this->query->select = $columns;
    $this->query->type = 'select';
    return $this;
  }

  /**
   * @param string $table
   * @param array $fields
   * @return $this
   */
  public function select(string $table, array $fields): static
  {
    $this->reset();
    $this->query->base = "SELECT " . implode(", ", $fields) . " FROM " . $table;
    $this->query->type = 'select';
    return $this;
  }

  /**
   * Insert method that supports both patterns:
   * - insert($table, $data) for direct usage
   * - insert($data) for fluent usage after table() call
   * @param string|array $tableOrData
   * @param array|null $data
   * @return $this|int
   */
  public function insert(string|array $tableOrData, array $data = null): static|int
  {
    // If first parameter is array, assume fluent usage: Database::table('users')->insert([...])
    if (is_array($tableOrData)) {
      return $this->insertData($tableOrData);
    }
    
    // If first parameter is string, assume direct usage: insert('users', [...])
    $this->reset();
    $columns = implode(", ", array_keys($data));
    $placeholders = [];
    foreach ($data as $column => $value) {
      $param = $this->nextParam($column);
      $placeholders[] = $param;
      $this->params[$param] = $value;
    }
    $this->query->base = "INSERT INTO $tableOrData ($columns) VALUES (" . implode(", ", $placeholders) . ")";
    $this->query->type = 'insert';
    return $this;
  }

  /**
   * Update method that supports both patterns:
   * - update($table, $data) for direct usage
   * - update($data) for fluent usage after table() call
   * @param string|array $tableOrData
   * @param array|null $data
   * @return $this|int
   */
  public function update(string|array $tableOrData, array $data = null): static|int
  {
    // If first parameter is array, assume fluent usage: Database::table('users')->update([...])
    if (is_array($tableOrData)) {
      return $this->updateData($tableOrData);
    }
    
    // If first parameter is string, assume direct usage: update('users', [...])
    $this->reset();
    $sets = [];
    foreach ($data as $column => $value) {
      $param = $this->nextParam($column);
      $sets[] = "$column = $param";
      $this->params[$param] = $value;
    }
    $this->query->base = "UPDATE $tableOrData SET " . implode(", ", $sets);
    $this->query->type = 'update';
    return $this;
  }

  /**
   * Delete method that supports both patterns:
   * - delete($table) for direct usage
   * - delete() for fluent usage after table() call
   * @param string|null $table
   * @return $this|int
   */
  public function delete(string $table = null): static|int
  {
    // If no parameter, assume fluent usage: Database::table('users')->delete()
    if ($table === null) {
      return $this->deleteData();
    }
    
    // If parameter provided, assume direct usage: delete('users')
    $this->reset();
    $this->query->base = "DELETE FROM $table";
    $this->query->type = 'delete';
    return $this;
  }

  /**
   * Add a WHERE condition (supports AND/OR, operators, and parameter binding)
   * Laravel style: where($field, $value) or where($field, $operator, $value)
   * @param string|array $field
   * @param mixed $operator
   * @param mixed $value
   * @param string $boolean
   * @return $this
   */
  public function where(string|array $field, mixed $operator = null, mixed $value = null, string $boolean = 'AND'): static
  {
    if (!in_array($this->query->type ?? 'select', ['select', 'update', 'delete'])) {
      throw new Exception("WHERE can only be added to SELECT, UPDATE OR DELETE");
    }
    if (!isset($this->query->where)) {
      $this->query->where = [];
    }
    if (is_array($field)) {
      foreach ($field as $k => $v) {
        $this->where($k, '=', $v, $boolean);
      }
    } else {
      if ($value === null) {
        $actualOperator = '=';
        $actualValue = $operator;
      } else {
        $actualOperator = $operator;
        $actualValue = $value;
      }
      
      $param = $this->nextParam($field);
      $clause = "$field $actualOperator $param";
      $this->query->where[] = [$clause, $boolean];
      $this->params[$param] = $actualValue;
    }
    return $this;
  }

  /**
   * Add an OR WHERE condition
   * @throws Exception
   */
  public function orWhere(string|array $field, mixed $operator = null, mixed $value = null): static
  {
    return $this->where($field, $operator, $value, 'OR');
  }

  /**
   * @param string $field
   * @param string $direction
   * @return $this
   */
  public function orderBy(string $field, string $direction = 'ASC'): static
  {
    $this->query->orderBy[] = "$field $direction";
    return $this;
  }

  /**
   * @param string $field
   * @return $this
   */
  public function groupBy(string $field): static
  {
    $this->query->groupBy[] = $field;
    return $this;
  }

  /**
   * @param string $field
   * @param mixed $value
   * @param string $operator
   * @return $this
   */
  public function having(string $field, mixed $value, string $operator = '='): static
  {
    if (!isset($this->query->having)) {
      $this->query->having = [];
    }
    $param = $this->nextParam($field);
    $this->query->having[] = "$field $operator $param";
    $this->params[$param] = $value;
    return $this;
  }

  /**
   * @return string
   */
  public function getSQL(): string
  {
    $sql = '';
    
    if (($this->query->type ?? 'select') === 'select' && isset($this->query->table)) {
      $select = implode(", ", $this->query->select ?? ['*']);
      $distinct = isset($this->query->distinct) && $this->query->distinct ? 'DISTINCT ' : '';
      $sql = "SELECT $distinct$select FROM " . $this->query->table;
      
      if (!empty($this->query->joins)) {
        $sql .= ' ' . implode(' ', $this->query->joins);
      }
    } else {
      $sql = $this->query->base ?? '';
    }
    
    if (!empty($this->query->where)) {
      $clauses = [];
      foreach ($this->query->where as $i => [$clause, $boolean]) {
        $clauses[] = ($i > 0 ? $boolean . ' ' : '') . $clause;
      }
      $sql .= ' WHERE ' . implode(' ', $clauses);
    }
    if (!empty($this->query->groupBy)) {
      $sql .= ' GROUP BY ' . implode(', ', $this->query->groupBy);
    }
    if (!empty($this->query->having)) {
      $sql .= ' HAVING ' . implode(' AND ', $this->query->having);
    }
    if (!empty($this->query->orderBy)) {
      $sql .= ' ORDER BY ' . implode(', ', $this->query->orderBy);
    }
    if (isset($this->query->limit)) {
      if (is_string($this->query->limit)) {
        $sql .= $this->query->limit;
      } else {
        $sql .= ' LIMIT ' . $this->query->limit;
      }
    }
    if (isset($this->query->offset)) {
      $sql .= ' OFFSET ' . $this->query->offset;
    }
    
    return $sql . ";";
  }

  /**
   * @return array
   */
  public function getParams(): array
  {
    return $this->params;
  }

  /**
   * @return string
   */
  protected function nextParam(string $field): string
  {
    return ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $field) . '_' . (++$this->paramCounter);
  }

  /**
   * Execute the query and get results (Laravel style)
   */
  public function get(): array
  {
    $connection = $this->connection ?? Database::getDatabaseConn();
    $sql = $this->getSQL();
    $stmt = $connection->prepare($sql);
    $stmt->execute($this->params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get first result (Laravel style)
   */
  public function first(): ?array
  {
    $originalLimit = $this->query->limit ?? null;
    $this->query->limit = 1;
    
    $result = $this->get();
    
    $this->query->limit = $originalLimit;
    
    return $result[0] ?? null;
  }

  /**
   * Find by ID (Laravel style)
   */
  public function find(int $id): ?array
  {
    return $this->where('id', $id)->first();
  }

  /**
   * Count results (Laravel style)
   */
  public function count(string $column = '*'): int
  {
    return $this->aggregate('COUNT', $column);
  }

  /**
   * Get maximum value (Laravel style)
   */
  public function max(string $column): mixed
  {
    return $this->aggregate('MAX', $column);
  }

  /**
   * Get minimum value (Laravel style)
   */
  public function min(string $column): mixed
  {
    return $this->aggregate('MIN', $column);
  }

  /**
   * Get average value (Laravel style)
   */
  public function avg(string $column): mixed
  {
    return $this->aggregate('AVG', $column);
  }

  /**
   * Get sum of values (Laravel style)
   */
  public function sum(string $column): mixed
  {
    return $this->aggregate('SUM', $column);
  }

  /**
   * Execute aggregate function
   */
  protected function aggregate(string $function, string $column): mixed
  {
    $connection = $this->connection ?? Database::getDatabaseConn();
    $originalSelect = $this->query->select ?? ['*'];
    $originalType = $this->query->type ?? 'select';
    
    $this->query->type = 'select';
    $this->query->select = ["$function($column) as aggregate"];
    
    $sql = $this->getSQL();
    $stmt = $connection->prepare($sql);
    $stmt->execute($this->params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $this->query->select = $originalSelect;
    $this->query->type = $originalType;
    
    return $result['aggregate'] ?? null;
  }

  /**
   * Check if records exist (Laravel style)
   */
  public function exists(): bool
  {
    return $this->count() > 0;
  }

  /**
   * Check if records don't exist (Laravel style)
   */
  public function doesntExist(): bool
  {
    return !$this->exists();
  }

  /**
   * Get a single column value (Laravel style)
   */
  public function value(string $column): mixed
  {
    $result = $this->selectColumns([$column])->first();
    return $result[$column] ?? null;
  }

  /**
   * Get an array of column values (Laravel style)
   */
  public function pluck(string $column, string $key = null): array
  {
    $columns = $key ? [$column, $key] : [$column];
    $results = $this->selectColumns($columns)->get();
    
    if (!$key) {
      return array_column($results, $column);
    }
    
    $plucked = [];
    foreach ($results as $row) {
      $plucked[$row[$key]] = $row[$column];
    }
    return $plucked;
  }

  /**
   * Laravel style limit
   */
  public function take(int $limit): static
  {
    $this->query->limit = $limit;
    return $this;
  }

  /**
   * Laravel style offset
   */
  public function skip(int $offset): static
  {
    $this->query->offset = $offset;
    return $this;
  }

  /**
   * Laravel style latest
   */
  public function latest(string $column = 'created_at'): static
  {
    return $this->orderBy($column, 'DESC');
  }

  /**
   * Laravel style oldest
   */
  public function oldest(string $column = 'created_at'): static
  {
    return $this->orderBy($column, 'ASC');
  }

  /**
   * Order by descending (Laravel style)
   */
  public function orderByDesc(string $column): static
  {
    return $this->orderBy($column, 'DESC');
  }

  /**
   * Get first result or fail (Laravel style)
   */
  public function firstOrFail(): array
  {
    $result = $this->first();
    if ($result === null) {
      throw new Exception("No records found");
    }
    return $result;
  }

  /**
   * Add distinct to query (Laravel style)
   */
  public function distinct(): static
  {
    if (!isset($this->query->distinct)) {
      $this->query->distinct = true;
    }
    return $this;
  }

  /**
   * Add select columns to existing query (Laravel style)
   */
  public function addSelect(array $columns): static
  {
    if (!isset($this->query->select)) {
      $this->query->select = ['*'];
    }
    
    $this->query->select = array_merge($this->query->select, $columns);
    return $this;
  }

  /**
   * OR WHERE IN clause (Laravel style)
   */
  public function orWhereIn(string $field, array $values): static
  {
    return $this->whereIn($field, $values, 'OR');
  }

  /**
   * OR WHERE NOT IN clause (Laravel style)
   */
  public function orWhereNotIn(string $field, array $values): static
  {
    return $this->whereNotIn($field, $values, 'OR');
  }

  /**
   * OR WHERE NULL clause (Laravel style)
   */
  public function orWhereNull(string $field): static
  {
    return $this->whereNull($field, 'OR');
  }

  /**
   * OR WHERE NOT NULL clause (Laravel style)
   */
  public function orWhereNotNull(string $field): static
  {
    return $this->whereNotNull($field, 'OR');
  }

  /**
   * OR WHERE BETWEEN clause (Laravel style)
   */
  public function orWhereBetween(string $field, array $values): static
  {
    return $this->whereBetween($field, $values, 'OR');
  }

  /**
   * OR WHERE NOT BETWEEN clause (Laravel style)
   */
  public function orWhereNotBetween(string $field, array $values): static
  {
    return $this->whereNotBetween($field, $values, 'OR');
  }

  /**
   * WHERE LIKE clause (Laravel style)
   */
  public function whereLike(string $field, string $value, string $boolean = 'AND'): static
  {
    return $this->where($field, 'LIKE', $value, $boolean);
  }

  /**
   * OR WHERE LIKE clause (Laravel style)
   */
  public function orWhereLike(string $field, string $value): static
  {
    return $this->whereLike($field, $value, 'OR');
  }

  /**
   * Laravel style limit (standard)
   */
  public function limit(int $limit): static
  {
    $this->query->limit = $limit;
    return $this;
  }

  /**
   * Laravel style offset (standard)
   */
  public function offset(int $offset): static
  {
    $this->query->offset = $offset;
    return $this;
  }

  /**
   * Random ordering (Laravel style)
   */
  public function inRandomOrder(): static
  {
    return $this->orderBy('RAND()');
  }

  /**
   * Conditional WHERE clause (Laravel style)
   */
  public function when(mixed $condition, callable $callback): static
  {
    if ($condition) {
      $callback($this);
    }
    return $this;
  }

  /**
   * WHERE IN clause (Laravel style)
   */
  public function whereIn(string $field, array $values, string $boolean = 'AND'): static
  {
    if (empty($values)) {
      return $this;
    }

    $placeholders = [];
    foreach ($values as $value) {
      $param = $this->nextParam($field);
      $placeholders[] = $param;
      $this->params[$param] = $value;
    }

    if (!isset($this->query->where)) {
      $this->query->where = [];
    }

    $clause = "$field IN (" . implode(', ', $placeholders) . ")";
    $this->query->where[] = [$clause, $boolean];

    return $this;
  }

  /**
   * WHERE NOT IN clause (Laravel style)
   */
  public function whereNotIn(string $field, array $values, string $boolean = 'AND'): static
  {
    if (empty($values)) {
      return $this;
    }

    $placeholders = [];
    foreach ($values as $value) {
      $param = $this->nextParam($field);
      $placeholders[] = $param;
      $this->params[$param] = $value;
    }

    if (!isset($this->query->where)) {
      $this->query->where = [];
    }

    $clause = "$field NOT IN (" . implode(', ', $placeholders) . ")";
    $this->query->where[] = [$clause, $boolean];

    return $this;
  }

  /**
   * WHERE NULL clause (Laravel style)
   */
  public function whereNull(string $field, string $boolean = 'AND'): static
  {
    if (!isset($this->query->where)) {
      $this->query->where = [];
    }

    $clause = "$field IS NULL";
    $this->query->where[] = [$clause, $boolean];

    return $this;
  }

  /**
   * WHERE NOT NULL clause (Laravel style)
   */
  public function whereNotNull(string $field, string $boolean = 'AND'): static
  {
    if (!isset($this->query->where)) {
      $this->query->where = [];
    }

    $clause = "$field IS NOT NULL";
    $this->query->where[] = [$clause, $boolean];

    return $this;
  }

  /**
   * WHERE BETWEEN clause (Laravel style)
   */
  public function whereBetween(string $field, array $values, string $boolean = 'AND'): static
  {
    if (count($values) !== 2) {
      throw new Exception("whereBetween requires exactly 2 values");
    }

    $param1 = $this->nextParam($field);
    $param2 = $this->nextParam($field);
    $this->params[$param1] = $values[0];
    $this->params[$param2] = $values[1];

    if (!isset($this->query->where)) {
      $this->query->where = [];
    }

    $clause = "$field BETWEEN $param1 AND $param2";
    $this->query->where[] = [$clause, $boolean];

    return $this;
  }

  /**
   * WHERE NOT BETWEEN clause (Laravel style)
   */
  public function whereNotBetween(string $field, array $values, string $boolean = 'AND'): static
  {
    if (count($values) !== 2) {
      throw new Exception("whereNotBetween requires exactly 2 values");
    }

    $param1 = $this->nextParam($field);
    $param2 = $this->nextParam($field);
    $this->params[$param1] = $values[0];
    $this->params[$param2] = $values[1];

    if (!isset($this->query->where)) {
      $this->query->where = [];
    }

    $clause = "$field NOT BETWEEN $param1 AND $param2";
    $this->query->where[] = [$clause, $boolean];

    return $this;
  }

  /**
   * WHERE DATE clause (Laravel style)
   */
  public function whereDate(string $field, string $operator, string $value = null): static
  {
    if ($value === null) {
      $value = $operator;
      $operator = '=';
    }

    $param = $this->nextParam($field);
    $this->params[$param] = $value;

    if (!isset($this->query->where)) {
      $this->query->where = [];
    }

    $clause = "DATE($field) $operator $param";
    $this->query->where[] = [$clause, 'AND'];

    return $this;
  }

  /**
   * JOIN clause (Laravel style)
   */
  public function join(string $table, string $first, string $operator = null, string $second = null, string $type = 'INNER'): static
  {
    if (!isset($this->query->joins)) {
      $this->query->joins = [];
    }

    if (func_num_args() === 3) {
      $second = $operator;
      $operator = '=';
    }

    $this->query->joins[] = "$type JOIN $table ON $first $operator $second";
    return $this;
  }

  /**
   * LEFT JOIN clause
   */
  public function leftJoin(string $table, string $first, string $operator = null, string $second = null): static
  {
    return $this->join($table, $first, $operator, $second, 'LEFT');
  }

  /**
   * Laravel style insert method
   */
  public function insertData(array $data): int
  {
    if (!isset($this->query->table)) {
      throw new Exception("Table must be set before insert");
    }

    $connection = $this->connection ?? Database::getDatabaseConn();
    
    $columns = implode(", ", array_keys($data));
    $placeholders = [];
    $params = [];
    
    foreach ($data as $column => $value) {
      $param = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $column) . '_' . (++$this->paramCounter);
      $placeholders[] = $param;
      $params[$param] = $value;
    }
    
    $sql = "INSERT INTO {$this->query->table} ($columns) VALUES (" . implode(", ", $placeholders) . ")";
    $stmt = $connection->prepare($sql);
    $stmt->execute($params);
    
    return (int) $connection->lastInsertId();
  }

  /**
   * Laravel style update method
   */
  public function updateData(array $data): int
  {
    if (!isset($this->query->table)) {
      throw new Exception("Table must be set before update");
    }

    $this->query->type = 'update';

    $connection = $this->connection ?? Database::getDatabaseConn();
    
    $sets = [];
    $params = $this->params;
    
    foreach ($data as $column => $value) {
      $param = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $column) . '_update_' . (++$this->paramCounter);
      $sets[] = "$column = $param";
      $params[$param] = $value;
    }
    
    $sql = "UPDATE {$this->query->table} SET " . implode(", ", $sets);
    
    if (!empty($this->query->where)) {
      $clauses = [];
      foreach ($this->query->where as $i => [$clause, $boolean]) {
        $clauses[] = ($i > 0 ? $boolean . ' ' : '') . $clause;
      }
      $sql .= ' WHERE ' . implode(' ', $clauses);
    }
    
    $stmt = $connection->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->rowCount();
  }

  /**
   * Laravel style delete method
   */
  public function deleteData(): int
  {
    if (!isset($this->query->table)) {
      throw new Exception("Table must be set before delete");
    }

    $this->query->type = 'delete';

    $connection = $this->connection ?? Database::getDatabaseConn();
    
    $sql = "DELETE FROM {$this->query->table}";
    
    if (!empty($this->query->where)) {
      $clauses = [];
      foreach ($this->query->where as $i => [$clause, $boolean]) {
        $clauses[] = ($i > 0 ? $boolean . ' ' : '') . $clause;
      }
      $sql .= ' WHERE ' . implode(' ', $clauses);
    }
    
    $stmt = $connection->prepare($sql);
    $stmt->execute($this->params);
    
    return $stmt->rowCount();
  }

  /**
   * Execute query (for insert, update, delete)
   */
  public function execute()
  {
    $connection = $this->connection ?? Database::getDatabaseConn();
    $sql = $this->getSQL();
    $stmt = $connection->prepare($sql);
    $result = $stmt->execute($this->params);
    
    if (($this->query->type ?? '') === 'insert') {
      return (int) $connection->lastInsertId();
    }
    
    if (in_array($this->query->type ?? '', ['update', 'delete'])) {
      return $stmt->rowCount();
    }
    
    return $result;
  }

  /**
   * Get the connection
   */
  public function getConnection(): PDO
  {
    return $this->connection ?? Database::getDatabaseConn();
  }
}
