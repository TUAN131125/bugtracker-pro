<?php
class Database {
    private static ?PDO $instance = null;

    // Thông tin kết nối — sẽ thay bằng thông tin InfinityFree khi deploy
    private static array $config = [
        'host'    => 'sql111.infinityfree.com', // đổi thành host của InfinityFree
        'dbname'  => 'if0_41761087_bugtracker',   // tên DB bạn tạo trong phpMyAdmin
        'user'    => 'if0_41761087',         // InfinityFree sẽ có username riêng
        'pass'    => 'bugtracker2026',
        'charset' => 'utf8mb4',
    ];

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$config['host'],
                self::$config['dbname'],
                self::$config['charset']
            );
            try {
                self::$instance = new PDO($dsn, self::$config['user'], self::$config['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // bắt buộc để PDO thật sự dùng prepared statements
                ]);
            } catch (PDOException $e) {
                // Không hiện lỗi DB chi tiết ra màn hình (bảo mật)
                error_log('DB Connection failed: ' . $e->getMessage());
                die('Không thể kết nối database. Vui lòng thử lại sau.');
            }
        }
        return self::$instance;
    }

    // Ngăn clone và unserialize (Singleton pattern)
    private function __clone() {}
    public function __wakeup() { throw new \Exception("Cannot unserialize singleton"); }
}