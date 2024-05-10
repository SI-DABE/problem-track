<?php

namespace Core\Database;

use Core\Constants\Constants;
use PDO;

class Database
{
    public static function getDatabaseConn(): PDO
    {
        $user = $_ENV['DB_USERNAME'];
        $pwd  = $_ENV['DB_PASSWORD'];
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $db   = $_ENV['DB_DATABASE'];

        $pdo = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db, $user, $pwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function getConn(): PDO
    {
        $user = $_ENV['DB_USERNAME'];
        $pwd  = $_ENV['DB_PASSWORD'];
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];

        $pdo = new PDO('mysql:host=' . $host . ';port=' . $port, $user, $pwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function create(): void
    {
        $sql = 'CREATE DATABASE IF NOT EXISTS ' . $_ENV['DB_DATABASE'] . ';';
        self::getConn()->exec($sql);
    }

    public static function drop(): void
    {
        $sql = 'DROP DATABASE IF EXISTS ' . $_ENV['DB_DATABASE'] . ';';
        self::getConn()->exec($sql);
    }

    public static function migrate(): void
    {
        $sql = file_get_contents(Constants::databasePath()->join('schema.sql'));
        self::getDatabaseConn()->exec($sql);
    }
}
