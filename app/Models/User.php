<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\Model;

class User extends Model
{
    protected static $table = 'users';
    protected static $columns = ['name', 'email', 'password'];

    private ?string $password_confirmation = null;

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
        Validations::notEmpty('email', $this);

        Validations::uniqueness('email', $this);
        Validations::passwordConfirmation($this);
    }

    public function authenticate(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public static function findByEmail(string $email): User | null
    {
        return User::findBy(['email' => $email]);
    }
}
