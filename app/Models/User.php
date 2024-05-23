<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;

class User extends Model
{
    protected static $table = 'users';
    protected static $columns = ['name', 'email', 'password'];

    private string $password_confirmation = '';

    public function validates(): void
    {
        if ($this->name === '' || $this->name === null) {
            $this->errors['name'] = 'não pode ser vazio!';
        }

        if ($this->email === '' || $this->email === null) {
            $this->errors['email'] = 'não pode ser vazio!';
        }

        if ($this->newRecord() && $this->password === '' || $this->password === null) {
            $this->errors['password'] = 'não pode ser vazio!';
        }

        if ($this->password !== $this->password_confirmation) {
            $this->errors['password'] = 'as senhas devem ser idênticas!';
        } else {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        }
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
