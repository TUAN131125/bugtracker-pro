<?php
/** @var array  $errors     */
/** @var array  $old        */
/** @var string $csrf_token */
$errors     = $errors     ?? [];
$old        = $old        ?? [];
$csrf_token = $csrf_token ?? '';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= APP_URL ?>/projects" class="btn btn-sm btn-outline-secondary">
        ← Quay lại
    </a>
    <h4 class="fw-bold mb-0">
        <i class="fa fa-folder-plus me-2 text-primary"></i>Tạo Dự Án Mới
    </h4>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <form method="POST" action="<?= APP_URL ?>/projects/new" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <!-- Tên dự án -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Tên dự án <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="projectName"
                           class="form-control <?= !empty($errors['name']) ? 'is-invalid':'' ?>"
                           value="<?= e($old['name'] ?? '') ?>"
                           placeholder="vd: Website Công ty ABC"
                           required autofocus>
                    <?php if (!empty($errors['name'])): ?>
                    <div class="invalid-feedback d-block">
                        <i class="fa fa-times-circle me-1"></i><?= e($errors['name']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Project Key -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Project Key <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="text"
                               name="key"
                               id="projectKey"
                               class="form-control <?= !empty($errors['key']) ? 'is-invalid':'' ?>"
                               value="<?= e($old['key'] ?? '') ?>"
                               placeholder="vd: ABC"
                               maxlength="10"
                               style="text-transform:uppercase;font-family:monospace;"
                               required>
                        <span class="input-group-text">
                            <i class="fa fa-hashtag text-muted"></i>
                        </span>
                    </div>
                    <small class="text-muted">2-10 ký tự viết hoa, chỉ A-Z và 0-9. Dùng làm prefix cho issue key (vd: ABC-001)</small>
                    <?php if (!empty($errors['key'])): ?>
                    <div class="text-danger small mt-1">
                        <i class="fa fa-times-circle me-1"></i><?= e($errors['key']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Mô tả -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả</label>
                    <textarea name="description"
                              class="form-control"
                              rows="3"
                              placeholder="Mô tả ngắn về dự án..."><?= e($old['description'] ?? '') ?></textarea>
                </div>

                <!-- Visibility -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Quyền truy cập</label>
                    <div class="d-flex flex-column gap-2">
                        <?php
                        $visOptions = [
                            'private'   => ['fa-lock',   '#DC3545', 'Private',   'Chỉ thành viên được mời mới xem được'],
                            'team_only' => ['fa-users',  '#0078D4', 'Team Only', 'Tất cả thành viên trong workspace'],
                            'public'    => ['fa-globe',  '#28A745', 'Public',    'Ai cũng xem được'],
                        ];
                        $selectedVis = $old['visibility'] ?? 'private';
                        foreach ($visOptions as $val => [$icon, $color, $label, $desc]):
                        ?>
                        <label class="d-flex align-items-center gap-3 p-3 rounded border
                                      <?= $selectedVis === $val ? 'border-primary bg-light' : '' ?>"
                               style="cursor:pointer;">
                            <input type="radio"
                                   name="visibility"
                                   value="<?= $val ?>"
                                   <?= $selectedVis === $val ? 'checked' : '' ?>>
                            <i class="fa <?= $icon ?>" style="color:<?= $color ?>;width:16px;"></i>
                            <div>
                                <div class="fw-bold" style="font-size:13px;"><?= $label ?></div>
                                <div class="text-muted" style="font-size:12px;"><?= $desc ?></div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= APP_URL ?>/projects"
                       class="btn btn-outline-secondary">Hủy</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fa fa-folder-plus me-1"></i>Tạo Dự Án
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-generate project key từ tên
document.getElementById('projectName').addEventListener('input', function() {
    const keyInput = document.getElementById('projectKey');
    // Chỉ auto-fill nếu user chưa tự nhập key
    if (!keyInput.dataset.manualEdit) {
        const key = this.value
            .toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .trim()
            .split(/\s+/)
            .map(w => w.substring(0, 3))
            .join('')
            .substring(0, 8);
        keyInput.value = key;
    }
});

// Đánh dấu user đã tự nhập key
document.getElementById('projectKey').addEventListener('input', function() {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    this.dataset.manualEdit = 'true';
});
</script>