<?php
$project    = $project    ?? [];
$bugs       = $bugs       ?? [];
$stats      = $stats      ?? [];
$members    = $members    ?? [];
$filters    = array_merge([
    'search'   => '',
    'status'   => '',
    'priority' => '',
    'type'     => '',
    'sort'     => 'newest',
], $filters ?? []);
$page       = $page       ?? 1;
$totalPages = $totalPages ?? 1;
$totalBugs  = $totalBugs  ?? 0;
?>

<!-- Stats bar -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="fa fa-folder-open me-2 text-primary"></i>
            <?= e($project['name']) ?>
            <span class="badge bg-secondary ms-2" style="font-size:12px;">
                <?= e($project['key']) ?>
            </span>
        </h4>
        <small class="text-muted"><?= e($project['description'] ?? '') ?></small>
    </div>
    <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/issues/new"
       class="btn btn-primary btn-sm fw-bold">
        <i class="fa fa-plus me-1"></i>Tạo Issue
    </a>
</div>

<!-- Quick stats -->
<div class="row g-2 mb-3">
    <?php
    $statItems = [
        ['Tổng',        $stats['total']       ?? 0, '#6C757D', 'fa-list'],
        ['Mở',          $stats['open']         ?? 0, '#0078D4', 'fa-circle-dot'],
        ['Đang xử lý',  $stats['in_progress']  ?? 0, '#FD7E14', 'fa-spinner'],
        ['Resolved',    $stats['resolved']     ?? 0, '#28A745', 'fa-check-circle'],
        ['Nghiêm trọng',$stats['critical']     ?? 0, '#DC3545', 'fa-exclamation-triangle'],
    ];
    foreach ($statItems as [$label, $count, $color, $icon]):
    ?>
    <div class="col">
        <div class="card text-center p-2" style="border-top:3px solid <?= $color ?>;">
            <div class="fw-bold" style="font-size:1.4rem;color:<?= $color ?>;">
                <?= $count ?>
            </div>
            <small class="text-muted"><?= $label ?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filter bar -->
<form method="GET" class="card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text"
                   name="q"
                   class="form-control form-control-sm"
                   placeholder="🔍 Tìm kiếm issue..."
                   value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">Tất cả status</option>
                <?php foreach (['open','in_progress','review','resolved','closed'] as $s): ?>
                <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>>
                    <?= ucfirst(str_replace('_',' ',$s)) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="priority" class="form-select form-select-sm">
                <option value="">Tất cả priority</option>
                <?php foreach (['critical','high','medium','low','trivial'] as $p): ?>
                <option value="<?= $p ?>" <?= $filters['priority'] === $p ? 'selected' : '' ?>>
                    <?= ucfirst($p) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select form-select-sm">
                <option value="">Tất cả loại</option>
                <?php foreach (['bug','feature','task','improvement','question'] as $t): ?>
                <option value="<?= $t ?>" <?= $filters['type'] === $t ? 'selected' : '' ?>>
                    <?= ucfirst($t) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="sort" class="form-select form-select-sm">
                <option value="newest"  <?= $filters['sort'] === 'newest'  ? 'selected':'' ?>>Mới nhất</option>
                <option value="updated" <?= $filters['sort'] === 'updated' ? 'selected':'' ?>>Cập nhật</option>
                <option value="priority"<?= $filters['sort'] === 'priority'? 'selected':'' ?>>Priority</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="fa fa-filter"></i>
            </button>
        </div>
    </div>
</form>

<!-- Issue table -->
<?php if (empty($bugs)): ?>
<div class="card text-center p-5">
    <i class="fa fa-bug fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">Chưa có issue nào</h5>
    <p class="text-muted mb-3">Tạo issue đầu tiên để bắt đầu tracking</p>
    <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/issues/new"
       class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>Tạo Issue đầu tiên
    </a>
</div>
<?php else: ?>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:13px;">
            <thead class="table-light">
                <tr>
                    <th style="width:100px;">Key</th>
                    <th>Tiêu đề</th>
                    <th style="width:90px;">Type</th>
                    <th style="width:100px;">Status</th>
                    <th style="width:90px;">Priority</th>
                    <th style="width:120px;">Phụ trách</th>
                    <th style="width:100px;">Cập nhật</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bugs as $bug): ?>
                <tr onclick="window.location='<?= APP_URL ?>/issues/<?= e($bug['issue_key']) ?>'"
                    style="cursor:pointer;">
                    <td>
                        <a href="<?= APP_URL ?>/issues/<?= e($bug['issue_key']) ?>"
                           class="text-primary fw-bold text-decoration-none"
                           style="font-family:monospace;">
                            <?= e($bug['issue_key']) ?>
                        </a>
                    </td>
                    <td>
                        <span class="fw-500"><?= e(truncate($bug['title'], 70)) ?></span>
                        <?php if (isOverdue($bug['due_date'], $bug['status'])): ?>
                        <span class="badge bg-danger ms-1" style="font-size:10px;">Quá hạn</span>
                        <?php endif; ?>
                    </td>
                    <td><?= typeBadge($bug['type']) ?></td>
                    <td><?= statusBadge($bug['status']) ?></td>
                    <td><?= priorityBadge($bug['priority']) ?></td>
                    <td>
                        <?php if (!empty($bug['assignee_name'])): ?>
                        <div class="d-flex align-items-center gap-1">
                            <?php if (!empty($bug['assignee_avatar'])): ?>
                            <img src="<?= APP_URL ?>/uploads/<?= e($bug['assignee_avatar']) ?>"
                                 style="width:20px;height:20px;border-radius:50%;" alt="">
                            <?php endif; ?>
                            <span><?= e(explode(' ', $bug['assignee_name'])[0]) ?></span>
                        </div>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= timeAgo($bug['updated_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>">
                ← Trước
            </a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">
                Tiếp →
            </a>
        </li>
    </ul>
    <p class="text-center text-muted" style="font-size:12px;">
        Hiển thị <?= count($bugs) ?> / <?= $totalBugs ?> issues
    </p>
</nav>
<?php endif; ?>
<?php endif; ?>