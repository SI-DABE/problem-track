<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\BelongsToMany;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $title
 * @property int $user_id
 * @property User $user
 * @property User[] $reinforced_by_users
 */
class Problem extends Model
{
    protected static string $table = 'problems';
    protected static array $columns = ['title', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reinforcedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'problem_user_reinforce', 'problem_id', 'user_id');
    }

    public function validates(): void
    {
        Validations::notEmpty('title', $this);
    }

    public function isSupportedByUser(User $user): bool
    {
        return ProblemUserReinforce::exists(['problem_id' => $this->id, 'user_id' => $user->id]);
    }
}
