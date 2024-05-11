<?php

namespace App\Models;

use Core\Database\Database;
use Lib\Paginator;

class Problem
{
    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(
        private int $id = -1,
        private string $title = '',
    ) {
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function save(): bool
    {
        if ($this->isValid()) {
            $pdo = Database::getDatabaseConn();
            if ($this->newRecord()) {
                $sql = 'INSERT INTO problems (title) VALUES (:title);';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':title', $this->title);

                $stmt->execute();

                $this->id = (int) $pdo->lastInsertId();
            } else {
                $sql = 'UPDATE problems SET title = :title WHERE id = :id;';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':title', $this->title);
                $stmt->bindParam(':id', $this->id);

                $stmt->execute();
            }
            return true;
        }
        return false;
    }

    public function destroy(): bool
    {
        $pdo = Database::getDatabaseConn();

        $sql = 'DELETE FROM problems WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $this->id);

        $stmt->execute();

        return ($stmt->rowCount() !== 0);
    }

    public function isValid(): bool
    {
        $this->errors = [];

        if (empty($this->title)) {
            $this->errors['title'] = 'não pode ser vazio!';
        }

        return empty($this->errors);
    }

    public function newRecord(): bool
    {
        return $this->id === -1;
    }

    public function hasErrors(): bool
    {
        return empty($this->errors);
    }

    public function errors(string $index): string | null
    {
        if (isset($this->errors[$index])) {
            return $this->errors[$index];
        }

        return null;
    }

    /**
     * @return array<int, Problem>
     */
    public static function all(): array
    {
        $problems = [];

        $pdo = Database::getDatabaseConn();
        $resp = $pdo->query('SELECT id, title FROM problems;');

        foreach ($resp as $row) {
            $problems[] = new Problem(id: $row['id'], title: $row['title']);
        }

        return $problems;
    }

    public static function findById(int $id): Problem|null
    {
        $pdo = Database::getDatabaseConn();

        $sql = 'SELECT id, title FROM problems WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return null;
        }

        $row = $stmt->fetch();

        return new Problem(id: $row['id'], title: $row['title']);
    }

    public static function paginate(int $page = 1, int $per_page = 10): Paginator
    {
        return new Paginator(
            class: Problem::class,
            page: $page,
            per_page: $per_page,
            table: 'problems',
            attributes: ['title']
        );
    }
}
