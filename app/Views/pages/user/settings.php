<?php
/** @var array  $user       */
/** @var string $csrf_token */
/** @var array  $errors     */
$errors = $errors ?? [];
?>

<h4 class="fw-bold mb-4">
    <i class="fa fa-gear me-2 text-primary"></i>Cài Đặt Tài Khoản
</h4>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <!-- Đổi mật khẩu -->
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-key me-2 text-warning"></i>Đổi Mật Khẩu
            </h6>
            <form method="POST" action="<?= APP_URL ?>/settings/password">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Mật khẩu hiện tại</label>
                    <input type="password"
                           name="old_password"
                           class="form-control <?= !empty($errors['old_password']) ? 'is-invalid':'' ?>"
                           required>
                    <?php if (!empty($errors['old_password'])): ?>
                    <div class="invalid-feedback d-block">
                        <?= e($errors['old_password']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mật khẩu mới</label>
                    <input type="password"
                           name="new_password"
                           id="newPw"
                           class="form-control <?= !empty($errors['new_password']) ? 'is-invalid':'' ?>"
                           placeholder="Tối thiểu 8 ký tự, có chữ hoa & số"
                           required>
                    <div class="progress mt-1" style="height:3px;">
                        <div id="pwBar" class="progress-bar" style="width:0%;"></div>
                    </div>
                    <?php if (!empty($errors['new_password'])): ?>
                    <div class="text-danger small mt-1">
                        <?= e($errors['new_password']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Xác nhận mật khẩu mới</label>
                    <input type="password"
                           name="confirm_password"
                           id="confirmPw"
                           class="form-control <?= !empty($errors['confirm_password']) ? 'is-invalid':'' ?>"
                           required>
                    <?php if (!empty($errors['confirm_password'])): ?>
                    <div class="invalid-feedback d-block">
                        <?= e($errors['confirm_password']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-warning fw-bold">
                    <i class="fa fa-key me-1"></i>Cập Nhật Mật Khẩu
                </button>
            </form>
        </div>

        <!-- Thông tin phiên đăng nhập -->
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-clock me-2 text-info"></i>Thông Tin Tài Khoản
            </h6>
            <div class="d-flex flex-column gap-2" style="font-size:14px;">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Email:</span>
                    <strong><?= e($user['email']) ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Username:</span>
                    <strong>@<?= e($user['username']) ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Role:</span>
                    <span class="badge bg-primary"><?= ucfirst($user['role']) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Tham gia:</span>
                    <span><?= formatDate($user['created_at']) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Đăng nhập cuối:</span>
                    <span><?= $user['last_login'] ? timeAgo($user['last_login']) : 'Chưa có' ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Trạng thái:</span>
                    <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Đăng xuất tất cả thiết bị -->
        <div class="card p-4">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-right-from-bracket me-2 text-danger"></i>Phiên Đăng Nhập
            </h6>
            <p class="text-muted mb-3" style="font-size:13px;">
                Đăng xuất khỏi tất cả thiết bị khác. Phiên hiện tại sẽ được giữ lại.
            </p>
            <a href="<?= APP_URL ?>/logout"
               class="btn btn-outline-danger btn-sm fw-bold"
               data-confirm="Đăng xuất khỏi tất cả thiết bị?">
                <i class="fa fa-right-from-bracket me-1"></i>Đăng Xuất
            </a>
        </div>
    </div>
</div>

<script>
// Password strength
document.getElementById('newPw')?.addEventListener('input', function() {
    const v = this.value;
    let s   = 0;
    if (v.length >= 8)          s++;
    if (/[A-Z]/.test(v))        s++;
    if (/[0-9]/.test(v))        s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;

    const bar = document.getElementById('pwBar');
    if (bar) {
        bar.style.width    = (s * 25) + '%';
        bar.className      = 'progress-bar '
            + ['bg-danger','bg-warning','bg-info','bg-success'][s-1] || 'bg-danger';
    }
});

// Confirm match
document.getElementById('confirmPw')?.addEventListener('input', function() {
    const match = this.value === document.getElementById('newPw')?.value;
    this.classList.toggle('is-invalid', !match && this.value.length > 0);
    this.classList.toggle('is-valid',   match  && this.value.length > 0);
});
</script>