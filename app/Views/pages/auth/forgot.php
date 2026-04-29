<?php
/**
 * @var string $csrf_token
 * @var array $errors
 * @var string|null $success
 */
$emailError = $errors['email'] ?? '';
$success = $success ?? null;
?>

<h4 class="card-title fw-bold mb-3">Quên Mật Khẩu?</h4>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa fa-check-circle me-2"></i><?= e($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<p class="text-muted small mb-3">
    Nhập email của bạn, chúng tôi sẽ gửi link để đặt lại mật khẩu
</p>

<form method="POST" action="<?= APP_URL ?>/forgot-password" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <div class="mb-3">
        <label class="form-label fw-500">Email</label>
        <input type="email"
               name="email"
               class="form-control <?= $emailError ? 'is-invalid' : '' ?>"
               placeholder="your@email.com"
               required
               autofocus>
        <?php if ($emailError): ?>
        <div class="invalid-feedback d-block">
            <i class="fa fa-times-circle me-1"></i><?= e($emailError) ?>
        </div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-bold mb-3">
        <i class="fa fa-paper-plane me-2"></i>Gửi Link Reset
    </button>

    <p class="text-center text-muted mb-0" style="font-size:13px;">
        Nhớ mật khẩu rồi?
        <a href="<?= APP_URL ?>/login" class="text-primary fw-bold text-decoration-none">
            Đăng nhập
        </a>
    </p>
</form>