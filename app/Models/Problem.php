<?php

namespace App\Models;

use Core\Constants\Constants;

class Problem
{
    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(
        private string $title = '',
        private int $id = -1
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
            if ($this->newRecord()) {
                $this->id = file_exists(self::dbPath()) ? count(file(self::dbPath())) : 0;
                file_put_contents(self::dbPath(), $this->title . PHP_EOL, FILE_APPEND);
            } else {
                $problems = file(self::dbPath(), FILE_IGNORE_NEW_LINES);
                $problems[$this->id] = $this->title;

                $data = implode(PHP_EOL, $problems);
                file_put_contents(self::dbPath(), $data . PHP_EOL);
            }
            return true;
        }
        return false;
    }

    public function destroy(): void
    {
        $problems = file(self::dbPath(), FILE_IGNORE_NEW_LINES);
        unset($problems[$this->id]);

        $data = implode(PHP_EOL, $problems);
        file_put_contents(self::dbPath(), $data . PHP_EOL);
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
        if (!file_exists(self::dbPath())) {
            return [];
        }

        $problems = file(self::dbPath(), FILE_IGNORE_NEW_LINES);

        return array_map(function ($line, $title) {
            return new Problem(id: $line, title: $title);
        }, array_keys($problems), $problems);
    }

    public static function findById(int $id): Problem|null
    {
        $problems = self::all();

        foreach ($problems as $problem) {
            if ($problem->getId() === $id) {
                return $problem;
            }
        }
        return null;
    }

    private static function dbPath(): string
    {
        return Constants::databasePath()->join($_ENV['DB_NAME']);
    }
}
