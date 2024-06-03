<?php

namespace Core\Database\ActiveRecord;

use Core\Database\Database;
use PDO;

class BelongsToMany
{
    public function __construct(
        private Model  $model,
        private string $related,
        private string $pivot_table,
        private string $from_foreign_key,
        private string $to_foreign_key,
    ) {
    }

    public function get()
    {
        $sql = <<<SQL
            SELECT problems.id, problems.title 
            FROM problems, users, {$this->pivot_table}
            WHERE problems.id = {$this->pivot_table}.{$this->to_foreign_key} and
                    users.id = {$this->pivot_table}.{$this->from_foreign_key} and
                    users.id = {$this->model->id}
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        dd($rows);
    }

    public function count()
    {
        $sql = <<<SQL
            SELECT count(problems.id) as total
            FROM problems, users, {$this->pivot_table}
            WHERE problems.id = {$this->pivot_table}.{$this->to_foreign_key} and
                    users.id = {$this->pivot_table}.{$this->from_foreign_key} and
                    users.id = {$this->model->id}
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows[0]['total'];
    }
}
