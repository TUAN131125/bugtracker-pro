<?php
define('APP_NAME',    'BugTracker Pro');
define('APP_VERSION', '1.0.0');
// Tự động detect local hay production
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']);
define('APP_URL', $isLocal ? 'http://localhost/bugtracker-pro' : 'https://bugtracker.kesug.com');

define('SESSION_LIFETIME', 7200);    // 2 giờ (giây)
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);         // 15 phút (giây)
define('UPLOAD_MAX_SIZE', 10485760); // 10MB (bytes)
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain']);

define('BCRYPT_COST', 12);
define('TOKEN_LENGTH', 64);
define('RESET_TOKEN_EXPIRES', 7200); // 2 giờ

define('ITEMS_PER_PAGE', 25);