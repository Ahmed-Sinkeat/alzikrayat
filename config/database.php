<?php

declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $databaseName = getenv('DB_NAME') ?: 'alzikrayat';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS') ?: '';
        $socket = getenv('DB_SOCKET') ?: '';
        $dataSourceName = $socket === ''
            ? "mysql:host={$host};port={$port};dbname={$databaseName};charset=utf8mb4"
            : "mysql:unix_socket={$socket};dbname={$databaseName};charset=utf8mb4";

        try {
            self::$connection = new PDO($dataSourceName, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'The database connection could not be created. Check the local configuration.',
                0,
                $exception
            );
        }

        return self::$connection;
    }
}
