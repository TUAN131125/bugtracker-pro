<?php
/**
 * @var string $csrf_token
 * @var string $token
 * @var array $errors
 */
$passwordError = $errors['password'] ?? '';
$confirmError = $errors['password_confirm'] ?? '';
?>

<h4 class="card-title fw-bold mb-3">Đặt Lại Mật Khẩu</h4>

<p class="text-muted small mb-3">
    Nhập mật khẩu mới của bạn. Link này có hiệu lực trong 2 giờ.
</p>

<form method="POST" action="<?= APP_URL ?>/reset-password" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <!-- Mật khẩu mới -->
    <div class="mb-3">
        <label class="form-label fw-500">Mật khẩu mới</label>
        <div class="input-group">
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control <?= $passwordError ? 'is-invalid' : '' ?>"
                   placeholder="Tối thiểu 8 ký tự, có chữ hoa & số"
                   required>
            <button class="btn btn-outline-secondary" type="button" id="togglePw">
                <i class="fa fa-eye"></i>
            </button>
        </div>
        <?php if ($passwordError): ?>
        <div class="text-danger small mt-1">
            <i class="fa fa-times-circle me-1"></i><?= e($passwordError) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Xác nhận -->
    <div class="mb-3">
        <label class="form-label fw-500">Xác nhận mật khẩu</label>
        <input type="password"
               name="password_confirm"
               class="form-control <?= $confirmError ? 'is-invalid' : '' ?>"
               placeholder="Nhập lại mật khẩu mới"
               required>
        <?php if ($confirmError): ?>
        <div class="invalid-feedback d-block">
            <i class="fa fa-times-circle me-1"></i><?= e($confirmError) ?>
        </div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-bold mb-3">
        <i class="fa fa-save me-2"></i>Lưu Mật Khẩu Mới
    </button>

    <p class="text-center text-muted mb-0" style="font-size:13px;">
        <a href="<?= APP_URL ?>/login" class="text-primary text-decoration-none">
            ← Quay lại Đăng nhập
        </a>
    </p>
</form>

<script>
document.getElementById('togglePw').addEventListener('click', function() {
    const pw = document.getElementById('password');
    pw.type = pw.type === 'password' ? 'text' : 'password';
    this.innerHTML = pw.type === 'password'
        ? '<i class="fa fa-eye"></i>'
        : '<i class="fa fa-eye-slash"></i>';
});
</script>