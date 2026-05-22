<?php

declare(strict_types=1);

namespace JuegosTenerife\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            $envLoaded = isset($_ENV['DB_DATABASE']) ? 'yes' : 'no';
            $message = 'No se pudo conectar con la base de datos. [diag] '
                . $exception->getMessage()
                . ' | host=' . $config['host']
                . ' port=' . $config['port']
                . ' db=' . $config['database']
                . ' user=' . $config['username']
                . ' pwd_len=' . strlen((string) $config['password'])
                . ' env_loaded=' . $envLoaded;
            throw new RuntimeException($message, 0, $exception);
        }

        return self::$connection;
    }
}

