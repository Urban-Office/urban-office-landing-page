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
            $hosts = [DB_HOST];
            if (DB_HOST === 'localhost') {
                $hosts[] = '127.0.0.1';
            } elseif (DB_HOST === '127.0.0.1') {
                $hosts[] = 'localhost';
            }

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 5,
            ];

            $lastException = null;
            foreach ($hosts as $host) {
                $dsn = "mysql:host=" . $host . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                try {
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                    break;
                } catch (PDOException $e) {
                    $lastException = $e;
                    usleep(100000); // 100ms backoff before next host/retry
                }
            }

            if (self::$instance === null) {
                error_log("DB Connection Failure: " . ($lastException ? $lastException->getMessage() : 'Unknown error'));
                
                // Detect if current request is from API
                $is_api = (strpos($_SERVER['REQUEST_URI'] ?? '', '/_api/') !== false) 
                          || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                          || !empty($_SERVER['HTTP_X_AUTOMATION_TOKEN']);

                if ($is_api) {
                    if (!headers_sent()) {
                        header('Content-Type: application/json; charset=utf-8');
                        http_response_code(500);
                    }
                    echo json_encode([
                        'ok' => false, 
                        'error' => 'Koneksi database gagal: ' . ($lastException ? $lastException->getMessage() : 'Gagal terhubung ke MySQL.')
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    exit;
                }

                if (DEV_MODE) {
                    http_response_code(500);
                    die("Database connection failed: " . ($lastException ? $lastException->getMessage() : 'Unknown'));
                } else {
                    http_response_code(500);
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
