<?php

namespace App\Config;

use PDO;
use PDOException;
use Flight;

class Database
{
    private static $pdo;

    public static function connect()
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        // Load config manually if not already accessible or use Flight config if registered
        $config = require __DIR__ . '/config.php';
        $db = $config['database'];

        try {
            $pdo = new PDO(
                "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8",
                $db['user'],
                $db['password']
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            self::$pdo = $pdo;
            return $pdo;

        } catch (PDOException $e) {
            die("Erreur connexion DB : " . $e->getMessage());
        }
    }
}

// Register for Flight usage
Flight::map('db', [Database::class, 'connect']);
