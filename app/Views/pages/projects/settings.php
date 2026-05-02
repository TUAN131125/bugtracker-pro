<?php
/** @var array  $project    */
/** @var array  $members    */
/** @var string $csrf_token */
/** @var array  $errors     */
$project    = $project    ?? [];
$members    = $members    ?? [];
$errors     = $errors     ?? [];
$csrf_token = $csrf_token ?? '';
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>"
       class="btn btn-sm btn-outline-secondary">← Quay lại</a>
    <h4 class="fw-bold mb-0">
        <i class="fa fa-gear me-2 text-primary"></i>
        Cài Đặt — <?= e($project['name']) ?>
    </h4>
</div>

<!-- Tab navigation -->
<ul class="nav nav-tabs mb-4" id="settingsTabs">
    <li class="nav-item">
        <a class="nav-link active" href="#general" data-bs-toggle="tab">
            <i class="fa fa-info-circle me-1"></i>Thông tin chung
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#members" data-bs-toggle="tab">
            <i class="fa fa-users me-1"></i>Thành viên
            <span class="badge bg-primary ms-1"><?= count($members) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#labels" data-bs-toggle="tab">
            <i class="fa fa-tags me-1"></i>Labels
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-danger" href="#danger" data-bs-toggle="tab">
            <i class="fa fa-triangle-exclamation me-1"></i>Danger Zone
        </a>
    </li>
</ul>

<div class="tab-content">

    <!-- ── Tab: Thông tin chung ── -->
    <div class="tab-pane fade show active" id="general">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card p-4">
                    <form method="POST"
                          action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/settings">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                        <!-- Tên dự án -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên dự án</label>
                            <input type="text"
                                   name="name"
                                   class="form-control <?= !empty($errors['name']) ? 'is-invalid':'' ?>"
                                   value="<?= e($project['name']) ?>"
                                   required>
                            <?php if (!empty($errors['name'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= e($errors['name']) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Project Key (readonly) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Project Key</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= e($project['key']) ?>"
                                   readonly
                                   style="font-family:monospace;background:#F5F7FA;">
                            <small class="text-muted">Project Key không thể thay đổi sau khi tạo</small>
                        </div>

                        <!-- Mô tả -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả</label>
                            <textarea name="description"
                                      class="form-control"
                                      rows="3"><?= e($project['description'] ?? '') ?></textarea>
                        </div>

                        <!-- Visibility -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quyền truy cập</label>
                            <select name="visibility" class="form-select">
                                <?php foreach ([
                                    'private'   => '🔒 Private — Chỉ thành viên được mời',
                                    'team_only' => '👥 Team Only — Toàn workspace',
                                    'public'    => '🌐 Public — Ai cũng xem được',
                                ] as $val => $label): ?>
                                <option value="<?= $val ?>"
                                        <?= $project['visibility'] === $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Trạng thái</label>
                            <select name="status" class="form-select">
                                <?php foreach ([
                                    'active'   => '✅ Active — Đang hoạt động',
                                    'archived' => '📦 Archived — Lưu trữ',
                                    'closed'   => '🔴 Closed — Đã đóng',
                                ] as $val => $label): ?>
                                <option value="<?= $val ?>"
                                        <?= $project['status'] === $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="fa fa-save me-1"></i>Lưu Cài Đặt
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Tab: Thành viên ── -->
    <div class="tab-pane fade" id="members">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold" style="font-size:14px;">
                    <i class="fa fa-users me-2"></i>Thành Viên Dự Án
                </span>
                <button class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#inviteMemberModal">
                    <i class="fa fa-user-plus me-1"></i>Mời thành viên
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Thành viên</th>
                            <th>Role</th>
                            <th>Tham gia</th>
                            <th style="width:100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($member['avatar'])): ?>
                                    <img src="<?= APP_URL ?>/uploads/<?= e($member['avatar']) ?>"
                                         style="width:32px;height:32px;border-radius:50%;
                                                object-fit:cover;" alt="">
                                    <?php else: ?>
                                    <div style="width:32px;height:32px;border-radius:50%;
                                                background:#E3F2FD;display:flex;
                                                align-items:center;justify-content:center;
                                                color:#0078D4;font-weight:700;font-size:13px;">
                                        <?= mb_strtoupper(mb_substr($member['full_name'], 0, 1)) ?>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-semibold"><?= e($member['full_name']) ?></div>
                                        <small class="text-muted">@<?= e($member['username']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($member['user_id'] !== $project['owner_id']): ?>
                                <form method="POST"
                                      action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/members/<?= $member['user_id'] ?>/role">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                    <select name="role"
                                            class="form-select form-select-sm"
                                            style="width:130px;"
                                            onchange="this.form.submit()">
                                        <?php foreach (['admin','manager','developer','reporter','viewer'] as $r): ?>
                                        <option value="<?= $r ?>"
                                                <?= $member['role'] === $r ? 'selected' : '' ?>>
                                            <?= ucfirst($r) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark">Owner</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted">
                                <?= formatDate($member['joined_at']) ?>
                            </td>
                            <td>
                                <?php if ($member['user_id'] !== $project['owner_id']
                                          && $member['user_id'] !== $_SESSION['user_id']): ?>
                                <form method="POST"
                                      action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/members/<?= $member['user_id'] ?>/remove">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            data-confirm="Xóa <?= e($member['full_name']) ?> khỏi dự án?">
                                        <i class="fa fa-user-minus"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal mời thành viên -->
        <div class="modal fade" id="inviteMemberModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="fa fa-user-plus me-2"></i>Mời Thành Viên
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST"
                          action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/members/invite">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email hoặc Username</label>
                                <input type="text"
                                       name="invite_input"
                                       class="form-control"
                                       placeholder="email@example.com hoặc @username"
                                       required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Role</label>
                                <select name="invite_role" class="form-select">
                                    <option value="developer">Developer</option>
                                    <option value="reporter">Reporter</option>
                                    <option value="viewer">Viewer</option>
                                    <option value="manager">Manager</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fa fa-paper-plane me-1"></i>Gửi lời mời
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Tab: Labels ── -->
    <div class="tab-pane fade" id="labels">
        <div class="text-center py-4">
            <i class="fa fa-tags fa-2x text-primary mb-3 d-block"></i>
            <p class="text-muted">Quản lý labels của dự án</p>
            <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/labels"
               class="btn btn-primary">
                <i class="fa fa-tags me-1"></i>Đi tới trang Labels
            </a>
        </div>
    </div>

    <!-- ── Tab: Danger Zone ── -->
    <div class="tab-pane fade" id="danger">
        <div class="row justify-content-center">
            <div class="col-lg-7">

                <!-- Archive -->
                <div class="card mb-3"
                     style="border:1px solid #FD7E14;">
                    <div class="card-body">
                        <h6 class="fw-bold text-warning mb-1">
                            <i class="fa fa-box-archive me-2"></i>Archive Dự Án
                        </h6>
                        <p class="text-muted mb-3" style="font-size:13px;">
                            Archive dự án sẽ ẩn nó khỏi danh sách nhưng không xóa dữ liệu.
                            Bạn có thể unarchive bất cứ lúc nào.
                        </p>
                        <form method="POST"
                              action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/settings">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                            <input type="hidden" name="name"        value="<?= e($project['name']) ?>">
                            <input type="hidden" name="visibility"  value="<?= e($project['visibility']) ?>">
                            <input type="hidden" name="description" value="<?= e($project['description'] ?? '') ?>">
                            <input type="hidden" name="status"      value="archived">
                            <button type="submit"
                                    class="btn btn-outline-warning fw-bold"
                                    data-confirm="Archive dự án <?= e($project['name']) ?>?">
                                <i class="fa fa-box-archive me-1"></i>Archive Dự Án
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Delete -->
                <div class="card" style="border:1px solid #DC3545;">
                    <div class="card-body">
                        <h6 class="fw-bold text-danger mb-1">
                            <i class="fa fa-trash me-2"></i>Xóa Vĩnh Viễn Dự Án
                        </h6>
                        <p class="text-muted mb-3" style="font-size:13px;">
                            Hành động này <strong>không thể hoàn tác</strong>.
                            Toàn bộ issues, comments, attachments sẽ bị xóa vĩnh viễn.
                        </p>
                        <form method="POST"
                              action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/delete"
                              id="deleteProjectForm">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-danger">
                                    Nhập tên dự án để xác nhận:
                                    <code><?= e($project['name']) ?></code>
                                </label>
                                <input type="text"
                                       name="confirm_name"
                                       class="form-control border-danger"
                                       placeholder="<?= e($project['name']) ?>"
                                       autocomplete="off">
                            </div>
                            <button type="submit"
                                    class="btn btn-danger fw-bold"
                                    id="deleteBtn"
                                    disabled>
                                <i class="fa fa-trash me-1"></i>Xóa Vĩnh Viễn
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Enable nút xóa khi nhập đúng tên
document.querySelector('[name=confirm_name]')?.addEventListener('input', function() {
    const btn       = document.getElementById('deleteBtn');
    const projectName = '<?= addslashes($project['name']) ?>';
    btn.disabled    = this.value !== projectName;
});
</script>