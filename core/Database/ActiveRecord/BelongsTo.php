<?php

namespace Core\Database\ActiveRecord;

class BelongsTo
{
    public function __construct(
        private Model $model,
        private string $related,
        private string $foreignKey
    ) {
    }

    public function get(): Model
    {
        $attribute = $this->foreignKey;
        return $this->related::findBy(['id' => $this->model->$attribute]);
    }
}
