<?php
/**
 * @var string $csrf_token
 * @var array $errors
 * @var array $old
 * @var int $captcha_n1
 * @var int $captcha_n2
 */
// Hiển thị lỗi nếu có
$hasErrors = !empty($errors);
$captchaError = $errors['captcha'] ?? '';
$generalError = $errors['general'] ?? '';
$loginInput = $old['login_input'] ?? '';
?>

<h4 class="card-title fw-bold mb-3">Đăng nhập</h4>

<?php if ($generalError): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fa fa-exclamation-circle me-2"></i><?= e($generalError) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="<?= APP_URL ?>/login" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <!-- Email hoặc Username -->
    <div class="mb-3">
        <label class="form-label fw-500">Email hoặc Username</label>
        <input type="text"
               name="login_input"
               class="form-control <?= !empty($errors['login_input']) ? 'is-invalid' : '' ?>"
               value="<?= e($loginInput) ?>"
               placeholder="example@email.com hoặc username"
               autofocus>
        <?php if (!empty($errors['login_input'])): ?>
        <div class="invalid-feedback d-block">
            <i class="fa fa-times-circle me-1"></i><?= e($errors['login_input']) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Mật khẩu -->
    <div class="mb-3">
        <label class="form-label fw-500">Mật khẩu</label>
        <div class="input-group">
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                   placeholder="Nhập mật khẩu"
                   autocomplete="current-password">
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="fa fa-eye"></i>
            </button>
        </div>
        <?php if (!empty($errors['password'])): ?>
        <div class="invalid-feedback d-block">
            <i class="fa fa-times-circle me-1"></i><?= e($errors['password']) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Captcha đơn giản (tính toán) -->
    <div class="mb-3 p-3 bg-light rounded">
        <label class="form-label fw-500 mb-2">Xác minh: <?= $captcha_n1 ?> + <?= $captcha_n2 ?> = ?</label>
        <input type="number"
               name="captcha_answer"
               class="form-control form-control-sm <?= $captchaError ? 'is-invalid' : '' ?>"
               placeholder="Nhập kết quả"
               style="max-width: 120px;">
        <?php if ($captchaError): ?>
        <small class="text-danger d-block mt-1">
            <i class="fa fa-times-circle me-1"></i><?= e($captchaError) ?>
        </small>
        <?php endif; ?>
    </div>

    <!-- Remember me -->
    <div class="mb-3 form-check">
        <input type="checkbox" name="remember" id="remember" class="form-check-input">
        <label class="form-check-label" for="remember">
            Ghi nhớ đăng nhập trong 30 ngày
        </label>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-100 fw-bold mb-3">
        <i class="fa fa-sign-in me-2"></i>Đăng nhập
    </button>

    <!-- Divider -->
    <div class="d-flex align-items-center my-3">
        <hr class="flex-grow-1">
        <span class="text-muted mx-2" style="font-size:12px;">HOẶC</span>
        <hr class="flex-grow-1">
    </div>

    <!-- Links -->
    <div class="text-center">
        <p class="mb-2">
            <a href="<?= APP_URL ?>/forgot-password" class="text-primary text-decoration-none">
                <i class="fa fa-key me-1"></i>Quên mật khẩu?
            </a>
        </p>
        <p class="text-muted mb-0" style="font-size:14px;">
            Chưa có tài khoản?
            <a href="<?= APP_URL ?>/register" class="text-primary fw-bold text-decoration-none">
                Tạo tài khoản mới
            </a>
        </p>
    </div>
</form>

<script>
// Bật tắt hiển thị mật khẩu
document.getElementById('togglePassword').addEventListener('click', function() {
    const pwInput = document.getElementById('password');
    const isPassword = pwInput.type === 'password';
    pwInput.type = isPassword ? 'text' : 'password';
    this.innerHTML = isPassword
        ? '<i class="fa fa-eye-slash"></i>'
        : '<i class="fa fa-eye"></i>';
});
</script>