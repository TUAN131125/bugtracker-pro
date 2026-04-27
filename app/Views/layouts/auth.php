<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'BugTracker Pro') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/public/css/auth.css" rel="stylesheet">
</head>
<body style="background:linear-gradient(135deg,#0F2A4A 0%,#1565C0 100%); min-height:100vh;">
<div class="d-flex justify-content-center align-items-center" style="min-height:100vh; padding:2rem 0;">
    <div class="w-100" style="max-width:480px;">

        <!-- Logo -->
        <div class="text-center mb-4">
            <a href="<?= APP_URL ?>/" class="text-decoration-none">
                <i class="fa-solid fa-bug fa-2x text-white me-2"></i>
                <span class="fs-3 fw-bold text-white">BugTracker Pro</span>
            </a>
        </div>

        <!-- Progress bar (hiển thị khi đang trong flow đăng ký) -->
        <?php if (!empty($step)): ?>
        <div class="mb-3">
            <?php
            $steps = ['Tạo tài khoản','Thông tin','Workspace','Mời thành viên'];
            $percent = ($step / 4) * 100;
            ?>
            <div class="d-flex justify-content-between mb-1">
                <?php foreach ($steps as $i => $label): ?>
                <small class="<?= ($i + 1) <= $step ? 'text-white fw-bold' : 'text-white-50' ?>">
                    <?= $i+1 ?>. <?= $label ?>
                </small>
                <?php endforeach; ?>
            </div>
            <div class="progress" style="height:4px;">
                <div class="progress-bar bg-info" style="width:<?= $percent ?>%"></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card chứa form -->
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-body p-4">
                <?= $content ?>
            </div>
        </div>

        <!-- Footer link -->
        <p class="text-center text-white-50 mt-3 small">
            &copy; 2025 BugTracker Pro — Miễn phí mãi mãi
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/public/js/auth.js"></script>
</body>
</html>