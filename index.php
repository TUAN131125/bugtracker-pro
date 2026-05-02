<?php
// Bật hiển thị lỗi khi dev (tắt khi production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cấu hình session lưu trong /tmp (cần cho InfinityFree)
ini_set('session.save_path', sys_get_temp_dir());
session_start();

// Định nghĩa đường dẫn gốc của project
define('ROOT_PATH', __DIR__);
define('APP_PATH',  ROOT_PATH . '/app');

// Tự động load class khi cần (thay cho Composer)
spl_autoload_register(function($className) {
    $paths = [
        APP_PATH . '/Controllers/' . $className . '.php',
        APP_PATH . '/Models/'      . $className . '.php',
        APP_PATH . '/Helpers/'     . $className . '.php',
        APP_PATH . '/Middleware/'  . $className . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load cấu hình
require_once APP_PATH . '/Config/app.php';
require_once APP_PATH . '/Config/database.php';
// Thêm vào index.php sau phần require Config
require_once APP_PATH . '/Helpers/functions.php';

// Load Router và chạy
require_once APP_PATH . '/Router.php';
$router = new Router();
$router->dispatch();

// ── SECURITY: Kiểm tra session timeout ──
if (!empty($_SESSION['user_id'])) {
    $sessionTimeout = SESSION_LIFETIME; // 7200 giây = 2 giờ

    if (isset($_SESSION['last_activity'])
        && (time() - $_SESSION['last_activity']) > $sessionTimeout) {
        // Session hết hạn
        session_unset();
        session_destroy();
        session_start();
        header('Location: ' . (defined('APP_URL') ? APP_URL : '') . '/login?expired=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

// ── SECURITY: Thêm session fingerprint chống session hijacking ──
if (!empty($_SESSION['user_id'])) {
    $fingerprint = md5(
        ($_SERVER['HTTP_USER_AGENT'] ?? '') .
        substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 8) // Chỉ lấy prefix IP
    );

    if (!isset($_SESSION['fingerprint'])) {
        $_SESSION['fingerprint'] = $fingerprint;
    } elseif ($_SESSION['fingerprint'] !== $fingerprint) {
        // Fingerprint thay đổi bất thường → destroy session
        session_unset();
        session_destroy();
        session_start();
        header('Location: ' . (defined('APP_URL') ? APP_URL : '') . '/login?security=1');
        exit;
    }
}