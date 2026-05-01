<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'BugTracker Pro') ?></title>

    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- CSS riêng của app -->
    <link href="<?= APP_URL ?>/public/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- ════════════════════════════════════
     NAVBAR
════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background:#1E3A5F;">
    <div class="container-fluid px-4">

        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="<?= APP_URL ?>/dashboard">
            <i class="fa-solid fa-bug me-2" style="color:#0078D4;"></i>BugTracker Pro
        </a>

        <!-- Search toàn cục (Ctrl+K) -->
        <div class="mx-auto d-none d-lg-block" style="width:380px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text"
                       id="globalSearch"
                       class="form-control border-start-0"
                       placeholder="Tìm kiếm issue, project... (Ctrl+K)"
                       autocomplete="off">
            </div>
            <!-- Search results dropdown -->
            <div id="searchResults"
                 class="position-absolute bg-white border rounded shadow-sm mt-1 d-none"
                 style="width:380px;z-index:9999;max-height:300px;overflow-y:auto;">
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 ms-3">

            <!-- Nút tạo issue mới -->
            <div class="dropdown">
                <button class="btn btn-sm btn-primary fw-bold dropdown-toggle"
                        data-bs-toggle="dropdown">
                    <i class="fa fa-plus me-1"></i>Tạo mới
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <a class="dropdown-item" href="<?= APP_URL ?>/projects/new">
                            <i class="fa fa-folder-plus me-2 text-primary"></i>Dự án mới
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <?php
                    // Lấy project gần nhất để tạo issue nhanh
                    if (!empty($_SESSION['user_id'])) {
                        try {
                            $navProjModel   = new ProjectModel();
                            $navProjects    = $navProjModel->getByUser($_SESSION['user_id']);
                            foreach (array_slice($navProjects, 0, 4) as $np):
                    ?>
                    <li>
                        <a class="dropdown-item"
                           href="<?= APP_URL ?>/projects/<?= htmlspecialchars(strtolower($np['key'])) ?>/issues/new">
                            <i class="fa fa-bug me-2 text-danger"></i>
                            Issue trong <strong><?= htmlspecialchars($np['key']) ?></strong>
                        </a>
                    </li>
                    <?php
                            endforeach;
                            if (empty($navProjects)):
                    ?>
                    <li>
                        <span class="dropdown-item text-muted" style="font-size:13px;">
                            Tạo dự án trước để thêm issue
                        </span>
                    </li>
                    <?php
                            endif;
                        } catch (Exception $e) { /* DB chưa sẵn sàng */ }
                    }
                    ?>
                </ul>
            </div>

            <!-- Notification bell -->
            <div class="dropdown">
                <button class="btn btn-link text-white position-relative p-1"
                        data-bs-toggle="dropdown"
                        title="Thông báo">
                    <i class="fa fa-bell fa-lg"></i>
                    <?php
                    $navUnread = 0;
                    if (!empty($_SESSION['user_id'])) {
                        try {
                            $navNotifModel = new NotificationModel();
                            $navUnread     = $navNotifModel->countUnread($_SESSION['user_id']);
                        } catch (Exception $e) { /* ignore */ }
                    }
                    if ($navUnread > 0):
                    ?>
                    <span class="position-absolute top-0 start-100 translate-middle
                                 badge rounded-pill bg-danger"
                          style="font-size:10px;">
                        <?= $navUnread > 99 ? '99+' : $navUnread ?>
                    </span>
                    <?php endif; ?>
                </button>

                <div class="dropdown-menu dropdown-menu-end shadow p-0"
                     style="width:340px;max-height:420px;overflow-y:auto;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center
                                py-2 px-3"
                         style="background:#F5F8FC;border-bottom:1px solid #E0E7EF;">
                        <span class="fw-bold" style="font-size:13px;">
                            <i class="fa fa-bell me-1 text-primary"></i>Thông báo
                        </span>
                        <?php if ($navUnread > 0): ?>
                        <a href="<?= APP_URL ?>/notifications/read-all"
                           class="text-primary text-decoration-none"
                           style="font-size:12px;">
                            Đánh dấu tất cả đã đọc
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php
                    if (!empty($_SESSION['user_id'])) {
                        try {
                            $navNotifs = (new NotificationModel())->getLatest($_SESSION['user_id'], 8);
                            if (!empty($navNotifs)):
                                foreach ($navNotifs as $notif):
                                $isUnread = !$notif['is_read'];
                    ?>
                    <a href="<?= $notif['link'] ? APP_URL . htmlspecialchars($notif['link']) : '#' ?>"
                       class="dropdown-item py-2 px-3 border-bottom"
                       style="<?= $isUnread ? 'background:#EEF4FB;' : '' ?>white-space:normal;">
                        <div class="d-flex gap-2 align-items-start">
                            <div style="width:8px;height:8px;border-radius:50%;
                                        background:<?= $isUnread ? '#0078D4' : 'transparent' ?>;
                                        margin-top:5px;flex-shrink:0;">
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:<?= $isUnread ? '600':'400' ?>;">
                                    <?= htmlspecialchars($notif['title']) ?>
                                </div>
                                <?php if (!empty($notif['message'])): ?>
                                <div class="text-muted" style="font-size:12px;">
                                    <?= htmlspecialchars(mb_substr($notif['message'], 0, 60)) ?>
                                    <?= mb_strlen($notif['message']) > 60 ? '...' : '' ?>
                                </div>
                                <?php endif; ?>
                                <div class="text-muted" style="font-size:11px;margin-top:2px;">
                                    <i class="fa fa-clock me-1"></i>
                                    <?= function_exists('timeAgo') ? timeAgo($notif['created_at']) : $notif['created_at'] ?>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php
                                endforeach;
                            else:
                    ?>
                    <div class="text-center text-muted py-4" style="font-size:13px;">
                        <i class="fa fa-bell-slash fa-2x d-block mb-2 opacity-50"></i>
                        Chưa có thông báo nào
                    </div>
                    <?php
                            endif;
                        } catch (Exception $e) {
                    ?>
                    <div class="text-center text-muted py-3" style="font-size:13px;">
                        Chưa có thông báo
                    </div>
                    <?php } } ?>

                    <div style="background:#F5F8FC;border-top:1px solid #E0E7EF;">
                        <a href="<?= APP_URL ?>/notifications"
                           class="dropdown-item text-center text-primary py-2"
                           style="font-size:13px;">
                            Xem tất cả thông báo →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Avatar + user menu -->
            <div class="dropdown">
                <button class="btn btn-link p-0 d-flex align-items-center gap-2"
                        data-bs-toggle="dropdown">
                    <?php
                    $navAvatar = $_SESSION['user_avatar'] ?? null;
                    $navName   = $_SESSION['user_name']   ?? 'User';
                    $navInitial= mb_strtoupper(mb_substr($navName, 0, 1));
                    if ($navAvatar):
                    ?>
                    <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($navAvatar) ?>"
                         class="rounded-circle border border-secondary"
                         width="34" height="34"
                         style="object-fit:cover;"
                         alt="<?= htmlspecialchars($navName) ?>">
                    <?php else: ?>
                    <!-- Avatar chữ cái đầu nếu chưa có ảnh -->
                    <div class="rounded-circle d-flex align-items-center justify-content-center
                                border border-secondary fw-bold"
                         style="width:34px;height:34px;background:#0078D4;
                                color:#fff;font-size:14px;flex-shrink:0;">
                        <?= $navInitial ?>
                    </div>
                    <?php endif; ?>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:220px;">
                    <!-- User info -->
                    <li class="px-3 py-2"
                        style="background:#F5F8FC;border-bottom:1px solid #E0E7EF;">
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($navAvatar): ?>
                            <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($navAvatar) ?>"
                                 class="rounded-circle"
                                 width="36" height="36"
                                 style="object-fit:cover;" alt="">
                            <?php else: ?>
                            <div class="rounded-circle d-flex align-items-center
                                        justify-content-center fw-bold"
                                 style="width:36px;height:36px;background:#0078D4;
                                        color:#fff;font-size:14px;flex-shrink:0;">
                                <?= $navInitial ?>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-bold" style="font-size:13px;">
                                    <?= htmlspecialchars($navName) ?>
                                </div>
                                <div class="text-muted" style="font-size:12px;">
                                    <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>

                    <li>
                        <a class="dropdown-item" href="<?= APP_URL ?>/profile">
                            <i class="fa fa-user me-2 text-muted"></i>Hồ sơ cá nhân
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= APP_URL ?>/settings">
                            <i class="fa fa-gear me-2 text-muted"></i>Cài đặt
                        </a>
                    </li>

                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item" href="<?= APP_URL ?>/admin">
                            <i class="fa fa-shield me-2 text-warning"></i>Quản trị hệ thống
                        </a>
                    </li>
                    <?php endif; ?>

                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout">
                            <i class="fa fa-right-from-bracket me-2"></i>Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</nav>

<!-- ════════════════════════════════════
     SIDEBAR + CONTENT
════════════════════════════════════ -->
<div class="d-flex" style="min-height:calc(100vh - 56px);">

    <!-- Sidebar -->
    <aside class="d-none d-lg-flex flex-column flex-shrink-0 p-3"
           style="width:220px;background:#fff;border-right:1px solid #dee2e6;
                  position:sticky;top:56px;height:calc(100vh - 56px);overflow-y:auto;">

        <!-- Menu chính -->
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item">
                <a href="<?= APP_URL ?>/dashboard"
                   class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : 'text-dark' ?>">
                    <i class="fa fa-gauge me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/projects"
                   class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], '/projects')
                                    && !str_contains($_SERVER['REQUEST_URI'], '/issues'))
                                    ? 'active' : 'text-dark' ?>">
                    <i class="fa fa-folder me-2"></i>Dự án
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/reports"
                   class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/reports') ? 'active' : 'text-dark' ?>">
                    <i class="fa fa-chart-bar me-2"></i>Báo cáo
                </a>
            </li>
        </ul>

        <hr class="my-2">

        <!-- Dự án gần đây từ DB -->
        <p class="text-muted small px-2 mb-1 fw-semibold"
           style="font-size:11px;letter-spacing:.5px;">
            DỰ ÁN GẦN ĐÂY
        </p>
        <ul class="nav nav-pills flex-column gap-1">
            <?php
            if (!empty($_SESSION['user_id'])) {
                try {
                    $sidebarProjModel = new ProjectModel();
                    $sidebarProjects  = $sidebarProjModel->getByUser($_SESSION['user_id']);
                    $currentUri       = $_SERVER['REQUEST_URI'] ?? '';

                    if (!empty($sidebarProjects)) {
                        foreach (array_slice($sidebarProjects, 0, 6) as $sp):
                            $spKey      = strtolower($sp['key']);
                            $isActive   = str_contains($currentUri, '/projects/' . $spKey);
                            $openCount  = $sp['open_bugs'] ?? 0;
                ?>
                <li>
                    <a href="<?= APP_URL ?>/projects/<?= htmlspecialchars($spKey) ?>"
                       class="nav-link <?= $isActive ? 'active' : 'text-dark' ?> text-truncate
                              d-flex justify-content-between align-items-center"
                       style="font-size:13px;padding:.35rem .75rem;"
                       title="<?= htmlspecialchars($sp['name']) ?>">
                        <span>
                            <i class="fa fa-circle-dot me-2"
                               style="color:<?= $isActive ? '#fff' : '#0078D4' ?>;
                                      font-size:10px;"></i>
                            <?= htmlspecialchars(mb_substr($sp['name'], 0, 18)) ?>
                            <?= mb_strlen($sp['name']) > 18 ? '...' : '' ?>
                        </span>
                        <?php if ($openCount > 0): ?>
                        <span class="badge rounded-pill"
                              style="background:<?= $isActive ? 'rgba(255,255,255,.3)' : '#E3F2FD' ?>;
                                     color:<?= $isActive ? '#fff' : '#0078D4' ?>;
                                     font-size:10px;">
                            <?= $openCount ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php
                        endforeach;
                    } else {
                ?>
                <li>
                    <a href="<?= APP_URL ?>/projects/new"
                       class="nav-link text-muted"
                       style="font-size:13px;padding:.35rem .75rem;">
                        <i class="fa fa-plus me-2" style="font-size:10px;"></i>
                        Tạo dự án mới
                    </a>
                </li>
                <?php
                    }
                } catch (Exception $e) {
                    // DB chưa sẵn sàng hoặc lỗi khác — không crash layout
                ?>
                <li>
                    <span class="nav-link text-muted" style="font-size:12px;">
                        Chưa có dự án
                    </span>
                </li>
                <?php } }  ?>
        </ul>

        <!-- Spacer + footer sidebar -->
        <div class="mt-auto pt-3 border-top">
            <a href="<?= APP_URL ?>/profile"
               class="d-flex align-items-center gap-2 text-decoration-none text-muted p-2
                      rounded hover-bg"
               style="font-size:12px;transition:.15s;"
               onmouseover="this.style.background='#F5F8FC'"
               onmouseout="this.style.background='transparent'">
                <?php if (!empty($_SESSION['user_avatar'])): ?>
                <img src="<?= APP_URL ?>/uploads/<?= htmlspecialchars($_SESSION['user_avatar']) ?>"
                     class="rounded-circle"
                     width="28" height="28"
                     style="object-fit:cover;" alt="">
                <?php else: ?>
                <div class="rounded-circle d-flex align-items-center justify-content-center
                            fw-bold flex-shrink-0"
                     style="width:28px;height:28px;background:#E3F2FD;
                            color:#0078D4;font-size:12px;">
                    <?= mb_strtoupper(mb_substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                </div>
                <?php endif; ?>
                <div class="text-truncate">
                    <div class="fw-semibold text-dark" style="font-size:12px;line-height:1.2;">
                        <?= htmlspecialchars(mb_substr($_SESSION['user_name'] ?? '', 0, 20)) ?>
                    </div>
                    <div style="font-size:11px;">
                        <?= htmlspecialchars($_SESSION['user_role'] ?? '') ?>
                    </div>
                </div>
            </a>
        </div>
    </aside>

    <!-- Main content area -->
    <main class="flex-grow-1 p-4" style="min-width:0;">

        <!-- Flash message -->
        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?>
                    alert-dismissible fade show mb-3"
             role="alert">
            <i class="fa <?= match($_SESSION['flash']['type']) {
                'success' => 'fa-check-circle',
                'danger'  => 'fa-times-circle',
                'warning' => 'fa-exclamation-triangle',
                default   => 'fa-info-circle',
            } ?> me-2"></i>
            <?= $_SESSION['flash']['message'] /* HTML được phép — đã sanitize ở controller */ ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <!-- Nội dung view -->
        <?= $content ?? '' ?>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="<?= APP_URL ?>/public/js/app.js"></script>

<script>
// ── Global Search Ctrl+K ──
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const s = document.getElementById('globalSearch');
        if (s) { s.focus(); s.select(); }
    }
});

// ── Đóng search khi click ra ngoài ──
document.addEventListener('click', function(e) {
    const results = document.getElementById('searchResults');
    if (results && !e.target.closest('#globalSearch') && !e.target.closest('#searchResults')) {
        results.classList.add('d-none');
    }
});

// ── Auto-hide flash sau 4 giây ──
document.querySelectorAll('.alert').forEach(function(el) {
    setTimeout(function() {
        el.classList.remove('show');
        setTimeout(() => el.remove(), 300);
    }, 4000);
});
</script>

</body>
</html>