<?php
/**
 * @var string $csrf_token
 * @var array $errors
 * @var array $old
 * @var string $suggested_name
 */
$nameError = $errors['workspace_name'] ?? '';
$slugError = $errors['workspace_slug'] ?? '';
$name = $old['workspace_name'] ?? $suggested_name ?? '';
$slug = $old['workspace_slug'] ?? '';
$type = $old['workspace_type'] ?? 'team';
?>

<h4 class="card-title fw-bold mb-1">Tạo Workspace</h4>
<p class="text-muted small mb-3">Bước 3/4 — Không gian làm việc</p>

<form method="POST" action="<?= APP_URL ?>/register/workspace" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <!-- Tên workspace -->
    <div class="mb-3">
        <label class="form-label fw-500">Tên Workspace</label>
        <input type="text"
               name="workspace_name"
               id="wsNameInput"
               class="form-control <?= $nameError ? 'is-invalid' : '' ?>"
               value="<?= e($name) ?>"
               placeholder="Công ty ABC / Team Frontend"
               required
               autofocus>
        <?php if ($nameError): ?>
        <div class="invalid-feedback d-block">
            <i class="fa fa-times-circle me-1"></i><?= e($nameError) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Workspace slug (URL) -->
    <div class="mb-3">
        <label class="form-label fw-500">URL Workspace</label>
        <div class="input-group">
            <span class="input-group-text"><?= APP_URL ?>/w/</span>
            <input type="text"
                   name="workspace_slug"
                   id="wsSlugInput"
                   class="form-control <?= $slugError ? 'is-invalid' : '' ?>"
                   value="<?= e($slug) ?>"
                   placeholder="cong-ty-abc"
                   required>
            <span class="input-group-text" id="slugStatus"></span>
        </div>
        <small class="text-muted d-block mt-1">Tự động sinh từ tên, có thể chỉnh sửa</small>
        <?php if ($slugError): ?>
        <div class="text-danger small mt-1">
            <i class="fa fa-times-circle me-1"></i><?= e($slugError) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Loại workspace -->
    <div class="mb-3">
        <label class="form-label fw-500">Loại Workspace</label>
        <select name="workspace_type" class="form-select">
            <option value="personal" <?= $type === 'personal' ? 'selected' : '' ?>>
                👤 Cá nhân — Chỉ tôi dùng
            </option>
            <option value="team" <?= $type === 'team' ? 'selected' : '' ?>>
                👥 Team nhỏ — 2-10 người
            </option>
            <option value="enterprise" <?= $type === 'enterprise' ? 'selected' : '' ?>>
                🏢 Doanh nghiệp — Tổ chức lớn
            </option>
        </select>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-100 fw-bold mb-2">
        Tạo Workspace → Bước 4
    </button>

    <!-- Skip option -->
    <button type="submit" name="skip" class="btn btn-outline-secondary w-100 fw-500">
        Bỏ qua, làm sau
    </button>

    <p class="text-center text-muted mt-3 mb-0" style="font-size:13px;">
        <a href="<?= APP_URL ?>/register/profile" class="text-primary text-decoration-none">
            ← Quay lại bước 2
        </a>
    </p>
</form>

<script>
// Tự động sinh slug từ tên
document.getElementById('wsNameInput').addEventListener('input', function() {
    const name = this.value;
    if (name) {
        const slug = name.toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9\-]/g, '');
        document.getElementById('wsSlugInput').value = slug;
    }
});

// Kiểm tra slug available
document.getElementById('wsSlugInput').addEventListener('blur', async function() {
    const slug = this.value.trim();
    const statusSpan = document.getElementById('slugStatus');

    if (!slug) {
        statusSpan.innerHTML = '';
        return;
    }

    try {
        const data = new FormData();
        data.append('name', document.getElementById('wsNameInput').value);

        const res = await fetch('<?= APP_URL ?>/api/slug-from-name', { method: 'POST', body: data });
        const result = await res.json();

        if (result.available) {
            statusSpan.innerHTML = '<i class="fa fa-check text-success"></i>';
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            statusSpan.innerHTML = '<i class="fa fa-times text-danger"></i>';
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    } catch (e) {
        console.error('Lỗi kiểm tra slug:', e);
    }
});
</script>