<?php
// ============================================================
// RapidAid - Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rapidaid_db');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'RapidAid');
define('APP_URL', 'http://localhost:8000');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true);

// Session config
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'rapidaid_session');

// Upload paths
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');

// Pagination
define('ITEMS_PER_PAGE', 10);

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
            } else {
                die(json_encode(['error' => 'Database connection failed. Please try again later.']));
            }
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->conn;
    }

    // Shorthand query helper
    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    public function insert(string $table, array $data): int {
        $cols = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO $table ($cols) VALUES ($placeholders)", array_values($data));
        return (int)$this->conn->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = implode(',', array_map(fn($k) => "$k=?", array_keys($data)));
        $stmt = $this->query("UPDATE $table SET $set WHERE $where", array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int {
        return $this->query("DELETE FROM $table WHERE $where", $params)->rowCount();
    }

    public function count(string $table, string $where = '1', array $params = []): int {
        return (int)$this->fetchOne("SELECT COUNT(*) as c FROM $table WHERE $where", $params)['c'];
    }
}

function db(): Database {
    return Database::getInstance();
}
