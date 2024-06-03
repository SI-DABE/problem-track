<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $title
 */
class Problem extends Model
{
    protected static string $table = 'problems';
    protected static array $columns = ['title', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validates(): void
    {
        Validations::notEmpty('title', $this);
    }
}
