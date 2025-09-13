<?php

namespace Core\Database\QueryBuilder;

interface QueryBuilderContract
{
    public function select(string $table, array $fields): QueryBuilderContract;
    public function insert(string $table, array $data): QueryBuilderContract;
    public function update(string $table, array $data): QueryBuilderContract;
    public function delete(string $table): QueryBuilderContract;
    public function where(string|array $field, mixed $value = null, string $operator = '=', string $boolean = 'AND'): QueryBuilderContract;
    public function orWhere(string|array $field, mixed $value = null, string $operator = '='): QueryBuilderContract;
    public function orderBy(string $field, string $direction = 'ASC'): QueryBuilderContract;
    public function groupBy(string $field): QueryBuilderContract;
    public function having(string $field, mixed $value, string $operator = '='): QueryBuilderContract;
    public function limit(int $start, int $offset): QueryBuilderContract;
    public function getSQL(): string;
    public function getParams(): array;
}
