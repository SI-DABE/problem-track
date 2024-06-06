<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $problem_id
 * @property int $user_id
 */
class ProblemUserReinforce extends Model
{
    protected static string $table = 'problem_user_reinforce';
    protected static array $columns = ['problem_id', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class, 'problem_id');
    }

    public function validates(): void
    {
        Validations::uniqueness(['problem_id', 'user_id'], $this);
    }
}
