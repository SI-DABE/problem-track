<?php

class Problem
{
    const DB_PATH = '/var/www/database/problems.txt';

    private array $errors = [];

    public function __construct(
        private string $title = '',
        private int $id = -1
    ) {
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTitle(string $title)
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
                $this->id = count(file(self::DB_PATH));
                file_put_contents(self::DB_PATH, $this->title . PHP_EOL, FILE_APPEND);
            } else {
                $problems = file(self::DB_PATH, FILE_IGNORE_NEW_LINES);
                $problems[$this->id] = $this->title;

                $data = implode(PHP_EOL, $problems);
                file_put_contents(self::DB_PATH, $data . PHP_EOL);
            }
            return true;
        }
        return false;
    }

    public function isValid(): bool
    {
        $this->errors = [];

        if (empty($this->title))
            $this->errors['title'] = 'não pode ser vazio!';

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

    public function errors($index)
    {
        if (isset($this->errors[$index]))
            return $this->errors[$index];

        return null;
    }

    public static function all(): array
    {
        $problems = file(self::DB_PATH, FILE_IGNORE_NEW_LINES);

        return array_map(function ($line, $title) {
            return new Problem(id: $line, title: $title);
        }, array_keys($problems), $problems);
    }

    public static function findById(int $id): Problem|null
    {
        $problems = self::all();

        foreach ($problems as $problem) {
            if ($problem->getId() === $id)
                return $problem;
        }
        return null;
    }
}
