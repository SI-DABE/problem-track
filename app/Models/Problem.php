<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\Model;

class Problem extends Model
{
    protected static $table = 'problems';
    protected static $columns = ['title'];

    public function validates(): void
    {
        Validations::notEmpty('title', $this);
    }
}
