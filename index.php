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