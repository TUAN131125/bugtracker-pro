<?php
// $stats, $myBugs, $recentActivity, $projects, $upcomingDeadlines
?>

<h4 class="fw-bold mb-4">
    <i class="fa fa-gauge me-2 text-primary"></i>
    Xin chào, <?= e(explode(' ', $_SESSION['user_name'])[0]) ?>! 👋
</h4>

<!-- 4 Widget thống kê nhanh -->
<div class="row g-3 mb-4">
    <?php
    $widgets = [
        ['Bugs được giao',   $stats['assigned_to_me'] ?? 0, '#0078D4', 'fa-user-check',    '#E3F2FD'],
        ['Đang xử lý',       $stats['in_progress']    ?? 0, '#FD7E14', 'fa-spinner',       '#FFF3E0'],
        ['Quá hạn',          $stats['overdue']        ?? 0, '#DC3545', 'fa-clock',         '#FFEBEE'],
        ['Resolved hôm nay', $stats['resolved_today'] ?? 0, '#28A745', 'fa-check-circle',  '#E8F5E9'],
    ];
    foreach ($widgets as [$label, $count, $color, $icon, $bg]):
    ?>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3 h-100" style="border-left:4px solid <?= $color ?>;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:2rem;font-weight:800;color:<?= $color ?>;">
                        <?= $count ?>
                    </div>
                    <div style="font-size:13px;color:#6B7A8F;"><?= $label ?></div>
                </div>
                <div style="width:48px;height:48px;border-radius:12px;background:<?= $bg ?>;
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fa <?= $icon ?>" style="color:<?= $color ?>;font-size:1.3rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Cột trái -->
    <div class="col-lg-7">

        <!-- My Bugs -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">
                    <i class="fa fa-bug me-2 text-danger"></i>Bugs Được Giao Cho Tôi
                </h6>
                <span class="badge bg-primary"><?= count($myBugs) ?></span>
            </div>
            <?php if (empty($myBugs)): ?>
            <div class="card-body text-center text-muted py-4">
                <i class="fa fa-check-circle fa-2x mb-2 text-success"></i>
                <p class="mb-0">Không có bug nào đang chờ xử lý!</p>
            </div>
            <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($myBugs as $bug): ?>
                <a href="<?= APP_URL ?>/issues/<?= e($bug['issue_key']) ?>"
                   class="list-group-item list-group-item-action py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                            <span class="text-primary fw-bold"
                                  style="font-family:monospace;font-size:12px;white-space:nowrap;">
                                <?= e($bug['issue_key']) ?>
                            </span>
                            <span class="text-truncate" style="font-size:13px;">
                                <?= e(truncate($bug['title'], 55)) ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-1 ms-2 flex-shrink-0">
                            <?= priorityBadge($bug['priority']) ?>
                            <?= statusBadge($bug['status']) ?>
                            <?php if (isOverdue($bug['due_date'], $bug['status'])): ?>
                            <span class="badge bg-danger" style="font-size:10px;">Quá hạn</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming Deadlines -->
        <?php if (!empty($upcomingDeadlines)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fa fa-calendar-exclamation me-2 text-warning"></i>Sắp Đến Hạn (7 ngày)
                </h6>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($upcomingDeadlines as $bug): ?>
                <a href="<?= APP_URL ?>/issues/<?= e($bug['issue_key']) ?>"
                   class="list-group-item list-group-item-action py-2 px-3">
                    <div class="d-flex justify-content-between">
                        <span style="font-size:13px;"><?= e(truncate($bug['title'], 50)) ?></span>
                        <span class="badge bg-warning text-dark" style="font-size:11px;">
                            <?= formatDate($bug['due_date']) ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Cột phải -->
    <div class="col-lg-5">

        <!-- Projects -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">
                    <i class="fa fa-folder me-2 text-primary"></i>Dự Án Của Tôi
                </h6>
                <a href="<?= APP_URL ?>/projects"
                   class="btn btn-outline-primary btn-sm" style="font-size:11px;">Xem tất cả</a>
            </div>
            <?php if (empty($projects)): ?>
            <div class="card-body text-center text-muted py-3">
                <p class="mb-0 small">Chưa có dự án nào</p>
                <a href="<?= APP_URL ?>/projects/new" class="btn btn-primary btn-sm mt-2">
                    Tạo dự án mới
                </a>
            </div>
            <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach (array_slice($projects, 0, 5) as $proj): ?>
                <a href="<?= APP_URL ?>/projects/<?= e(strtolower($proj['key'])) ?>"
                   class="list-group-item list-group-item-action py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold" style="font-size:13px;">
                                <?= e($proj['name']) ?>
                            </div>
                            <small class="text-muted">
                                <?= $proj['open_bugs'] ?? 0 ?> open ·
                                <?= $proj['member_count'] ?? 0 ?> thành viên
                            </small>
                        </div>
                        <span class="badge bg-light text-dark border"
                              style="font-family:monospace;font-size:11px;">
                            <?= e($proj['key']) ?>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h6 class="fw-bold mb-0">
                    <i class="fa fa-history me-2 text-secondary"></i>Hoạt Động Gần Đây
                </h6>
            </div>
            <?php if (empty($recentActivity)): ?>
            <div class="card-body text-center text-muted py-3">
                <p class="mb-0 small">Chưa có hoạt động nào</p>
            </div>
            <?php else: ?>
            <div class="card-body p-2">
                <?php foreach (array_slice($recentActivity, 0, 10) as $act): ?>
                <div class="d-flex gap-2 py-1 border-bottom" style="font-size:12px;">
                    <div style="width:6px;height:6px;border-radius:50%;
                                background:#0078D4;margin-top:5px;flex-shrink:0;"></div>
                    <div class="text-truncate">
                        <strong><?= e(explode(' ', $act['user_name'])[0]) ?></strong>
                        <?= e($act['action']) ?>
                        <?php if (!empty($act['bug_key'])): ?>
                        <a href="<?= APP_URL ?>/issues/<?= e($act['bug_key']) ?>"
                           class="text-primary text-decoration-none">
                            <?= e($act['bug_key']) ?>
                        </a>
                        <?php endif; ?>
                        <span class="text-muted d-block">
                            <?= timeAgo($act['created_at']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>