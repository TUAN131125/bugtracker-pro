<?php
// app/Views/pages/kanban/board.php
// Kanban Board — Dev A Ngày 4

// Priority badge colors
$priorityColors = [
    'critical' => '#DC3545',
    'high'     => '#FD7E14',
    'medium'   => '#FFC107',
    'low'      => '#28A745',
    'trivial'  => '#6C757D',
];

$priorityLabels = [
    'critical' => 'Nghiêm trọng',
    'high'     => 'Cao',
    'medium'   => 'Trung bình',
    'low'      => 'Thấp',
    'trivial'  => 'Không đáng kể',
];

$typeIcons = [
    'bug'         => 'fa-bug text-danger',
    'feature'     => 'fa-star text-warning',
    'task'        => 'fa-check-square text-primary',
    'improvement' => 'fa-arrow-up text-info',
    'question'    => 'fa-question-circle text-secondary',
    'epic'        => 'fa-bolt text-purple',
];
?>

<style>
/* ── Kanban Layout ── */
.kanban-wrapper {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 16px;
    align-items: flex-start;
    min-height: 70vh;
}

.kanban-col {
    flex: 0 0 280px;
    background: #F5F7FA;
    border-radius: 10px;
    border: 2px solid transparent;
    transition: border-color .2s;
}

.kanban-col.drag-over {
    border-color: #0078D4;
    background: #EBF4FF;
}

.kanban-col-header {
    padding: 10px 14px 8px;
    border-radius: 8px 8px 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 10;
    background: #F5F7FA;
}

.kanban-col-title {
    font-weight: 700;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.kanban-count-badge {
    background: #fff;
    border: 1.5px solid #dde2e8;
    border-radius: 12px;
    padding: 1px 8px;
    font-size: 12px;
    font-weight: 700;
    color: #444;
}

.kanban-wip-badge {
    font-size: 11px;
    color: #888;
    margin-left: 4px;
}

.kanban-wip-exceeded {
    color: #DC3545 !important;
    font-weight: 700;
}

.kanban-cards {
    padding: 8px;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* ── Cards ── */
.kanban-card {
    background: #fff;
    border-radius: 8px;
    border: 1.5px solid #E0E7EF;
    padding: 10px 12px;
    cursor: grab;
    transition: box-shadow .15s, transform .1s, opacity .15s;
    user-select: none;
    position: relative;
}

.kanban-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
    transform: translateY(-1px);
}

.kanban-card.dragging {
    opacity: .5;
    cursor: grabbing;
    box-shadow: 0 8px 24px rgba(0,0,0,.18);
}

.kanban-card.drag-placeholder {
    background: #EBF4FF;
    border: 2px dashed #0078D4;
    min-height: 60px;
}

.card-key {
    font-size: 11px;
    font-weight: 600;
    color: #0078D4;
    text-decoration: none;
    font-family: monospace;
}

.card-key:hover { text-decoration: underline; }

.card-title {
    font-size: 13px;
    font-weight: 500;
    color: #1E3A5F;
    margin: 4px 0 8px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    flex-wrap: wrap;
}

.priority-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.avatar-xs {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    object-fit: cover;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
}

.due-date-badge {
    font-size: 10px;
    padding: 1px 6px;
    border-radius: 8px;
}

/* ── Quick-view modal ── */
#quickViewModal .modal-body dt { font-size: 12px; color: #888; margin-bottom: 2px; }
#quickViewModal .modal-body dd { font-size: 14px; margin-bottom: 10px; }

/* ── Add card button ── */
.kanban-add-btn {
    width: 100%;
    background: transparent;
    border: none;
    padding: 6px 8px;
    font-size: 12px;
    color: #888;
    text-align: left;
    border-radius: 6px;
    cursor: pointer;
    transition: background .15s, color .15s;
    margin-top: 4px;
}

.kanban-add-btn:hover { background: #E3F2FD; color: #0078D4; }
</style>

<!-- ── Page Header ── -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:13px;">
                <li class="breadcrumb-item">
                    <a href="<?= APP_URL ?>/projects" class="text-decoration-none">Dự án</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>"
                       class="text-decoration-none">
                        <?= e($project['name']) ?>
                    </a>
                </li>
                <li class="breadcrumb-item active">Kanban</li>
            </ol>
        </nav>
        <h4 class="mb-0 fw-bold" style="color:#1E3A5F;">
            <i class="fa fa-columns me-2 text-primary"></i>Kanban Board
        </h4>
    </div>

    <div class="d-flex gap-2 align-items-center flex-wrap">

        <!-- Filter: Assignee -->
        <select class="form-select form-select-sm" id="filterAssignee"
                style="width:auto;min-width:130px;" onchange="applyFilters()">
            <option value="">Tất cả assignee</option>
            <?php foreach ($members as $m): ?>
            <option value="<?= $m['id'] ?>"
                <?= $filterAssignee == $m['id'] ? 'selected' : '' ?>>
                <?= e($m['full_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <!-- Filter: Priority -->
        <select class="form-select form-select-sm" id="filterPriority"
                style="width:auto;min-width:130px;" onchange="applyFilters()">
            <option value="">Tất cả priority</option>
            <option value="critical" <?= $filterPriority === 'critical' ? 'selected' : '' ?>>🔴 Nghiêm trọng</option>
            <option value="high"     <?= $filterPriority === 'high'     ? 'selected' : '' ?>>🟠 Cao</option>
            <option value="medium"   <?= $filterPriority === 'medium'   ? 'selected' : '' ?>>🟡 Trung bình</option>
            <option value="low"      <?= $filterPriority === 'low'      ? 'selected' : '' ?>>🟢 Thấp</option>
        </select>

        <!-- Link tới Sprint Board -->
        <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/sprint"
           class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-rocket me-1"></i>Sprint Board
        </a>

        <!-- Tạo issue mới -->
        <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/issues/new"
           class="btn btn-sm btn-primary">
            <i class="fa fa-plus me-1"></i>Tạo Issue
        </a>
    </div>
</div>

<!-- ── CSRF token ẩn dùng cho AJAX ── -->
<input type="hidden" id="csrfToken" value="<?= $this->generateCsrfToken() ?>">
<input type="hidden" id="projectKey" value="<?= e(strtolower($project['key'])) ?>">

<!-- ── Kanban Board ── -->
<div class="kanban-wrapper" id="kanbanBoard">

    <?php foreach ($columnConfig as $status => $config): ?>
    <?php
        $cards       = $columns[$status] ?? [];
        $count       = count($cards);
        $wipLimit    = $wipLimits[$status];
        $wipExceeded = $wipLimit > 0 && $count > $wipLimit;
    ?>

    <div class="kanban-col" data-status="<?= $status ?>" id="col-<?= $status ?>">

        <!-- Column Header -->
        <div class="kanban-col-header">
            <div class="kanban-col-title">
                <span style="width:10px;height:10px;border-radius:50%;
                             background:<?= $config['color'] ?>;display:inline-block;"></span>
                <?= e($config['label']) ?>
                <span class="kanban-count-badge"><?= $count ?></span>
            </div>
            <?php if ($wipLimit > 0): ?>
            <span class="kanban-wip-badge <?= $wipExceeded ? 'kanban-wip-exceeded' : '' ?>"
                  title="WIP Limit">
                <?= $wipExceeded ? '⚠️' : '' ?> WIP <?= $wipLimit ?>
            </span>
            <?php endif; ?>
        </div>

        <!-- Cards -->
        <div class="kanban-cards" id="cards-<?= $status ?>"
             ondragover="handleDragOver(event)"
             ondragleave="handleDragLeave(event)"
             ondrop="handleDrop(event, '<?= $status ?>')">

            <?php foreach ($cards as $card): ?>
            <?php
                $isOverdue = !empty($card['due_date'])
                    && strtotime($card['due_date']) < time()
                    && !in_array($card['status'], ['resolved','closed']);
                $assigneeInitial = $card['assignee_name']
                    ? mb_strtoupper(mb_substr($card['assignee_name'], 0, 1))
                    : null;
                $priorityColor = $priorityColors[$card['priority']] ?? '#6C757D';
                $typeIcon      = $typeIcons[$card['type']] ?? 'fa-circle';
            ?>
            <div class="kanban-card"
                 data-key="<?= e($card['issue_key']) ?>"
                 data-status="<?= e($card['status']) ?>"
                 draggable="true"
                 ondragstart="handleDragStart(event)"
                 ondragend="handleDragEnd(event)">

                <!-- Top row: key + type icon -->
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <a href="<?= APP_URL ?>/issues/<?= e(strtolower($card['issue_key'])) ?>"
                       class="card-key"
                       onclick="event.stopPropagation()">
                        <?= e($card['issue_key']) ?>
                    </a>
                    <div class="d-flex align-items-center gap-1">
                        <i class="fa <?= $typeIcon ?>" style="font-size:11px;"
                           title="<?= e($card['type']) ?>"></i>
                        <!-- Quick-view button -->
                        <button class="btn btn-link p-0 text-muted"
                                style="font-size:11px;line-height:1;"
                                title="Quick view"
                                onclick="event.stopPropagation();openQuickView('<?= e($card['issue_key']) ?>')">
                            <i class="fa fa-expand-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Title -->
                <div class="card-title" title="<?= e($card['title']) ?>">
                    <?= e($card['title']) ?>
                </div>

                <!-- Footer: priority dot + due date + assignee avatar -->
                <div class="card-footer">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Priority dot -->
                        <span class="priority-dot"
                              style="background:<?= $priorityColor ?>;"
                              title="<?= e($priorityLabels[$card['priority']] ?? $card['priority']) ?>">
                        </span>

                        <!-- Due date -->
                        <?php if ($card['due_date']): ?>
                        <span class="due-date-badge <?= $isOverdue ? 'bg-danger text-white' : 'bg-light text-muted border' ?>"
                              title="Hạn chót">
                            <i class="fa fa-calendar-day" style="font-size:9px;"></i>
                            <?= date('d/m', strtotime($card['due_date'])) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Assignee avatar -->
                    <?php if ($card['assignee_name']): ?>
                    <div class="avatar-xs"
                         style="background:#1E3A5F;"
                         title="<?= e($card['assignee_name']) ?>">
                        <?php if ($card['assignee_avatar']): ?>
                        <img src="<?= APP_URL ?>/uploads/<?= e($card['assignee_avatar']) ?>"
                             class="avatar-xs" alt="">
                        <?php else: ?>
                        <?= e($assigneeInitial) ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Empty state -->
            <?php if (empty($cards)): ?>
            <div class="text-center text-muted py-3" style="font-size:12px;">
                <i class="fa fa-inbox d-block mb-1 opacity-50" style="font-size:20px;"></i>
                Không có issue
            </div>
            <?php endif; ?>

        </div><!-- /kanban-cards -->

        <!-- Add card shortcut (chỉ cột open) -->
        <?php if ($status === 'open'): ?>
        <div style="padding:0 8px 10px;">
            <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/issues/new"
               class="kanban-add-btn">
                <i class="fa fa-plus me-1"></i>Thêm issue
            </a>
        </div>
        <?php endif; ?>

    </div><!-- /kanban-col -->
    <?php endforeach; ?>

</div><!-- /kanban-wrapper -->

<!-- ════════════════════════════════════
     QUICK-VIEW MODAL
════════════════════════════════════ -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header py-2 px-4"
                 style="background:#1E3A5F;color:#fff;border-radius:8px 8px 0 0;">
                <span class="fw-bold" id="qv-key" style="font-family:monospace;font-size:13px;"></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3" id="qv-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" style="width:28px;height:28px;"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <a id="qv-link" href="#" class="btn btn-sm btn-primary">
                    <i class="fa fa-external-link-alt me-1"></i>Xem đầy đủ
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════
     TOAST thông báo drag-drop
════════════════════════════════════ -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="kanbanToast" class="toast align-items-center text-white border-0"
         role="alert" style="min-width:260px;">
        <div class="d-flex">
            <div class="toast-body" id="kanbanToastMsg">Đã cập nhật!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
// ════════════════════════════════════════════════════════
//  KANBAN DRAG-AND-DROP  (Vanilla JS)
// ════════════════════════════════════════════════════════

const CSRF       = document.getElementById('csrfToken').value;
const projectKey = document.getElementById('projectKey').value;
let   dragCard   = null;
let   placeholder= null;

// ── Drag Start ──
function handleDragStart(e) {
    dragCard = e.currentTarget;
    dragCard.classList.add('dragging');

    // Tạo placeholder
    placeholder = document.createElement('div');
    placeholder.className = 'kanban-card drag-placeholder';
    placeholder.style.height = dragCard.offsetHeight + 'px';

    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', dragCard.dataset.key);

    // Đợi 1 frame để placeholder không bị render ở nguồn
    requestAnimationFrame(() => {
        dragCard.style.display = 'none';
    });
}

// ── Drag End ──
function handleDragEnd(e) {
    if (dragCard) dragCard.classList.remove('dragging');
    if (dragCard) dragCard.style.display = '';
    if (placeholder && placeholder.parentNode) {
        placeholder.parentNode.removeChild(placeholder);
    }
    document.querySelectorAll('.kanban-col').forEach(c => c.classList.remove('drag-over'));
    dragCard    = null;
    placeholder = null;
}

// ── Drag Over ──
function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';

    const col    = e.currentTarget.closest('.kanban-col');
    const cards  = e.currentTarget;

    col.classList.add('drag-over');

    // Tìm vị trí chèn placeholder
    const afterEl = getDragAfterElement(cards, e.clientY);
    if (afterEl) {
        cards.insertBefore(placeholder, afterEl);
    } else {
        cards.appendChild(placeholder);
    }
}

// ── Drag Leave ──
function handleDragLeave(e) {
    const col = e.currentTarget.closest('.kanban-col');
    if (!col.contains(e.relatedTarget)) {
        col.classList.remove('drag-over');
    }
}

// ── Drop ──
function handleDrop(e, newStatus) {
    e.preventDefault();
    if (!dragCard) return;

    const issueKey = dragCard.dataset.key;
    const oldStatus= dragCard.dataset.status;

    if (newStatus === oldStatus) return; // không thay đổi

    // Đặt card vào vị trí placeholder
    const cards = document.getElementById('cards-' + newStatus);
    if (placeholder && placeholder.parentNode === cards) {
        cards.insertBefore(dragCard, placeholder);
        if (placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
    } else {
        cards.appendChild(dragCard);
    }

    dragCard.dataset.status = newStatus;
    dragCard.style.display  = '';

    // Ẩn empty state nếu có
    updateEmptyState(cards);
    updateEmptyState(document.getElementById('cards-' + oldStatus));
    updateColumnCount(newStatus, 1);
    updateColumnCount(oldStatus, -1);

    // Gọi API
    moveCardAPI(issueKey, newStatus);
}

// ── Tính vị trí chèn ──
function getDragAfterElement(container, y) {
    const draggables = [...container.querySelectorAll('.kanban-card:not(.dragging):not(.drag-placeholder)')];
    return draggables.reduce((closest, child) => {
        const box  = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
        }
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// ── Gọi API cập nhật status ──
async function moveCardAPI(issueKey, newStatus) {
    try {
        const res = await fetch(`${<?= json_encode(APP_URL) ?>}/projects/${projectKey}/board/move`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                csrf_token: CSRF,
                issue_key:  issueKey,
                new_status: newStatus,
            }),
        });
        const data = await res.json();
        if (data.ok) {
            showToast('✅ ' + issueKey + ' → ' + newStatus.replace('_',' '), 'success');
        } else {
            showToast('❌ Lỗi: ' + (data.error || 'Không xác định'), 'danger');
        }
    } catch (err) {
        showToast('❌ Kết nối thất bại', 'danger');
    }
}

// ── Cập nhật badge đếm số card ──
function updateColumnCount(status, delta) {
    const col   = document.getElementById('col-' + status);
    if (!col) return;
    const badge = col.querySelector('.kanban-count-badge');
    if (!badge) return;
    const cur = parseInt(badge.textContent) || 0;
    badge.textContent = Math.max(0, cur + delta);
}

// ── Hiện/ẩn empty state ──
function updateEmptyState(container) {
    if (!container) return;
    const realCards = container.querySelectorAll('.kanban-card:not(.drag-placeholder)');
    let emptyEl = container.querySelector('.kanban-empty');

    if (realCards.length === 0 && !emptyEl) {
        const empty = document.createElement('div');
        empty.className = 'text-center text-muted py-3 kanban-empty';
        empty.style.fontSize = '12px';
        empty.innerHTML = '<i class="fa fa-inbox d-block mb-1 opacity-50" style="font-size:20px;"></i>Không có issue';
        container.appendChild(empty);
    } else if (realCards.length > 0 && emptyEl) {
        emptyEl.remove();
    }
}

// ── Quick-view popup ──
const qvModal = new bootstrap.Modal(document.getElementById('quickViewModal'));
async function openQuickView(issueKey) {
    document.getElementById('qv-key').textContent = issueKey;
    document.getElementById('qv-body').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-primary" style="width:28px;height:28px;"></div></div>';
    document.getElementById('qv-link').href = '#';
    qvModal.show();

    try {
        const res  = await fetch(`${<?= json_encode(APP_URL) ?>}/issues/${issueKey.toLowerCase()}/quickview`);
        const data = await res.json();
        if (!data.ok) throw new Error(data.error);

        const b = data.bug;
        const prColors = {
            critical:'#DC3545', high:'#FD7E14', medium:'#FFC107', low:'#28A745', trivial:'#6C757D'
        };
        document.getElementById('qv-link').href = b.url;
        document.getElementById('qv-body').innerHTML = `
            <h6 class="fw-bold mb-3" style="color:#1E3A5F;">${escHtml(b.title)}</h6>
            <dl class="row mb-0">
                <dt class="col-4">Loại</dt>
                <dd class="col-8 text-capitalize">${escHtml(b.type)}</dd>
                <dt class="col-4">Status</dt>
                <dd class="col-8"><span class="badge text-bg-secondary">${escHtml(b.status.replace('_',' '))}</span></dd>
                <dt class="col-4">Priority</dt>
                <dd class="col-8"><span class="badge" style="background:${prColors[b.priority]||'#888'}">${escHtml(b.priority)}</span></dd>
                <dt class="col-4">Assignee</dt>
                <dd class="col-8">${escHtml(b.assignee_name || '— Chưa giao')}</dd>
                <dt class="col-4">Reporter</dt>
                <dd class="col-8">${escHtml(b.reporter_name)}</dd>
                <dt class="col-4">Hạn chót</dt>
                <dd class="col-8">${b.due_date ? escHtml(b.due_date) : '—'}</dd>
                ${b.description ? `<dt class="col-4">Mô tả</dt>
                <dd class="col-8 text-muted" style="font-size:13px;">${escHtml(b.description)}${b.description.length>=300?'…':''}</dd>` : ''}
            </dl>`;
    } catch (err) {
        document.getElementById('qv-body').innerHTML =
            `<div class="alert alert-danger mb-0">Lỗi: ${escHtml(err.message)}</div>`;
    }
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Toast ──
function showToast(msg, type = 'success') {
    const toast    = document.getElementById('kanbanToast');
    const toastMsg = document.getElementById('kanbanToastMsg');
    toast.className = `toast align-items-center text-white border-0 bg-${type === 'success' ? 'success' : 'danger'}`;
    toastMsg.textContent = msg;
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 3000 }).show();
}

// ── Apply Filters ──
function applyFilters() {
    const assignee = document.getElementById('filterAssignee').value;
    const priority = document.getElementById('filterPriority').value;
    const params   = new URLSearchParams();
    if (assignee) params.set('assignee', assignee);
    if (priority) params.set('priority', priority);
    window.location.search = params.toString();
}
</script>
