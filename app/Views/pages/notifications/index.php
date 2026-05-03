<?php
/** @var array $notifications */
/** @var int   $page          */
/** @var int   $totalPages    */
/** @var int   $total         */
/** @var int   $unread        */
$notifications = $notifications ?? [];
$unread        = $unread ?? 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="fa fa-bell me-2 text-primary"></i>Thông Báo
            <?php if ($unread > 0): ?>
            <span class="badge bg-danger ms-1"><?= $unread ?></span>
            <?php endif; ?>
        </h4>
        <small class="text-muted">Tổng <?= $total ?> thông báo</small>
    </div>
    <?php if ($unread > 0): ?>
    <a href="<?= APP_URL ?>/notifications/read-all"
       class="btn btn-outline-primary btn-sm">
        <i class="fa fa-check-double me-1"></i>Đánh dấu tất cả đã đọc
    </a>
    <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
<div class="card text-center py-5">
    <i class="fa fa-bell-slash fa-3x text-muted mb-3 d-block"></i>
    <h5 class="text-muted">Chưa có thông báo nào</h5>
    <p class="text-muted">Thông báo sẽ xuất hiện khi có hoạt động liên quan đến bạn</p>
</div>
<?php else: ?>
<div class="card">
    <?php
    $typeIcons = [
        'issue_assigned'  => ['fa-user-check',  '#0078D4', '#E3F2FD'],
        'comment_added'   => ['fa-comment',      '#6A1B9A', '#F3E5F5'],
        'status_changed'  => ['fa-arrows-rotate','#FD7E14', '#FFF3E0'],
        'mentioned'       => ['fa-at',           '#28A745', '#E8F5E9'],
        'due_date'        => ['fa-calendar-xmark','#DC3545','#FFEBEE'],
    ];
    foreach ($notifications as $notif):
        [$icon, $color, $bg] = $typeIcons[$notif['type']] ?? ['fa-bell','#6C757D','#F5F7FA'];
        $isUnread = !$notif['is_read'];
    ?>
    <div class="d-flex gap-3 p-3 border-bottom <?= $isUnread ? '' : 'opacity-75' ?>"
         style="<?= $isUnread ? 'background:#F8FBFF;' : '' ?>">

        <!-- Icon -->
        <div style="width:40px;height:40px;border-radius:10px;
                    background:<?= $bg ?>;flex-shrink:0;
                    display:flex;align-items:center;justify-content:center;">
            <i class="fa <?= $icon ?>" style="color:<?= $color ?>;"></i>
        </div>

        <!-- Content -->
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex justify-content-between align-items-start">
                <div class="fw-<?= $isUnread ? 'bold' : 'normal' ?>"
                     style="font-size:14px;">
                    <?= htmlspecialchars($notif['title']) ?>
                </div>
                <div class="d-flex align-items-center gap-2 ms-2 flex-shrink-0">
                    <?php if ($isUnread): ?>
                    <span style="width:8px;height:8px;border-radius:50%;
                                 background:#0078D4;display:inline-block;"></span>
                    <?php endif; ?>
                    <small class="text-muted">
                        <?= timeAgo($notif['created_at']) ?>
                    </small>
                </div>
            </div>
            <?php if (!empty($notif['message'])): ?>
            <div class="text-muted mt-1" style="font-size:13px;">
                <?= htmlspecialchars($notif['message']) ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($notif['link'])): ?>
            <a href="<?= APP_URL ?>/notifications/read/<?= $notif['id'] ?>"
               class="btn btn-sm btn-outline-primary mt-2"
               style="font-size:12px;padding:2px 10px;">
                Xem chi tiết →
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>