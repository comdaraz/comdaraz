<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private static int $transactionDepth = 0;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                if (env('APP_DEBUG', false)) {
                    throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
                }
                error_log("Database connection error: " . $e->getMessage());
                die("Database Connection Error. Please check server logs.");
            }
        }

        return self::$instance;
    }

    public static function query(string $sql, array $params = []): \PDOStatement {
        $db = self::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): array|false {
        return self::query($sql, $params)->fetch();
    }

    public static function beginTransaction(): bool {
        $db = self::getInstance();
        if (self::$transactionDepth === 0) {
            $result = $db->beginTransaction();
            self::$transactionDepth = 1;
            return $result;
        }
        self::$transactionDepth++;
        $db->exec("SAVEPOINT SAVEPOINT_" . self::$transactionDepth);
        return true;
    }

    public static function commit(): bool {
        $db = self::getInstance();
        if (self::$transactionDepth === 1) {
            $result = $db->commit();
            self::$transactionDepth = 0;
            return $result;
        }
        if (self::$transactionDepth > 1) {
            $savepoint = 'SAVEPOINT_' . self::$transactionDepth;
            $db->exec("RELEASE SAVEPOINT {$savepoint}");
            self::$transactionDepth--;
            return true;
        }
        return false;
    }

    public static function rollBack(): bool {
        $db = self::getInstance();
        if (self::$transactionDepth === 1) {
            $result = $db->rollBack();
            self::$transactionDepth = 0;
            return $result;
        }
        if (self::$transactionDepth > 1) {
            $savepoint = 'SAVEPOINT_' . self::$transactionDepth;
            $db->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
            self::$transactionDepth--;
            return true;
        }
        return false;
    }

    public static function inTransaction(): bool {
        return self::$transactionDepth > 0 || (self::$instance !== null && self::$instance->inTransaction());
    }

    public static function lastInsertId(): string|false {
        return self::getInstance()->lastInsertId();
    }
}
