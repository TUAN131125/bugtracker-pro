<?php
/** @var array  $projects */
/** @var string $title    */
$projects = $projects ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="fa fa-folder me-2 text-primary"></i>Dự Án Của Tôi
        </h4>
        <small class="text-muted">Tất cả dự án bạn đang tham gia</small>
    </div>
    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin','manager'])): ?>
    <a href="<?= APP_URL ?>/projects/new" class="btn btn-primary fw-bold">
        <i class="fa fa-plus me-1"></i>Tạo Dự Án Mới
    </a>
    <?php endif; ?>
</div>

<?php if (empty($projects)): ?>
<!-- Empty state -->
<div class="card text-center py-5">
    <div class="py-3">
        <i class="fa fa-folder-open fa-3x text-muted mb-3 d-block"></i>
        <h5 class="text-muted">Bạn chưa tham gia dự án nào</h5>
        <p class="text-muted mb-4">Tạo dự án mới hoặc chờ được mời vào dự án của team</p>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['admin','manager'])): ?>
        <a href="<?= APP_URL ?>/projects/new" class="btn btn-primary px-4">
            <i class="fa fa-plus me-2"></i>Tạo Dự Án Đầu Tiên
        </a>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<!-- Grid dự án -->
<div class="row g-3">
    <?php foreach ($projects as $proj): ?>
    <?php
    $statusColor = match($proj['status'] ?? 'active') {
        'active'   => '#28A745',
        'archived' => '#6C757D',
        'closed'   => '#DC3545',
        default    => '#0078D4',
    };
    $visIcon = match($proj['visibility'] ?? 'private') {
        'public'    => 'fa-globe',
        'team_only' => 'fa-users',
        default     => 'fa-lock',
    };
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 project-card"
             style="border-top:3px solid <?= $statusColor ?>;
                    transition:.2s;cursor:pointer;"
             onclick="window.location='<?= APP_URL ?>/projects/<?= e(strtolower($proj['key'])) ?>'">
            <div class="card-body p-3">

                <!-- Header card -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Avatar project (chữ cái đầu) -->
                        <div style="width:40px;height:40px;border-radius:8px;
                                    background:<?= $statusColor ?>22;
                                    display:flex;align-items:center;justify-content:center;
                                    font-weight:800;color:<?= $statusColor ?>;font-size:14px;">
                            <?= mb_strtoupper(mb_substr($proj['name'], 0, 2)) ?>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:14px;color:#1A2332;">
                                <?= e($proj['name']) ?>
                            </div>
                            <span style="font-family:monospace;font-size:11px;
                                         color:#6B7A8F;background:#F5F7FA;
                                         padding:1px 6px;border-radius:4px;">
                                <?= e($proj['key']) ?>
                            </span>
                        </div>
                    </div>
                    <i class="fa <?= $visIcon ?> text-muted" style="font-size:12px;"
                       title="<?= e($proj['visibility'] ?? 'private') ?>"></i>
                </div>

                <!-- Mô tả -->
                <?php if (!empty($proj['description'])): ?>
                <p class="text-muted mb-2" style="font-size:12px;line-height:1.5;">
                    <?= e(truncate($proj['description'], 80)) ?>
                </p>
                <?php endif; ?>

                <!-- Stats -->
                <div class="d-flex gap-3 mt-2 pt-2"
                     style="border-top:1px solid #F0F4F8;font-size:12px;">
                    <span class="text-muted">
                        <i class="fa fa-circle-dot me-1" style="color:#0078D4;"></i>
                        <strong><?= $proj['open_bugs'] ?? 0 ?></strong> open
                    </span>
                    <span class="text-muted">
                        <i class="fa fa-users me-1"></i>
                        <strong><?= $proj['member_count'] ?? 0 ?></strong> thành viên
                    </span>
                    <span class="text-muted ms-auto">
                        <i class="fa fa-clock me-1"></i>
                        <?= timeAgo($proj['created_at']) ?>
                    </span>
                </div>
            </div>

            <!-- Footer card -->
            <div class="card-footer bg-transparent px-3 py-2"
                 style="border-top:1px solid #F0F4F8;">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge"
                          style="background:<?= $statusColor ?>22;
                                 color:<?= $statusColor ?>;font-size:11px;">
                        <?= ucfirst($proj['status'] ?? 'active') ?>
                    </span>
                    <a href="<?= APP_URL ?>/projects/<?= e(strtolower($proj['key'])) ?>"
                       class="btn btn-sm btn-outline-primary"
                       style="font-size:11px;padding:2px 10px;"
                       onclick="event.stopPropagation()">
                        Xem Issues →
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Total count -->
<p class="text-muted mt-3 mb-0" style="font-size:13px;">
    <i class="fa fa-info-circle me-1"></i>
    Tổng cộng <?= count($projects) ?> dự án
</p>
<?php endif; ?>

<style>
.project-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 24px rgba(0,120,212,.12);
}
</style>