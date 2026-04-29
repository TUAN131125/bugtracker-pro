<?php
/**
 * @var string $csrf_token
 */
?>
<h4 class="card-title fw-bold mb-1">Mời Thành Viên</h4>
<p class="text-muted small mb-3">Bước 4/4 — Lần cuối (có thể bỏ qua)</p>

<form method="POST" action="<?= APP_URL ?>/register/invite" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <!-- Email list -->
    <div class="mb-3">
        <label class="form-label fw-500">Email những người muốn mời</label>
        <textarea name="invite_emails"
                  class="form-control"
                  rows="4"
                  placeholder="user1@email.com
user2@email.com
user3@email.com"
                  style="font-family:monospace;font-size:13px;"></textarea>
        <small class="text-muted d-block mt-1">Cách nhau bằng Enter hoặc dấu phẩy</small>
    </div>

    <!-- Role cho người mời -->
    <div class="mb-3">
        <label class="form-label fw-500">Role cho người được mời</label>
        <select name="invite_role" class="form-select">
            <option value="developer" selected>
                👨‍💻 Developer — Xử lý bug, comment
            </option>
            <option value="manager">
                👔 Manager — Quản lý dự án
            </option>
            <option value="reporter">
                📝 Reporter — Báo cáo bug
            </option>
            <option value="viewer">
                👁️ Viewer — Chỉ xem
            </option>
        </select>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn btn-primary w-100 fw-bold mb-2">
        <i class="fa fa-paper-plane me-2"></i>Gửi Lời Mời
    </button>

    <!-- Skip option -->
    <button type="submit" name="skip" class="btn btn-outline-secondary w-100 fw-500">
        Bỏ qua, mời sau
    </button>

    <!-- Success message (nếu skip) -->
    <div class="alert alert-success mt-3 d-none" id="successBox">
        <i class="fa fa-check-circle me-2"></i>
        <strong>Chúc mừng!</strong> Tài khoản bạn đã sẵn sàng. Bắt đầu tạo bug ngay thôi!
    </div>

    <p class="text-center text-muted mt-3 mb-0" style="font-size:13px;">
        <a href="<?= APP_URL ?>/register/workspace" class="text-primary text-decoration-none">
            ← Quay lại bước 3
        </a>
    </p>
</form>