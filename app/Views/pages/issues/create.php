<?php $old = $old ?? []; $errors = $errors ?? []; ?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>"
       class="btn btn-sm btn-outline-secondary">
        ← Quay lại
    </a>
    <h4 class="fw-bold mb-0">
        <i class="fa fa-plus-circle me-2 text-primary"></i>
        Tạo Issue Mới —
        <span class="text-primary"><?= e($project['name']) ?></span>
    </h4>
</div>

<div class="row g-4">
    <!-- Form chính -->
    <div class="col-lg-8">
        <form method="POST"
              action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/issues/new"
              enctype="multipart/form-data"
              novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

            <div class="card p-4">
                <!-- Tiêu đề -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Tiêu đề <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="title"
                           class="form-control <?= !empty($errors['title']) ? 'is-invalid':'' ?>"
                           value="<?= e($old['title'] ?? '') ?>"
                           placeholder="Mô tả ngắn gọn về bug/issue..."
                           required
                           autofocus>
                    <?php if (!empty($errors['title'])): ?>
                    <div class="invalid-feedback d-block">
                        <i class="fa fa-times-circle me-1"></i><?= e($errors['title']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Mô tả -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả chi tiết</label>
                    <textarea name="description"
                              class="form-control"
                              rows="6"
                              placeholder="Mô tả chi tiết issue, bao gồm context, expected vs actual behavior...
Hỗ trợ **bold**, *italic*, `code`"><?= e($old['description'] ?? '') ?></textarea>
                    <small class="text-muted">Hỗ trợ Markdown cơ bản</small>
                </div>

                <!-- Steps to reproduce -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Các bước tái tạo lỗi</label>
                    <textarea name="steps_to_reproduce"
                              class="form-control"
                              rows="4"
                              placeholder="1. Mở trang login
2. Nhập email không hợp lệ
3. Click nút Đăng nhập
4. Quan sát kết quả"><?= e($old['steps_to_reproduce'] ?? '') ?></textarea>
                </div>

                <!-- Môi trường + Browser -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Môi trường</label>
                        <input type="text"
                               name="environment"
                               class="form-control"
                               value="<?= e($old['environment'] ?? '') ?>"
                               placeholder="Production / Staging / Local">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Trình duyệt</label>
                        <input type="text"
                               name="browser_info"
                               class="form-control"
                               value="<?= e($old['browser_info'] ?? '') ?>"
                               placeholder="Chrome 120 / Firefox 121 / Safari 17"
                               id="browserInfo">
                    </div>
                </div>

                <!-- File đính kèm -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Đính kèm file
                        <small class="text-muted fw-normal">(JPG, PNG, PDF, TXT · Max 10MB · Tối đa 5 file)</small>
                    </label>
                    <input type="file"
                           name="attachments[]"
                           class="form-control"
                           multiple
                           accept="image/*,.pdf,.txt,.log">
                </div>

                <!-- Submit buttons -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>"
                       class="btn btn-outline-secondary">Hủy</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fa fa-plus me-1"></i>Tạo Issue
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Sidebar metadata -->
    <div class="col-lg-4">
        <div class="card p-3">
            <h6 class="fw-bold mb-3 text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:1px;">
                Thông tin Issue
            </h6>

            <!-- Type -->
            <div class="mb-3">
                <label class="form-label fw-500">Loại Issue</label>
                <select name="type" class="form-select form-select-sm">
                    <?php foreach ([
                        'bug'         => '🐛 Bug',
                        'feature'     => '✨ Feature',
                        'task'        => '✅ Task',
                        'improvement' => '⬆️ Improvement',
                        'question'    => '❓ Question',
                        'epic'        => '🗺️ Epic',
                    ] as $val => $label): ?>
                    <option value="<?= $val ?>"
                            <?= ($old['type'] ?? 'bug') === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Priority -->
            <div class="mb-3">
                <label class="form-label fw-500">Priority</label>
                <select name="priority" class="form-select form-select-sm">
                    <?php foreach ([
                        'critical' => '🔴 Critical',
                        'high'     => '🟠 High',
                        'medium'   => '🟡 Medium',
                        'low'      => '🟢 Low',
                        'trivial'  => '⚪ Trivial',
                    ] as $val => $label): ?>
                    <option value="<?= $val ?>"
                            <?= ($old['priority'] ?? 'medium') === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Severity -->
            <div class="mb-3">
                <label class="form-label fw-500">Severity</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">— Không chọn —</option>
                    <?php foreach (['blocker','major','minor','cosmetic'] as $s): ?>
                    <option value="<?= $s ?>"
                            <?= ($old['severity'] ?? '') === $s ? 'selected' : '' ?>>
                        <?= ucfirst($s) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Assignee -->
            <div class="mb-3">
                <label class="form-label fw-500">Giao cho</label>
                <select name="assignee_id" class="form-select form-select-sm">
                    <option value="">— Chưa giao —</option>
                    <?php foreach ($members as $member): ?>
                    <option value="<?= $member['id'] ?>"
                            <?= ($old['assignee_id'] ?? '') == $member['id'] ? 'selected' : '' ?>>
                        <?= e($member['full_name']) ?>
                        (<?= e($member['role']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Due date -->
            <div class="mb-3">
                <label class="form-label fw-500">Hạn chót</label>
                <input type="date"
                       name="due_date"
                       class="form-control form-control-sm"
                       value="<?= e($old['due_date'] ?? '') ?>"
                       min="<?= date('Y-m-d') ?>">
            </div>

            <!-- Estimated hours -->
            <div class="mb-0">
                <label class="form-label fw-500">Ước tính (giờ)</label>
                <input type="number"
                       name="estimated_hours"
                       class="form-control form-control-sm"
                       value="<?= e($old['estimated_hours'] ?? '') ?>"
                       min="0.5"
                       max="999"
                       step="0.5"
                       placeholder="vd: 2.5">
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill browser info
document.addEventListener('DOMContentLoaded', function() {
    const bi = document.getElementById('browserInfo');
    if (bi && !bi.value) {
        bi.value = navigator.userAgent.split(' ').slice(-3).join(' ');
    }
});
</script>