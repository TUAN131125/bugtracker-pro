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

<!-- ── NAVBAR ── -->
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
                <input type="text" id="globalSearch" class="form-control border-start-0"
                       placeholder="Tìm kiếm issue, project... (Ctrl+K)">
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 ms-3">
            <!-- Nút tạo issue mới -->
            <a href="<?= APP_URL ?>/projects" class="btn btn-sm btn-primary">
                <i class="fa fa-plus me-1"></i>Tạo Issue
            </a>

            <!-- Notification bell -->
            <div class="dropdown">
                <button class="btn btn-link text-white position-relative p-1" data-bs-toggle="dropdown">
                    <i class="fa fa-bell fa-lg"></i>
                    <?php if (!empty($unread_notifications)): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:10px;">
                        <?= $unread_notifications ?>
                    </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow" style="width:340px;">
                    <div class="dropdown-header fw-bold">Thông báo</div>
                    <div class="dropdown-item text-muted text-center py-3">
                        <i class="fa fa-bell-slash"></i> Chưa có thông báo
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?= APP_URL ?>/notifications" class="dropdown-item text-center text-primary small">
                        Xem tất cả
                    </a>
                </div>
            </div>

            <!-- Avatar + user menu -->
            <div class="dropdown">
                <button class="btn btn-link p-0" data-bs-toggle="dropdown">
                    <img src="<?= APP_URL ?>/public/img/default-avatar.png"
                         class="rounded-circle border border-secondary"
                         width="34" height="34" alt="Avatar">
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li class="dropdown-header">
                        <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/profile"><i class="fa fa-user me-2"></i>Hồ sơ</a></li>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/settings"><i class="fa fa-gear me-2"></i>Cài đặt</a></li>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/admin"><i class="fa fa-shield me-2"></i>Quản trị</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout"><i class="fa fa-right-from-bracket me-2"></i>Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- ── SIDEBAR + CONTENT ── -->
<div class="d-flex" style="min-height:calc(100vh - 56px);">

    <!-- Sidebar -->
    <aside class="d-none d-lg-flex flex-column flex-shrink-0 p-3"
           style="width:220px; background:#fff; border-right:1px solid #dee2e6;">
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item">
                <a href="<?= APP_URL ?>/dashboard"
                   class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : 'text-dark' ?>">
                    <i class="fa fa-gauge me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/projects"
                   class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/projects') ? 'active' : 'text-dark' ?>">
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

        <hr>
        <p class="text-muted small px-2 mb-1 fw-semibold">DỰ ÁN GẦN ĐÂY</p>
        <ul class="nav nav-pills flex-column gap-1">
            <!-- Sẽ được render động từ DB ở ngày 3 -->
            <li><a href="#" class="nav-link text-dark text-truncate small">
                <i class="fa fa-circle-dot me-2 text-primary"></i>Sample Project
            </a></li>
        </ul>
    </aside>

    <!-- Main content area -->
    <main class="flex-grow-1 p-4">
        <!-- Flash message (nếu có) -->
        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <!-- Nội dung view được nhét vào đây -->
        <?= $content ?>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>