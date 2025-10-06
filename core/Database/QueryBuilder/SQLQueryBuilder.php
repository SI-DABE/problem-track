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
   * @param string $table
   * @param array $data
   * @return $this
   */
  public function insert(string $table, array $data): static
  {
    $this->reset();
    $columns = implode(", ", array_keys($data));
    $placeholders = [];
    foreach ($data as $column => $value) {
      $param = $this->nextParam($column);
      $placeholders[] = $param;
      $this->params[$param] = $value;
    }
    $this->query->base = "INSERT INTO $table ($columns) VALUES (" . implode(", ", $placeholders) . ")";
    $this->query->type = 'insert';
    return $this;
  }

  /**
   * @param string $table
   * @param array $data
   * @return $this
   */
  public function update(string $table, array $data): static
  {
    $this->reset();
    $sets = [];
    foreach ($data as $column => $value) {
      $param = $this->nextParam($column);
      $sets[] = "$column = $param";
      $this->params[$param] = $value;
    }
    $this->query->base = "UPDATE $table SET " . implode(", ", $sets);
    $this->query->type = 'update';
    return $this;
  }

  /**
   * @param string $table
   * @return $this
   */
  public function delete(string $table): static
  {
    $this->reset();
    $this->query->base = "DELETE FROM $table";
    $this->query->type = 'delete';
    return $this;
  }

  /**
   * Add a WHERE condition (supports AND/OR, operators, and parameter binding)
   * @param string|array $field
   * @param mixed $value
   * @param string $operator
   * @param string $boolean
   * @return $this
   */
  public function where(string|array $field, mixed $value = null, string $operator = '=', string $boolean = 'AND'): static
  {
    if (!in_array($this->query->type, ['select', 'update', 'delete'])) {
      throw new Exception("WHERE can only be added to SELECT, UPDATE OR DELETE");
    }
    if (!isset($this->query->where)) {
      $this->query->where = [];
    }
    if (is_array($field)) {
      foreach ($field as $k => $v) {
        $this->where($k, $v, '=', $boolean);
      }
    } else {
      $param = $this->nextParam($field);
      $clause = "$field $operator $param";
      $this->query->where[] = [$clause, $boolean];
      $this->params[$param] = $value;
    }
    return $this;
  }

  /**
   * Add an OR WHERE condition
   * @throws Exception
   */
  public function orWhere(string|array $field, mixed $value = null, string $operator = '='): static
  {
    return $this->where($field, $value, $operator, 'OR');
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
   * @param int $start
   * @param int $offset
   * @return $this
   * @throws Exception
   */
  public function limit(int $start, int $offset): static
  {
    if ($this->query->type !== 'select') {
      throw new Exception("LIMIT can only be added to SELECT");
    }
    $this->query->limit = " LIMIT $start, $offset";
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
      $sql = "SELECT $select FROM " . $this->query->table;
      
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

  // Laravel-style methods
  
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
    $connection = $this->connection ?? Database::getDatabaseConn();
    $originalSelect = $this->query->select ?? ['*'];
    $this->query->select = ["COUNT($column) as count"];
    
    $sql = $this->getSQL();
    $stmt = $connection->prepare($sql);
    $stmt->execute($this->params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $this->query->select = $originalSelect;
    
    return (int) ($result['count'] ?? 0);
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
   * Execute query (for insert, update, delete)
   */
  public function execute(): bool|int
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
