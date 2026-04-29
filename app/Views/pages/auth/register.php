<?php
/**
 * @var string $csrf_token
 * @var array $errors
 * @var array $old
 * @var int $captcha_n1
 * @var int $captcha_n2
 */
$emailError = $errors['email'] ?? '';
$passwordError = $errors['password'] ?? '';
$confirmError = $errors['password_confirm'] ?? '';
$agreeError = $errors['agree'] ?? '';
$captchaError = $errors['captcha'] ?? '';
$email = $old['email'] ?? '';
?>

<h4 class="card-title fw-bold mb-1">Tạo tài khoản</h4>
<p class="text-muted small mb-3">Bước 1/4 — Email & Mật khẩu</p>

<form method="POST" action="<?= APP_URL ?>/register" novalidate id="registerForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <!-- Email -->
    <div class="mb-3">
        <label class="form-label fw-500">Email</label>
        <input type="email"
               name="email"
               id="emailInput"
               class="form-control <?= $emailError ? 'is-invalid' : '' ?>"
               value="<?= e($email) ?>"
               placeholder="your@email.com"
               required
               autofocus>
        <small class="text-muted d-block mt-1">Sẽ dùng để đăng nhập và xác minh tài khoản</small>
        <?php if ($emailError): ?>
        <div class="invalid-feedback d-block">
            <i class="fa fa-times-circle me-1"></i><?= e($emailError) ?>
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
                   class="form-control <?= $passwordError ? 'is-invalid' : '' ?>"
                   placeholder="Tối thiểu 8 ký tự, có chữ hoa & số"
                   required>
            <button class="btn btn-outline-secondary" type="button" id="togglePw">
                <i class="fa fa-eye"></i>
            </button>
        </div>

        <!-- Password strength indicator -->
        <div class="progress mt-2" style="height:4px;">
            <div id="passwordStrength" class="progress-bar bg-danger" style="width:0%"></div>
        </div>
        <small id="pwStrengthLabel" class="text-muted d-block mt-1">Yêu cầu: 8+ ký tự, chữ hoa, số</small>

        <?php if ($passwordError): ?>
        <div class="text-danger mt-2 small">
            <i class="fa fa-exclamation-circle me-1"></i><?= e($passwordError) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Xác nhận mật khẩu -->
    <div class="mb-3">
        <label class="form-label fw-500">Xác nhận mật khẩu</label>
        <input type="password"
               id="password_confirm"
               name="password_confirm"
               class="form-control <?= $confirmError ? 'is-invalid' : '' ?>"
               placeholder="Nhập lại mật khẩu"
               required>
        <?php if ($confirmError): ?>
        <div class="invalid-feedback d-block">
            <i class="fa fa-times-circle me-1"></i><?= e($confirmError) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Captcha -->
    <div class="mb-3 p-3 bg-light rounded">
        <small class="fw-500">Xác minh: <?= $captcha_n1 ?> + <?= $captcha_n2 ?> = ?</small>
        <input type="number"
               name="captcha_answer"
               class="form-control form-control-sm mt-2 <?= $captchaError ? 'is-invalid' : '' ?>"
               placeholder="Kết quả"
               style="max-width: 100px;">
        <?php if ($captchaError): ?>
        <small class="text-danger d-block mt-1">
            <i class="fa fa-times-circle me-1"></i><?= e($captchaError) ?>
        </small>
        <?php endif; ?>
    </div>

    <!-- Agree terms -->
    <div class="mb-3 form-check">
        <input type="checkbox" name="agree_terms" id="agreeTerms" class="form-check-input" required>
        <label class="form-check-label" for="agreeTerms">
            Tôi đồng ý với <a href="#" class="text-primary">Điều khoản dịch vụ</a>
            & <a href="#" class="text-primary">Chính sách bảo mật</a>
        </label>
        <?php if ($agreeError): ?>
        <div class="text-danger small mt-1">
            <i class="fa fa-times-circle me-1"></i><?= e($agreeError) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-100 fw-bold mb-3">
        Tiếp tục → Bước 2
    </button>

    <p class="text-center text-muted mb-0" style="font-size:13px;">
        Đã có tài khoản?
        <a href="<?= APP_URL ?>/login" class="text-primary fw-bold text-decoration-none">
            Đăng nhập
        </a>
    </p>
</form>

<script src="<?= APP_URL ?>/public/js/auth.js"></script>