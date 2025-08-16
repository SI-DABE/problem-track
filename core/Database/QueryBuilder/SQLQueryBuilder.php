<?php

declare(strict_types=1);

namespace Core\Database\QueryBuilder;

use Exception;
use stdClass;

class SQLQueryBuilder implements QueryBuilderContract
{
  protected stdClass $query;
  protected array $params = [];
  protected int $paramCounter = 0;

  public function __construct()
  {
    $this->reset();
  }

  protected function reset(): void
  {
    $this->query = new stdClass();
    $this->params = [];
    $this->paramCounter = 0;
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
    $sql = $this->query->base;
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
      $sql .= $this->query->limit;
    }
    $sql .= ";";
    return $sql;
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
    return ':' . $field . '_' . (++$this->paramCounter);
  }
}
