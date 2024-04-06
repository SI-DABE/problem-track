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
            $this->id = count(file(self::DB_PATH));
            file_put_contents(self::DB_PATH, $this->title . PHP_EOL, FILE_APPEND);
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
}
