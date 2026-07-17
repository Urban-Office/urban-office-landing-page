<?php
/**
 * Database Singleton Helper using PDO
 * Connects securely and provides helper execution methods
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}

    /**
     * Retrieve the active PDO connection instance (Singleton)
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // In development mode log error, in production hide technical details
                if (DEV_MODE) {
                    die("Database connection failed: " . $e->getMessage());
                } else {
                    error_log("DB Connection Failure: " . $e->getMessage());
                    die("A system error occurred. Please try again later.");
                }
            }
        }
        return self::$instance;
    }

    /**
     * Executes a safe query with prepared statements
     * Returns the PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Helper to fetch a single row from database
     */
    public static function fetch(string $sql, array $params = []): ?array {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result ? $result : null;
    }

    /**
     * Helper to fetch all rows from database
     */
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Helper to insert records and return the auto-incremented ID
     */
    public static function insert(string $sql, array $params = []): string {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $db->lastInsertId();
    }
}
