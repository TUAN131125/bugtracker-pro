<?php
// app/Views/pages/kanban/sprint.php
// Sprint Board — Dev A Ngày 4

$priorityColors = [
    'critical' => '#DC3545', 'high' => '#FD7E14',
    'medium' => '#FFC107',   'low'  => '#28A745', 'trivial' => '#6C757D',
];
$priorityLabels = [
    'critical' => 'Nghiêm trọng', 'high' => 'Cao',
    'medium' => 'Trung bình',     'low'  => 'Thấp', 'trivial' => 'Không đáng kể',
];
$statusLabels = [
    'open' => 'Mở', 'in_progress' => 'Đang xử lý',
    'review' => 'Review', 'resolved' => 'Đã giải quyết', 'closed' => 'Đóng',
];
?>

<style>
.sprint-card {
    background: #fff;
    border: 1.5px solid #E0E7EF;
    border-radius: 10px;
    padding: 14px 18px;
    transition: box-shadow .15s;
}
.sprint-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.08); }

.sprint-active-badge {
    background: #E3F9E5; color: #155724; border-radius: 20px;
    padding: 2px 10px; font-size: 11px; font-weight: 700;
}

.backlog-item {
    background: #fff; border: 1px solid #E0E7EF; border-radius: 8px;
    padding: 10px 14px; display: flex; align-items: center; gap: 12px;
    cursor: grab; transition: box-shadow .12s;
    user-select: none;
}
.backlog-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.backlog-item.dragging { opacity: .4; cursor: grabbing; }

.sprint-drop-zone {
    min-height: 60px; border: 2px dashed #d0d5dd; border-radius: 8px;
    padding: 8px; transition: border-color .2s, background .2s;
}
.sprint-drop-zone.drag-over { border-color: #0078D4; background: #EBF4FF; }

.issue-key-link {
    font-family: monospace; font-size: 12px; font-weight: 700;
    color: #0078D4; text-decoration: none; white-space: nowrap;
}
.issue-key-link:hover { text-decoration: underline; }

.priority-dot-sm {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
</style>

<!-- ── Header ── -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:13px;">
                <li class="breadcrumb-item">
                    <a href="<?= APP_URL ?>/projects" class="text-decoration-none">Dự án</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>"
                       class="text-decoration-none"><?= e($project['name']) ?></a>
                </li>
                <li class="breadcrumb-item active">Sprint Board</li>
            </ol>
        </nav>
        <h4 class="mb-0 fw-bold" style="color:#1E3A5F;">
            <i class="fa fa-rocket me-2 text-primary"></i>Sprint Board
        </h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/board"
           class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-columns me-1"></i>Kanban
        </a>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createSprintModal">
            <i class="fa fa-plus me-1"></i>Tạo Sprint
        </button>
    </div>
</div>

<input type="hidden" id="csrfToken" value="<?= e($csrf_token) ?>">
<input type="hidden" id="projectKey" value="<?= e(strtolower($project['key'])) ?>">
<input type="hidden" id="appUrl" value="<?= e(APP_URL) ?>">

<div class="row g-4">

    <!-- ════ CỘT TRÁI: Burndown + Sprints ════ -->
    <div class="col-lg-7">

        <!-- Burndown Chart -->
        <?php if ($activeSprint && !empty($burndownData)): ?>
        <div class="sprint-card mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h6 class="fw-bold mb-0" style="color:#1E3A5F;">
                        <i class="fa fa-chart-line me-2 text-primary"></i>Burndown Chart
                    </h6>
                    <small class="text-muted">
                        <?= e($activeSprint['name']) ?> &bull;
                        <?= e(date('d/m', strtotime($activeSprint['start_date'] ?? 'today'))) ?>
                        →
                        <?= e(date('d/m', strtotime($activeSprint['end_date'] ?? 'today'))) ?>
                    </small>
                </div>
                <span class="sprint-active-badge">
                    <i class="fa fa-play me-1"></i>Đang chạy
                </span>
            </div>
            <canvas id="burndownChart" height="100"></canvas>
        </div>
        <?php endif; ?>

        <!-- Danh sách Sprints -->
        <div class="sprint-card">
            <h6 class="fw-bold mb-3" style="color:#1E3A5F;">
                <i class="fa fa-list me-2 text-primary"></i>Tất cả Sprint
            </h6>

            <?php if (empty($sprints)): ?>
            <div class="text-center text-muted py-4">
                <i class="fa fa-rocket fa-2x d-block mb-2 opacity-50"></i>
                Chưa có sprint nào. Hãy tạo sprint đầu tiên!
            </div>
            <?php else: ?>
            <div class="d-flex flex-column gap-3">
            <?php foreach ($sprints as $sprint): ?>
            <?php
                $pct = ($sprint['total_issues'] > 0)
                    ? round($sprint['done_issues'] / $sprint['total_issues'] * 100)
                    : 0;
                $statusColors = [
                    'planning'  => ['bg' => '#E3F2FD', 'text' => '#0078D4', 'label' => 'Lên kế hoạch'],
                    'active'    => ['bg' => '#E3F9E5', 'text' => '#155724', 'label' => 'Đang chạy'],
                    'completed' => ['bg' => '#F8F9FA', 'text' => '#6C757D', 'label' => 'Hoàn thành'],
                ];
                $sc = $statusColors[$sprint['status']] ?? $statusColors['planning'];
            ?>
            <div class="border rounded p-3" style="background:#FAFBFD;">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold" style="color:#1E3A5F;"><?= e($sprint['name']) ?></span>
                            <span class="badge rounded-pill"
                                  style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;
                                         font-size:10px;font-weight:600;">
                                <?= $sc['label'] ?>
                            </span>
                        </div>
                        <?php if ($sprint['goal']): ?>
                        <small class="text-muted"><?= e($sprint['goal']) ?></small>
                        <?php endif; ?>
                        <?php if ($sprint['start_date'] && $sprint['end_date']): ?>
                        <div class="text-muted mt-1" style="font-size:12px;">
                            <i class="fa fa-calendar me-1"></i>
                            <?= e(date('d/m/Y', strtotime($sprint['start_date']))) ?>
                            —
                            <?= e(date('d/m/Y', strtotime($sprint['end_date']))) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-1 flex-shrink-0">
                        <?php if ($sprint['status'] === 'planning'
                            && in_array($_SESSION['user_role'] ?? '', ['admin','manager'])): ?>
                        <form method="POST"
                              action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/sprint/<?= $sprint['id'] ?>/start">
                            <input type="hidden" name="csrf_token"
                                   value="<?= e($csrf_token) ?>">
                            <button class="btn btn-sm btn-success" type="submit"
                                    onclick="return confirm('Bắt đầu sprint này?')">
                                <i class="fa fa-play me-1"></i>Bắt đầu
                            </button>
                        </form>
                        <?php elseif ($sprint['status'] === 'active'
                            && in_array($_SESSION['user_role'] ?? '', ['admin','manager'])): ?>
                        <form method="POST"
                              action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/sprint/<?= $sprint['id'] ?>/complete">
                            <input type="hidden" name="csrf_token"
                                   value="<?= e($csrf_token) ?>">
                            <input type="hidden" name="move_unfinished" value="backlog">
                            <button class="btn btn-sm btn-outline-secondary" type="submit"
                                    onclick="return confirm('Hoàn thành sprint? Issue chưa xong sẽ về Backlog.')">
                                <i class="fa fa-flag-checkered me-1"></i>Hoàn thành
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Progress -->
                <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height:6px;">
                        <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                    </div>
                    <small class="text-muted" style="white-space:nowrap;font-size:11px;">
                        <?= (int)$sprint['done_issues'] ?>/<?= (int)$sprint['total_issues'] ?> done
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /col-left -->

    <!-- ════ CỘT PHẢI: Backlog ════ -->
    <div class="col-lg-5">
        <div class="sprint-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0" style="color:#1E3A5F;">
                    <i class="fa fa-inbox me-2 text-primary"></i>Backlog
                    <span class="badge bg-secondary ms-1" style="font-size:11px;">
                        <?= count($backlog) ?>
                    </span>
                </h6>
                <?php if ($activeSprint): ?>
                <small class="text-muted" style="font-size:11px;">
                    Kéo issue vào sprint: <strong><?= e($activeSprint['name']) ?></strong>
                </small>
                <?php endif; ?>
            </div>

            <!-- Drop zone: kéo vào sprint active -->
            <?php if ($activeSprint): ?>
            <div class="sprint-drop-zone mb-3" id="sprintDropZone"
                 data-sprint-id="<?= $activeSprint['id'] ?>"
                 ondragover="event.preventDefault();this.classList.add('drag-over')"
                 ondragleave="this.classList.remove('drag-over')"
                 ondrop="dropToSprint(event, <?= $activeSprint['id'] ?>)">
                <div class="text-center text-muted" style="font-size:12px;pointer-events:none;">
                    <i class="fa fa-arrow-down me-1"></i>
                    Kéo issue vào đây để thêm vào <strong><?= e($activeSprint['name']) ?></strong>
                </div>
            </div>
            <?php endif; ?>

            <!-- Backlog items -->
            <div id="backlogList" class="d-flex flex-column gap-2">

                <?php if (empty($backlog)): ?>
                <div class="text-center text-muted py-4" style="font-size:13px;">
                    <i class="fa fa-check-circle fa-2x d-block mb-2 text-success opacity-75"></i>
                    Backlog trống — mọi issue đã vào sprint!
                </div>
                <?php endif; ?>

                <?php foreach ($backlog as $issue): ?>
                <?php
                    $priorityColor = $priorityColors[$issue['priority']] ?? '#6C757D';
                    $assigneeInit  = $issue['assignee_name']
                        ? mb_strtoupper(mb_substr($issue['assignee_name'], 0, 1)) : null;
                ?>
                <div class="backlog-item"
                     data-bug-id="<?= $issue['id'] ?>"
                     data-key="<?= e($issue['issue_key']) ?>"
                     draggable="<?= $activeSprint ? 'true' : 'false' ?>"
                     ondragstart="backlogDragStart(event)"
                     ondragend="backlogDragEnd(event)">

                    <!-- Priority dot -->
                    <span class="priority-dot-sm"
                          style="background:<?= $priorityColor ?>"
                          title="<?= e($priorityLabels[$issue['priority']] ?? '') ?>"></span>

                    <!-- Key + title -->
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= APP_URL ?>/issues/<?= e(strtolower($issue['issue_key'])) ?>"
                               class="issue-key-link"
                               onclick="event.stopPropagation()">
                                <?= e($issue['issue_key']) ?>
                            </a>
                            <span class="text-truncate text-dark" style="font-size:13px;">
                                <?= e($issue['title']) ?>
                            </span>
                        </div>
                        <div class="text-muted" style="font-size:11px;margin-top:2px;">
                            <?= e($statusLabels[$issue['status']] ?? $issue['status']) ?>
                            <?php if ($issue['estimated_hours']): ?>
                            &bull; <?= e($issue['estimated_hours']) ?>h ước tính
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Assignee avatar -->
                    <?php if ($issue['assignee_name']): ?>
                    <div style="width:24px;height:24px;border-radius:50%;background:#1E3A5F;
                                color:#fff;font-size:10px;font-weight:700;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;"
                         title="<?= e($issue['assignee_name']) ?>">
                        <?php if ($issue['assignee_avatar']): ?>
                        <img src="<?= APP_URL ?>/uploads/<?= e($issue['assignee_avatar']) ?>"
                             style="width:24px;height:24px;border-radius:50%;object-fit:cover;" alt="">
                        <?php else: ?>
                        <?= e($assigneeInit) ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div><!-- /col-right -->

</div>

<!-- ════ Modal: Tạo Sprint ════ -->
<div class="modal fade" id="createSprintModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST"
                  action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/sprint/create">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-header" style="background:#1E3A5F;color:#fff;border-radius:8px 8px 0 0;">
                    <h6 class="modal-title fw-bold">
                        <i class="fa fa-rocket me-2"></i>Tạo Sprint Mới
                    </h6>
                    <button type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên sprint <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               placeholder="vd: Sprint 1 — Auth & Core Features" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mục tiêu sprint</label>
                        <textarea name="goal" class="form-control" rows="2"
                                  placeholder="Hoàn thành tính năng đăng nhập, dashboard..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Ngày bắt đầu</label>
                            <input type="date" name="start_date" class="form-control"
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Ngày kết thúc</label>
                            <input type="date" name="end_date" class="form-control"
                                   value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="fa fa-rocket me-1"></i>Tạo Sprint
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="sprintToast" class="toast align-items-center text-white border-0 bg-success"
         role="alert" style="min-width:260px;">
        <div class="d-flex">
            <div class="toast-body" id="sprintToastMsg">OK</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ════════════════════════════════════════════════
//  BURNDOWN CHART
// ════════════════════════════════════════════════
<?php if ($activeSprint && !empty($burndownData)): ?>
(function() {
    const data = <?= json_encode($burndownData, JSON_UNESCAPED_UNICODE) ?>;
    const labels    = data.map(d => d.date.substring(5));  // MM-DD
    const remaining = data.map(d => d.remaining);
    const ideal     = data.map(d => d.ideal);

    new Chart(document.getElementById('burndownChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Còn lại (thực tế)',
                    data: remaining,
                    borderColor: '#0078D4',
                    backgroundColor: 'rgba(0,120,212,.1)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    tension: .3,
                    fill: true,
                },
                {
                    label: 'Lý tưởng',
                    data: ideal,
                    borderColor: '#28A745',
                    borderDash: [6, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.dataset.label}: ${ctx.raw} issue`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    title: { display: true, text: 'Issue còn lại', font: { size: 11 } }
                },
                x: { ticks: { font: { size: 11 } } }
            }
        }
    });
})();
<?php endif; ?>

// ════════════════════════════════════════════════
//  BACKLOG DRAG TO SPRINT
// ════════════════════════════════════════════════
let backlogDragEl = null;

function backlogDragStart(e) {
    backlogDragEl = e.currentTarget;
    backlogDragEl.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', backlogDragEl.dataset.bugId);
}

function backlogDragEnd(e) {
    if (backlogDragEl) backlogDragEl.classList.remove('dragging');
    document.querySelectorAll('.sprint-drop-zone').forEach(z => z.classList.remove('drag-over'));
    backlogDragEl = null;
}

async function dropToSprint(e, sprintId) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');

    const bugId = parseInt(e.dataTransfer.getData('text/plain'));
    if (!bugId) return;

    const appUrl     = document.getElementById('appUrl').value;
    const projectKey = document.getElementById('projectKey').value;
    const csrf       = document.getElementById('csrfToken').value;

    try {
        const res = await fetch(`${appUrl}/projects/${projectKey}/sprint/assign-issue`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ csrf_token: csrf, bug_id: bugId, sprint_id: sprintId }),
        });
        const data = await res.json();

        if (data.ok) {
            // Xóa item khỏi backlog list
            if (backlogDragEl) backlogDragEl.remove();
            showSprintToast('✅ Đã thêm issue vào sprint!', 'success');

            // Cập nhật count badge backlog
            const badge = document.querySelector('[data-sprint-id] ~ * .badge.bg-secondary');
            // Reload nhẹ để đồng bộ UI (hoặc có thể update DOM trực tiếp)
            setTimeout(() => location.reload(), 800);
        } else {
            showSprintToast('❌ ' + (data.error || 'Thất bại'), 'danger');
        }
    } catch(err) {
        showSprintToast('❌ Kết nối thất bại', 'danger');
    }
}

function showSprintToast(msg, type = 'success') {
    const toast = document.getElementById('sprintToast');
    const msg_el= document.getElementById('sprintToastMsg');
    toast.className = `toast align-items-center text-white border-0 bg-${type}`;
    msg_el.textContent = msg;
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 3000 }).show();
}
</script>
