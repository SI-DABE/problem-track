<?php

namespace Core\Database\ActiveRecord;

use Core\Database\Database;

class BelongsToMany
{
    public function __construct(
        private Model  $model,
        private string $related,
        private string $pivot_table,
        private string $from_foreign_key,
        private string $to_foreign_key,
    ) {
    }

    /**
     * @return array<Model>
     */
    public function get()
    {
        $toTable = $this->related::table();
        $columns = array_merge(['id'], $this->related::columns());
        
        // Build SELECT columns with table prefix
        $selectColumns = [];
        foreach ($columns as $column) {
            $selectColumns[] = "$toTable.$column";
        }

        $rows = Database::table($toTable)
            ->selectColumns($selectColumns)
            ->join($this->pivot_table, "$toTable.id", '=', "{$this->pivot_table}.{$this->to_foreign_key}")
            ->where("{$this->pivot_table}.{$this->from_foreign_key}", $this->model->id)
            ->get();

        return array_map(fn($row) => new $this->related($row), $rows);
    }

    public function count(): int
    {
        $toTable = $this->related::table();

        return Database::table($toTable)
            ->join($this->pivot_table, "$toTable.id", '=', "{$this->pivot_table}.{$this->to_foreign_key}")
            ->where("{$this->pivot_table}.{$this->from_foreign_key}", $this->model->id)
            ->count();
    }
}
