<?php

namespace Core\Database\ActiveRecord;

use Core\Database\Database;

class BelongsTo
{
    public function __construct(
        private Model $model,
        private string $related,
        private string $foreignKey
    ) {
    }

    public function get(): ?Model
    {
        $foreignKeyValue = $this->model->{$this->foreignKey};
        
        if (!$foreignKeyValue) {
            return null;
        }

        $row = Database::table($this->related::table())
            ->selectColumns(array_merge(['id'], $this->related::columns()))
            ->where('id', $foreignKeyValue)
            ->first();
            
        return $row ? new $this->related($row) : null;
    }
}
