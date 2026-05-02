<?php
/** @var array  $user       */
/** @var string $csrf_token */
/** @var array  $errors     */
$errors = $errors ?? [];
$old    = $old    ?? [];
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">
        <i class="fa fa-user me-2 text-primary"></i>Hồ Sơ Cá Nhân
    </h4>
</div>

<div class="row g-4">

    <!-- Cột trái — Avatar -->
    <div class="col-lg-3">
        <div class="card p-3 text-center">
            <?php if (!empty($user['avatar'])): ?>
            <img src="<?= APP_URL ?>/uploads/<?= e($user['avatar']) ?>"
                 class="rounded-circle mx-auto mb-3"
                 style="width:100px;height:100px;object-fit:cover;
                        border:3px solid #E3F2FD;"
                 alt="Avatar">
            <?php else: ?>
            <div class="rounded-circle mx-auto mb-3 d-flex align-items-center
                        justify-content-center fw-bold"
                 style="width:100px;height:100px;background:#0078D4;
                        color:#fff;font-size:2.5rem;">
                <?= mb_strtoupper(mb_substr($user['full_name'], 0, 1)) ?>
            </div>
            <?php endif; ?>

            <div class="fw-bold" style="font-size:15px;">
                <?= e($user['full_name']) ?>
            </div>
            <div class="text-muted" style="font-size:13px;">
                @<?= e($user['username']) ?>
            </div>
            <div class="mt-2">
                <span class="badge"
                      style="background:#E3F2FD;color:#0078D4;font-size:12px;">
                    <?= ucfirst($user['role']) ?>
                </span>
            </div>

            <?php if (!empty($user['bio'])): ?>
            <p class="text-muted mt-3 mb-0" style="font-size:13px;">
                <?= e($user['bio']) ?>
            </p>
            <?php endif; ?>

            <hr>
            <div class="text-muted" style="font-size:12px;">
                <i class="fa fa-clock me-1"></i>
                Tham gia <?= timeAgo($user['created_at']) ?>
            </div>
            <?php if (!empty($user['last_login'])): ?>
            <div class="text-muted mt-1" style="font-size:12px;">
                <i class="fa fa-sign-in me-1"></i>
                Đăng nhập <?= timeAgo($user['last_login']) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cột phải — Form -->
    <div class="col-lg-9">
        <div class="card p-4">

            <!-- Tab navigation -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link active" href="#info" data-bs-toggle="tab">
                        <i class="fa fa-user me-1"></i>Thông tin
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#security" data-bs-toggle="tab">
                        <i class="fa fa-shield me-1"></i>Bảo mật
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                <!-- Tab: Thông tin cá nhân -->
                <div class="tab-pane fade show active" id="info">
                    <form method="POST"
                          action="<?= APP_URL ?>/profile/update"
                          enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                        <!-- Họ tên -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ và tên</label>
                            <input type="text"
                                   name="full_name"
                                   class="form-control <?= !empty($errors['full_name']) ? 'is-invalid':'' ?>"
                                   value="<?= e($old['fullName'] ?? $user['full_name']) ?>"
                                   required>
                            <?php if (!empty($errors['full_name'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= e($errors['full_name']) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Email (readonly) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email"
                                   class="form-control"
                                   value="<?= e($user['email']) ?>"
                                   readonly
                                   style="background:#F5F7FA;">
                            <small class="text-muted">
                                Email không thể thay đổi
                            </small>
                        </div>

                        <!-- Username (readonly) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text"
                                       class="form-control"
                                       value="<?= e($user['username']) ?>"
                                       readonly
                                       style="background:#F5F7FA;font-family:monospace;">
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiểu sử</label>
                            <textarea name="bio"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Một vài câu giới thiệu về bạn..."><?= e($old['bio'] ?? $user['bio'] ?? '') ?></textarea>
                        </div>

                        <!-- Avatar upload -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Ảnh đại diện
                                <small class="text-muted fw-normal">
                                    (JPG, PNG · Max 2MB)
                                </small>
                            </label>
                            <input type="file"
                                   name="avatar"
                                   class="form-control <?= !empty($errors['avatar']) ? 'is-invalid':'' ?>"
                                   accept="image/jpeg,image/png,image/gif"
                                   onchange="previewAvatar(this)">
                            <?php if (!empty($errors['avatar'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= e($errors['avatar']) ?>
                            </div>
                            <?php endif; ?>
                            <div id="avatarPreview" class="mt-2"></div>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="fa fa-save me-1"></i>Lưu Thay Đổi
                        </button>
                    </form>
                </div>

                <!-- Tab: Bảo mật -->
                <div class="tab-pane fade" id="security">
                    <form method="POST" action="<?= APP_URL ?>/settings/password">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu hiện tại</label>
                            <input type="password"
                                   name="old_password"
                                   class="form-control <?= !empty($errors['old_password']) ? 'is-invalid':'' ?>"
                                   placeholder="Nhập mật khẩu hiện tại"
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
                                   id="newPassword"
                                   class="form-control <?= !empty($errors['new_password']) ? 'is-invalid':'' ?>"
                                   placeholder="Tối thiểu 8 ký tự, có chữ hoa & số"
                                   required>
                            <!-- Strength indicator -->
                            <div class="progress mt-2" style="height:4px;">
                                <div id="passwordStrength"
                                     class="progress-bar"
                                     style="width:0%;transition:.3s;"></div>
                            </div>
                            <small id="pwStrengthLabel" class="text-muted"></small>
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
                                   id="confirmPassword"
                                   class="form-control <?= !empty($errors['confirm_password']) ? 'is-invalid':'' ?>"
                                   placeholder="Nhập lại mật khẩu mới"
                                   required>
                            <?php if (!empty($errors['confirm_password'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= e($errors['confirm_password']) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-warning fw-bold px-4">
                            <i class="fa fa-key me-1"></i>Đổi Mật Khẩu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview avatar trước khi upload
function previewAvatar(input) {
    const preview = document.getElementById('avatarPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `
                <img src="${e.target.result}"
                     style="width:80px;height:80px;border-radius:50%;
                            object-fit:cover;border:3px solid #E3F2FD;">
                <small class="text-muted ms-2">Preview</small>`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Password strength (dùng lại từ auth.js)
document.getElementById('newPassword')?.addEventListener('input', function() {
    const val   = this.value;
    let score   = 0;
    if (val.length >= 8)           score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val))  score++;

    const bar    = document.getElementById('passwordStrength');
    const label  = document.getElementById('pwStrengthLabel');
    const colors = ['bg-danger','bg-warning','bg-info','bg-success'];
    const labels = ['Yếu','Trung bình','Khá','Mạnh'];

    if (bar) {
        bar.style.width = (score * 25) + '%';
        bar.className   = 'progress-bar ' + (colors[score-1] || 'bg-danger');
    }
    if (label) label.textContent = labels[score-1] || '';
});

// Confirm password match
document.getElementById('confirmPassword')?.addEventListener('input', function() {
    const newPw = document.getElementById('newPassword')?.value;
    const match = this.value === newPw;
    this.classList.toggle('is-invalid', !match && this.value.length > 0);
    this.classList.toggle('is-valid',   match  && this.value.length > 0);
});
</script>