<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\Model;

class User extends Model
{
    protected static $table = 'users';
    protected static $columns = ['name', 'email', 'encrypted_password'];

    private ?string $password = null;
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
        if ($this->encrypted_password == null) {
            return false;
        }

        return password_verify($password, $this->encrypted_password);
    }

    public static function findByEmail(string $email): User | null
    {
        return User::findBy(['email' => $email]);
    }

    public function __set($property, $value)
    {
        parent::__set($property, $value);

        $notEmpty = $value !== null && $value !== '';
        if ($property === 'password' && $this->newRecord() && $notEmpty) {
            $this->encrypted_password = password_hash($value, PASSWORD_DEFAULT);
        }
    }
}
