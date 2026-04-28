<?php
class Database {
    private static ?PDO $instance = null;

    private static function getConfig(): array {
        // Tự động nhận biết đang chạy local hay production
        $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', [
            'localhost',
            '127.0.0.1',
        ]) || str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:');

        if ($isLocal) {
            // ── CẤU HÌNH LOCAL (XAMPP) ──
            return [
                'host'    => '127.0.0.1',
                'dbname'  => 'bugtracker',
                'user'    => 'root',
                'pass'    => '',
                'charset' => 'utf8mb4',
            ];
        } else {
            // ── CẤU HÌNH PRODUCTION (InfinityFree) ──
            return [
                'host'    => 'sql111.infinityfree.com',
                'dbname'  => 'if0_41761087_bugtracker',
                'user'    => 'if0_41761087',
                'pass'    => 'bugtracker2026',
                'charset' => 'utf8mb4',
            ];
        }
    }

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = self::getConfig();
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['dbname'],
                $config['charset']
            );
            try {
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('DB Connection failed: ' . $e->getMessage());
                die('Không thể kết nối database. Vui lòng thử lại sau.');
            }
        }
        return self::$instance;
    }

    private function __clone() {}
    public function __wakeup() { throw new \Exception("Cannot unserialize singleton"); }
}