<?php
session_start();

// 1. Logic Sinh CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Logic Rate Limiting (Khóa 15 phút nếu sai 5 lần)
$lockout_time = 15 * 60; 
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    if (time() - $_SESSION['last_attempt_time'] < $lockout_time) {
        $error = "Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau 15 phút.";
    } else {
        // Hết thời gian khóa, reset lại
        $_SESSION['login_attempts'] = 0;
    }
}

// 3. Xử lý khi Submit Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($error)) {
    // Kiểm tra CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Lỗi bảo mật CSRF Token!");
    }

    $username_or_email = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // TODO: Chỗ này ông ghép nối với PDO Database của Dev A để check DB nha.
    // Tui làm giả lập kiểm tra tài khoản: admin / 123456
    if ($username_or_email === 'admin' && $password === '123456') {
        session_regenerate_id(true); // Bảo mật Session Management
        $_SESSION['user_logged_in'] = true;
        // Xử lý Remember Me (30 ngày)
        if (isset($_POST['remember'])) {
            setcookie('remember_me', 'token_gia_lap_luu_db', time() + (86400 * 30), "/");
        }
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_attempt_time'] = time();
        $error = "Sai thông tin đăng nhập! Số lần sai: " . $_SESSION['login_attempts'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>BugTracker Pro - Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Đăng Nhập</h3>
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Email hoặc Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="passwordField" class="form-control" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">Hiện</button>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="remember" class="form-check-input" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Ghi nhớ đăng nhập (30 ngày)</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Đăng Nhập</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Vanilla JS: Ẩn/Hiện mật khẩu
        document.getElementById('togglePassword').addEventListener('click', function (e) {
            const passwordField = document.getElementById('passwordField');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Hiện' : 'Ẩn';
        });
    </script>
</body>
</html>