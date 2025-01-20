<?php

namespace Tests\Unit\Lib\Validations;

use PHPUnit\Framework\TestCase;
use Core\Database\ActiveRecord\Model;
use Core\Database\Database;
use Lib\Validations;

class UniquenessValidationsTest extends TestCase
{
    public function setup(): void
    {
        Database::drop();
        Database::create();
        Database::exec('
            CREATE TABLE test_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(50) UNIQUE NOT NULL,
                phone VARCHAR(50)
            );
        ');
    }

    public function tearDown(): void
    {
        Database::drop();
    }

    public function test_uniqueness_true_when_no_registers(): void
    {
        $model = new class () extends Model {
            protected static string $table = 'test_users';
            protected static array $columns = ['email'];
        };

        $this->assertTrue(Validations::uniqueness('email', $model));
    }

    public function test_uniqueness_true_with_same_register(): void
    {
        $model = new class () extends Model {
            protected static string $table = 'test_users';
            protected static array $columns = ['email'];
        };

        $model->email = 'a@a.com'; // @phpstan-ignore-line
        $this->assertTrue($model->save());
        $this->assertTrue(Validations::uniqueness('email', $model));
    }

    public function test_uniqueness_update(): void
    {
        $model = new class () extends Model {
            protected static string $table = 'test_users';
            protected static array $columns = ['email'];

            public function validates(): void
            {
                Validations::uniqueness('email', $this);
            }
        };

        $model->email = 'a@a.com'; // @phpstan-ignore-line
        $this->assertTrue($model->save());
        $this->assertTrue($model->save());
    }

    public function test_uniqueness_update_with_another_email(): void
    {
        $model = new class () extends Model {
            protected static string $table = 'test_users';
            protected static array $columns = ['email'];

            public function validates(): void
            {
                Validations::uniqueness('email', $this);
            }
        };

        $model->email = 'a@a.com'; // @phpstan-ignore-line
        $this->assertTrue($model->save());

        $model->email = 'b@b.com';
        $this->assertTrue($model->save());
    }

    public function test_uniqueness_update_change_email_to_one_registered(): void
    {
        Database::exec('INSERT INTO test_users (email) values ("b@b.com")');

        $model = new class () extends Model {
            protected static string $table = 'test_users';
            protected static array $columns = ['email'];

            public function validates(): void
            {
                Validations::uniqueness('email', $this);
            }
        };

        $model->email = 'a@a.com'; // @phpstan-ignore-line
        $this->assertTrue($model->save());

        $model->email = 'b@b.com';

        $this->assertFalse($model->save());
        $this->assertFalse(Validations::uniqueness('email', $model));
    }

    public function test_uniqueness_two_fields(): void
    {
        Database::exec('INSERT INTO test_users (email, phone) values ("b@b.com", "123456789")');

        $model = new class () extends Model {
            protected static string $table = 'test_users';
            protected static array $columns = ['email', 'phone'];

            public function validates(): void
            {
                Validations::uniqueness(['email', 'phone'], $this);
            }
        };

        $model->email = 'c@c.com'; // @phpstan-ignore-line
        $model->phone = '123456789'; // @phpstan-ignore-line
        $this->assertTrue($model->save());

        $model->email = 'b@b.com';
        $model->phone = '123456789';

        $this->assertFalse($model->save());
        $this->assertFalse(Validations::uniqueness(['email', 'phone'], $model));
    }
}
