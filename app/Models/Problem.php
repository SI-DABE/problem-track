<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;

class Problem extends Model
{
    protected static $table = 'problems';
    protected static $columns = ['title'];

    public function validates(): void
    {
        if ($this->title === '' || $this->title === null) {
            $this->errors['title'] = 'não pode ser vazio!';
        }
    }
}
