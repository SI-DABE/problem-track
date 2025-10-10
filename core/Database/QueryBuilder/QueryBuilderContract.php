<?php

namespace Core\Database\QueryBuilder;

interface QueryBuilderContract
{
    public function select(string $table, array $fields): QueryBuilderContract;
    public function insert(string|array $tableOrData, array $data = null): QueryBuilderContract|int;
    public function update(string|array $tableOrData, array $data = null): QueryBuilderContract|int;
    public function delete(string $table = null): QueryBuilderContract|int;
    public function where(string|array $field, mixed $operator = null, mixed $value = null, string $boolean = 'AND'): QueryBuilderContract;
    public function orWhere(string|array $field, mixed $operator = null, mixed $value = null): QueryBuilderContract;
    public function orderBy(string $field, string $direction = 'ASC'): QueryBuilderContract;
    public function groupBy(string $field): QueryBuilderContract;
    public function having(string $field, mixed $value, string $operator = '='): QueryBuilderContract;
    public function limit(int $limit): QueryBuilderContract;
    public function offset(int $offset): QueryBuilderContract;
    public function getSQL(): string;
    public function getParams(): array;
}
