<?php
/**
 * @var string $csrf_token
 * @var array $errors
 * @var array $old
 */
$fullNameError = $errors['full_name'] ?? '';
$usernameError = $errors['username'] ?? '';
$fullName = $old['full_name'] ?? '';
$username = $old['username'] ?? '';
?>

<h4 class="card-title fw-bold mb-1">Thông tin cá nhân</h4>
<p class="text-muted small mb-3">Bước 2/4 — Profile</p>

<form method="POST" action="<?= APP_URL ?>/register/profile" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <!-- Họ và tên -->
    <div class="mb-3">
        <label class="form-label fw-500">Họ và tên</label>
        <input type="text"
               name="full_name"
               class="form-control <?= $fullNameError ? 'is-invalid' : '' ?>"
               value="<?= e($fullName) ?>"
               placeholder="Nguyễn Văn A"
               required
               autofocus>
        <?php if ($fullNameError): ?>
        <div class="invalid-feedback d-block">
            <i class="fa fa-times-circle me-1"></i><?= e($fullNameError) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Username (kiểm tra realtime AJAX) -->
    <div class="mb-3">
        <label class="form-label fw-500">Username</label>
        <div class="input-group">
            <span class="input-group-text">@</span>
            <input type="text"
                   name="username"
                   id="usernameInput"
                   class="form-control <?= $usernameError ? 'is-invalid' : '' ?>"
                   value="<?= e($username) ?>"
                   placeholder="nguyenvana"
                   pattern="[a-z0-9_]+"
                   required>
            <span class="input-group-text" id="usernameStatus"></span>
        </div>
        <small class="text-muted d-block mt-1">Chỉ dùng a-z, 0-9, dấu _. Từ 4-50 ký tự</small>
        <?php if ($usernameError): ?>
        <div class="text-danger small mt-1">
            <i class="fa fa-times-circle me-1"></i><?= e($usernameError) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Avatar (optional) -->
    <div class="mb-3">
        <label class="form-label fw-500">Ảnh đại diện (Tùy chọn)</label>
        <div class="input-group">
            <input type="file"
                   name="avatar"
                   id="avatarInput"
                   class="form-control"
                   accept="image/jpeg,image/png"
                   style="max-width: 200px;">
            <small class="text-muted">JPG hoặc PNG, max 2MB</small>
        </div>
        <div id="avatarPreview" class="mt-2"></div>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-100 fw-bold mb-3">
        Tiếp tục → Bước 3
    </button>

    <p class="text-center text-muted mb-0" style="font-size:13px;">
        <a href="<?= APP_URL ?>/register" class="text-primary text-decoration-none">
            ← Quay lại bước 1
        </a>
    </p>
</form>

<script>
// Kiểm tra username realtime
document.getElementById('usernameInput').addEventListener('blur', async function() {
    const username = this.value.trim();
    const statusSpan = document.getElementById('usernameStatus');

    if (!username) {
        statusSpan.innerHTML = '';
        return;
    }

    try {
        const res = await fetch('<?= APP_URL ?>/api/check-username?username=' + encodeURIComponent(username));
        const data = await res.json();

        if (data.available) {
            statusSpan.innerHTML = '<i class="fa fa-check text-success"></i>';
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            statusSpan.innerHTML = '<i class="fa fa-times text-danger"></i>';
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    } catch (e) {
        console.error('Lỗi kiểm tra username:', e);
    }
});

// Preview avatar
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const preview = document.getElementById('avatarPreview');
    const file = e.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            preview.innerHTML = `<img src="${event.target.result}" style="width:80px;height:80px;border-radius:8px;object-fit:cover;">`;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});
</script>