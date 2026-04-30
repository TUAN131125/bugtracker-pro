<?php

$bug          = $bug          ?? [];
$comments     = $comments     ?? [];
$activities   = $activities   ?? [];
$attachments  = $attachments  ?? [];
$linkedIssues = $linkedIssues ?? [];
$csrf_token   = $csrf_token   ?? '';

$canEdit   = in_array($_SESSION['user_role'] ?? '', ['admin','manager','developer']);
$canDelete = in_array($_SESSION['user_role'] ?? '', ['admin','manager']);

?>

<link rel="stylesheet" href="<?= APP_URL ?>/public/css/issue.css">

<!-- ── Breadcrumb ── -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb" style="font-size:13px;">
        <li class="breadcrumb-item">
            <a href="<?= APP_URL ?>/projects" class="text-decoration-none">
                <i class="fa fa-folder me-1"></i>Dự án
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= APP_URL ?>/projects/<?= e(strtolower($bug['project_key'])) ?>" class="text-decoration-none">
                <?= e($bug['project_name']) ?>
            </a>
        </li>
        <li class="breadcrumb-item active"><?= e($bug['issue_key']) ?></li>
    </ol>
</nav>

<!-- ── Issue Header ── -->
<div class="issue-header mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <div class="issue-key"><?= e($bug['issue_key']) ?></div>
            <h1 class="issue-title"><?= e($bug['title']) ?></h1>
            <div class="issue-meta mt-2">
                <?= typeBadge($bug['type']) ?>
                <?= statusBadge($bug['status']) ?>
                <?= priorityBadge($bug['priority']) ?>
                <span class="text-muted">
                    <i class="fa fa-clock me-1"></i>
                    Tạo <?= timeAgo($bug['created_at']) ?>
                </span>
                <span class="text-muted">
                    bởi <strong><?= e($bug['reporter_name']) ?></strong>
                </span>
                <?php if (isOverdue($bug['due_date'], $bug['status'])): ?>
                <span class="badge bg-danger">
                    <i class="fa fa-exclamation-triangle me-1"></i>Quá hạn
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="issue-actions">
            <button class="btn-action" onclick="copyIssueLink()">
                <i class="fa fa-link"></i> Copy link
            </button>
            <?php if ($canEdit): ?>
            <a href="<?= APP_URL ?>/issues/<?= e($bug['issue_key']) ?>/edit"
               class="btn-action">
                <i class="fa fa-pen"></i> Chỉnh sửa
            </a>
            <?php endif; ?>
            <?php if ($canDelete): ?>
            <button class="btn-action danger"
                    data-confirm="Bạn chắc chắn muốn xóa issue này?"
                    onclick="deleteIssue()">
                <i class="fa fa-trash"></i> Xóa
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── 2 Panel Layout ── -->
<div class="issue-layout">

    <!-- ════ PANEL TRÁI (70%) ════ -->
    <div class="issue-main">

        <!-- Mô tả -->
        <?php if (!empty($bug['description'])): ?>
        <div class="issue-card">
            <h6><i class="fa fa-align-left me-2"></i>Mô Tả</h6>
            <div class="issue-description">
                <?= parseMarkdown($bug['description']) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Steps to reproduce -->
        <?php if (!empty($bug['steps_to_reproduce'])): ?>
        <div class="issue-card">
            <h6><i class="fa fa-list-ol me-2"></i>Các Bước Tái Tạo</h6>
            <?php
            $steps = array_filter(explode("\n", $bug['steps_to_reproduce']));
            ?>
            <?php if ($steps): ?>
            <ol class="steps-list">
                <?php foreach ($steps as $step): ?>
                <li><?= e(trim($step)) ?></li>
                <?php endforeach; ?>
            </ol>
            <?php else: ?>
            <p class="text-muted mb-0" style="font-size:14px;">
                <?= e($bug['steps_to_reproduce']) ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Môi trường / Browser -->
        <?php if (!empty($bug['environment']) || !empty($bug['browser_info'])): ?>
        <div class="issue-card">
            <h6><i class="fa fa-desktop me-2"></i>Môi Trường</h6>
            <div class="row g-3" style="font-size:13px;">
                <?php if (!empty($bug['environment'])): ?>
                <div class="col-md-6">
                    <div class="text-muted mb-1">Môi trường:</div>
                    <code><?= e($bug['environment']) ?></code>
                </div>
                <?php endif; ?>
                <?php if (!empty($bug['browser_info'])): ?>
                <div class="col-md-6">
                    <div class="text-muted mb-1">Trình duyệt:</div>
                    <code><?= e($bug['browser_info']) ?></code>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resolution -->
        <?php if (!empty($bug['resolution'])): ?>
        <div class="issue-card" style="border-left:3px solid #28A745;">
            <h6><i class="fa fa-check-circle me-2 text-success"></i>Cách Giải Quyết</h6>
            <div class="issue-description">
                <?= parseMarkdown($bug['resolution']) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── TABS ── -->
        <div class="issue-card">
            <div class="issue-tabs">
                <div class="issue-tab active" data-tab="comments">
                    <i class="fa fa-comment"></i>
                    Comments
                    <?php if (!empty($comments)): ?>
                    <span class="issue-tab-badge"><?= count($comments) ?></span>
                    <?php endif; ?>
                </div>
                <div class="issue-tab" data-tab="activity">
                    <i class="fa fa-history"></i>
                    Activity
                    <?php if (!empty($activities)): ?>
                    <span class="issue-tab-badge"><?= count($activities) ?></span>
                    <?php endif; ?>
                </div>
                <div class="issue-tab" data-tab="attachments">
                    <i class="fa fa-paperclip"></i>
                    Files
                    <?php if (!empty($attachments)): ?>
                    <span class="issue-tab-badge"><?= count($attachments) ?></span>
                    <?php endif; ?>
                </div>
                <div class="issue-tab" data-tab="linked">
                    <i class="fa fa-link"></i>
                    Linked
                    <?php if (!empty($linkedIssues)): ?>
                    <span class="issue-tab-badge"><?= count($linkedIssues) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab: Comments -->
            <div class="tab-pane active" id="tab-comments">
                <?php if (!empty($comments)): ?>
                <div class="mb-4">
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-item" id="comment-<?= $comment['id'] ?>">
                        <!-- Avatar -->
                        <?php if (!empty($comment['user_avatar'])): ?>
                        <img src="<?= APP_URL ?>/uploads/<?= e($comment['user_avatar']) ?>"
                             class="comment-avatar" alt="">
                        <?php else: ?>
                        <div class="comment-avatar">
                            <?= e(mb_strtoupper(mb_substr($comment['user_name'], 0, 1))) ?>
                        </div>
                        <?php endif; ?>

                        <div class="comment-body">
                            <div class="comment-header">
                                <span class="comment-author"><?= e($comment['user_name']) ?></span>
                                <span class="comment-time">
                                    <i class="fa fa-clock me-1"></i>
                                    <?= timeAgo($comment['created_at']) ?>
                                </span>
                            </div>
                            <div class="comment-content">
                                <?= parseMarkdown($comment['content']) ?>
                            </div>
                            <?php if ((isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']) || $canEdit): ?>
                            <div class="comment-actions">
                                <button class="comment-action-btn"
                                        onclick="editComment(<?= $comment['id'] ?>)">
                                    <i class="fa fa-pen me-1"></i>Sửa
                                </button>
                                <button class="comment-action-btn"
                                        onclick="deleteComment(<?= $comment['id'] ?>)">
                                    <i class="fa fa-trash me-1"></i>Xóa
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-3" style="font-size:13px;">
                    <i class="fa fa-comment-slash fa-2x d-block mb-2 text-muted"></i>
                    Chưa có comment nào. Hãy là người đầu tiên!
                </p>
                <?php endif; ?>

                <!-- Form thêm comment -->
                <form method="POST"
                      action="<?= APP_URL ?>/issues/<?= e($bug['issue_key']) ?>/comment"
                      id="commentForm"
                      onsubmit="return submitComment(event)">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>">
                    <div class="comment-editor">
                        <div class="comment-editor-toolbar">
                            <button type="button" class="editor-btn" onclick="wrapText('**','**')" title="Bold">
                                <i class="fa fa-bold"></i>
                            </button>
                            <button type="button" class="editor-btn" onclick="wrapText('*','*')" title="Italic">
                                <i class="fa fa-italic"></i>
                            </button>
                            <button type="button" class="editor-btn" onclick="wrapText('`','`')" title="Code">
                                <i class="fa fa-code"></i>
                            </button>
                            <button type="button" class="editor-btn" onclick="wrapText('\n- ','')" title="List">
                                <i class="fa fa-list"></i>
                            </button>
                        </div>
                        <textarea id="commentContent"
                                  name="content"
                                  class="comment-textarea"
                                  placeholder="Thêm comment... Hỗ trợ **bold**, *italic*, `code`. Dùng @username để mention."
                                  required></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">
                            Hỗ trợ Markdown cơ bản
                        </small>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                            <i class="fa fa-paper-plane me-1"></i>Gửi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab: Activity -->
            <div class="tab-pane" id="tab-activity">
                <?php if (!empty($activities)): ?>
                <?php foreach ($activities as $act): ?>
                <?php
                $dotColor = match(true) {
                    str_contains($act['action'], 'resolv')  => 'green',
                    str_contains($act['action'], 'creat')   => 'blue',
                    str_contains($act['action'], 'assign')  => 'orange',
                    str_contains($act['action'], 'delet')   => 'red',
                    default => ''
                };
                $actionLabel = [
                    'bug_created'       => 'đã tạo issue này',
                    'status_changed'    => 'đã đổi status',
                    'priority_changed'  => 'đã đổi priority',
                    'assignee_changed'  => 'đã thay đổi người phụ trách',
                    'comment_added'     => 'đã thêm comment',
                    'bug_resolved'      => 'đã đánh dấu resolved',
                    'attachment_added'  => 'đã đính kèm file',
                ][$act['action']] ?? $act['action'];
                ?>
                <div class="activity-item">
                    <div class="activity-dot <?= $dotColor ?>"></div>
                    <div>
                        <strong><?= e($act['user_name']) ?></strong>
                        <?= e($actionLabel) ?>
                        <?php if (!empty($act['new_value'])): ?>
                        <?php $newVal = json_decode($act['new_value'], true); ?>
                        <?php if (isset($newVal['status'])): ?>
                        → <?= statusBadge($newVal['status']) ?>
                        <?php elseif (isset($newVal['priority'])): ?>
                        → <?= priorityBadge($newVal['priority']) ?>
                        <?php endif; ?>
                        <?php endif; ?>
                        <div class="activity-time">
                            <i class="fa fa-clock me-1"></i><?= timeAgo($act['created_at']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p class="text-muted text-center py-3" style="font-size:13px;">
                    <i class="fa fa-history fa-2x d-block mb-2"></i>
                    Chưa có hoạt động nào
                </p>
                <?php endif; ?>
            </div>

            <!-- Tab: Attachments -->
            <div class="tab-pane" id="tab-attachments">
                <?php if (!empty($attachments)): ?>
                <div class="d-flex flex-column gap-2 mb-3">
                    <?php foreach ($attachments as $att): ?>
                    <?php
                    $iconMap = [
                        'image' => ['fa-image',       '#0078D4', '#E3F2FD'],
                        'pdf'   => ['fa-file-pdf',    '#DC3545', '#FFEBEE'],
                        'text'  => ['fa-file-alt',    '#28A745', '#E8F5E9'],
                    ];
                    $mimeGroup = explode('/', $att['mime_type'])[0];
                    [$icon, $iconColor, $iconBg] = $iconMap[$mimeGroup]
                        ?? ['fa-file', '#6B7A8F', '#F5F7FA'];
                    $sizeKB = round($att['file_size'] / 1024);
                    ?>
                    <div class="attachment-item">
                        <div class="attachment-icon" style="background:<?= $iconBg ?>;">
                            <i class="fa <?= $icon ?>" style="color:<?= $iconColor ?>;"></i>
                        </div>
                        <div class="attachment-info">
                            <div class="attachment-name"><?= e($att['original_name']) ?></div>
                            <div class="attachment-size">
                                <?= $sizeKB ?>KB · <?= timeAgo($att['created_at']) ?>
                            </div>
                        </div>
                        <a href="<?= APP_URL ?>/uploads/<?= e($att['filename']) ?>"
                           class="btn btn-sm btn-outline-primary"
                           download="<?= e($att['original_name']) ?>">
                            <i class="fa fa-download"></i>
                        </a>
                        <?php if ($canEdit): ?>
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="deleteAttachment(<?= $att['id'] ?>)"
                                title="Xóa file">
                            <i class="fa fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Drop zone upload -->
                <?php if ($canEdit): ?>
                <form method="POST"
                      action="<?= APP_URL ?>/issues/<?= e($bug['issue_key']) ?>/attach"
                      enctype="multipart/form-data"
                      id="attachForm"
                      onsubmit="return submitAttachment(event)">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>">
                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                        <i class="fa fa-cloud-upload-alt d-block mb-2"></i>
                        <p>Kéo thả file vào đây hoặc <strong>click để chọn</strong></p>
                        <small class="text-muted">JPG, PNG, PDF, TXT · Tối đa 10MB/file · Tối đa 5 files</small>
                    </div>
                    <input type="file"
                           name="attachments[]"
                           id="fileInput"
                           multiple
                           accept="image/*,.pdf,.txt,.log"
                           style="display:none;"
                           onchange="handleFileSelect(this)">
                    <div id="fileList" class="mt-2"></div>
                    <button type="submit" class="btn btn-primary btn-sm mt-2 d-none" id="uploadBtn">
                        <i class="fa fa-upload me-1"></i>Upload
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Tab: Linked Issues -->
            <div class="tab-pane" id="tab-linked">
                <?php if (!empty($linkedIssues)): ?>
                <?php foreach ($linkedIssues as $linked): ?>
                <div class="d-flex align-items-center gap-2 p-2 border-bottom">
                    <span class="badge bg-secondary" style="font-size:11px;">
                        <?= e($linked['relation_type']) ?>
                    </span>
                    <a href="<?= APP_URL ?>/issues/<?= e($linked['issue_key']) ?>"
                       class="text-decoration-none text-primary fw-bold"
                       style="font-size:13px;">
                        <?= e($linked['issue_key']) ?>
                    </a>
                    <span style="font-size:13px;"><?= e(truncate($linked['title'], 60)) ?></span>
                    <?= statusBadge($linked['status']) ?>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p class="text-muted text-center py-3" style="font-size:13px;">
                    <i class="fa fa-link fa-2x d-block mb-2"></i>
                    Chưa có issue liên kết
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ════ PANEL PHẢI — SIDEBAR ════ -->
    <div class="issue-sidebar">

        <!-- Status selector -->
        <?php if ($canEdit): ?>
        <div class="meta-card">
            <div style="font-size:12px;font-weight:700;color:#6B7A8F;text-transform:uppercase;letter-spacing:1px;margin-bottom:.8rem;">
                Đổi Status
            </div>
            <div class="status-selector">
                <?php
                $statuses = [
                    'open'        => ['#0078D4', 'Mở'],
                    'in_progress' => ['#FD7E14', 'Đang xử lý'],
                    'review'      => ['#6A1B9A', 'Review'],
                    'resolved'    => ['#28A745', 'Resolved'],
                    'closed'      => ['#6C757D', 'Đóng'],
                ];
                foreach ($statuses as $val => [$color, $label]):
                ?>
                <div class="status-option <?= $bug['status'] === $val ? 'active' : '' ?>"
                     data-status="<?= $val ?>"
                     style="color:<?= $color ?>; background:<?= $color ?>18;"
                     onclick="changeStatus('<?= $val ?>')">
                    <?= $label ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Metadata -->
        <div class="meta-card">
            <div style="font-size:12px;font-weight:700;color:#6B7A8F;text-transform:uppercase;letter-spacing:1px;margin-bottom:.8rem;">
                Chi Tiết
            </div>

            <!-- Assignee -->
            <div class="meta-row">
                <span class="meta-label"><i class="fa fa-user me-1"></i>Phụ trách</span>
                <span class="meta-value">
                    <?php if (!empty($bug['assignee_name'])): ?>
                    <?php if (!empty($bug['assignee_avatar'])): ?>
                    <img src="<?= APP_URL ?>/uploads/<?= e($bug['assignee_avatar']) ?>"
                         style="width:20px;height:20px;border-radius:50%;margin-right:4px;" alt="">
                    <?php endif; ?>
                    <?= e($bug['assignee_name']) ?>
                    <?php else: ?>
                    <span class="text-muted">Chưa giao</span>
                    <?php endif; ?>
                </span>
            </div>

            <!-- Priority -->
            <div class="meta-row">
                <span class="meta-label"><i class="fa fa-flag me-1"></i>Priority</span>
                <span class="meta-value"><?= priorityBadge($bug['priority']) ?></span>
            </div>

            <!-- Type -->
            <div class="meta-row">
                <span class="meta-label"><i class="fa fa-tag me-1"></i>Loại</span>
                <span class="meta-value"><?= typeBadge($bug['type']) ?></span>
            </div>

            <!-- Due date -->
            <div class="meta-row">
                <span class="meta-label"><i class="fa fa-calendar me-1"></i>Hạn chót</span>
                <span class="meta-value <?= isOverdue($bug['due_date'], $bug['status']) ? 'text-danger' : '' ?>">
                    <?= formatDate($bug['due_date']) ?>
                </span>
            </div>

            <!-- Estimated hours -->
            <?php if (!empty($bug['estimated_hours'])): ?>
            <div class="meta-row">
                <span class="meta-label"><i class="fa fa-clock me-1"></i>Ước tính</span>
                <span class="meta-value"><?= e($bug['estimated_hours']) ?>h</span>
            </div>
            <?php endif; ?>

            <!-- Actual hours -->
            <?php if (!empty($bug['actual_hours'])): ?>
            <div class="meta-row">
                <span class="meta-label"><i class="fa fa-stopwatch me-1"></i>Thực tế</span>
                <span class="meta-value"><?= e($bug['actual_hours']) ?>h</span>
            </div>
            <?php endif; ?>

            <!-- Reporter -->
            <div class="meta-row">
                <span class="meta-label"><i class="fa fa-user-edit me-1"></i>Báo cáo bởi</span>
                <span class="meta-value"><?= e($bug['reporter_name']) ?></span>
            </div>

            <!-- Created at -->
            <div class="meta-row">
                <span class="meta-label"><i class="fa fa-plus-circle me-1"></i>Tạo lúc</span>
                <span class="meta-value"><?= formatDate($bug['created_at'], 'd/m/Y H:i') ?></span>
            </div>

            <!-- Updated at -->
            <div class="meta-row">
                <span class="meta-label"><i class="fa fa-pen me-1"></i>Cập nhật</span>
                <span class="meta-value"><?= timeAgo($bug['updated_at']) ?></span>
            </div>
        </div>

        <!-- Vote + Watch -->
        <div class="meta-card">
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="voteIssue()">
                    <i class="fa fa-thumbs-up me-1"></i>
                    Vote (<span id="voteCount"><?= e((string)($bug['votes'] ?? 0)) ?></span>)
                </button>
                <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="watchIssue()">
                    <i class="fa fa-eye me-1"></i>Watch
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ════ JAVASCRIPT ════ -->
<script>
const ISSUE_KEY  = '<?= e($bug['issue_key']) ?>';
const APP_URL    = '<?= APP_URL ?>';
const CSRF_TOKEN = '<?= e($csrf_token ?? '') ?>';

if (!CSRF_TOKEN || CSRF_TOKEN.trim() === '') {
    console.warn('CSRF_TOKEN not provided from server');
}

// ── TABS ──
document.querySelectorAll('.issue-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Deactivate tất cả
        document.querySelectorAll('.issue-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

        // Activate tab được click
        this.classList.add('active');
        const target = this.dataset.tab;
        document.getElementById('tab-' + target)?.classList.add('active');
    });
});

// ── MARKDOWN TOOLBAR ──
function wrapText(before, after) {
    const ta = document.getElementById('commentContent');
    if (!ta) return;

    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    const selected = ta.value.substring(start, end) || 'text';

    ta.value = ta.value.substring(0, start)
             + before + selected + after
             + ta.value.substring(end);

    ta.focus();
    ta.selectionStart = start + before.length;
    ta.selectionEnd   = start + before.length + selected.length;
}

// ── CHANGE STATUS ──
function changeStatus(newStatus) {
    if (!confirm('Đổi status thành: ' + newStatus + '?')) return;

    fetch(APP_URL + '/issues/' + ISSUE_KEY + '/status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'status=' + newStatus + '&csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(r => {
        if (!r.ok) throw new Error('Server error: ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            // Update UI: so sánh với data-status attribute
            document.querySelectorAll('.status-option').forEach(el => {
                const isActive = el.getAttribute('data-status') === newStatus;
                el.classList.toggle('active', isActive);
            });
            showToast('Đã đổi status thành công!', 'success');
        } else {
            showToast(data.error || 'Không thể đổi status', 'danger');
        }
    })
    .catch(err => {
        console.error('Error changing status:', err);
        showToast('Có lỗi xảy ra: ' + err.message, 'danger');
    });
}

// ── COPY LINK ──
function copyIssueLink() {
    navigator.clipboard.writeText(window.location.href)
        .then(() => showToast('Đã copy link!', 'success'))
        .catch(err => {
            console.error('Clipboard error:', err);
            showToast('Không thể copy link', 'danger');
        });
}

// ── VOTE ──
function voteIssue() {
    fetch(APP_URL + '/issues/' + ISSUE_KEY + '/vote', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(r => {
        if (!r.ok) throw new Error('Server error: ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.votes !== undefined) {
            document.getElementById('voteCount').textContent = data.votes;
            showToast('Cảm ơn bạn đã vote!', 'success');
        }
    })
    .catch(err => {
        console.error('Error voting:', err);
        showToast('Lỗi khi vote: ' + err.message, 'danger');
    });
}

// ── DELETE COMMENT ──
function deleteComment(commentId) {
    if (!confirm('Xóa comment này?')) return;

    fetch(APP_URL + '/comments/' + commentId + '/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(r => {
        if (!r.ok) throw new Error('Server error: ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('comment-' + commentId)?.remove();
            showToast('Đã xóa comment!', 'success');
        } else {
            showToast(data.error || 'Không thể xóa comment', 'danger');
        }
    })
    .catch(err => {
        console.error('Error deleting comment:', err);
        showToast('Lỗi khi xóa comment: ' + err.message, 'danger');
    });
}

// ── DELETE ATTACHMENT ──
function deleteAttachment(attachId) {
    if (!confirm('Xóa file đính kèm này?')) return;

    fetch(APP_URL + '/attachments/' + attachId + '/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(r => {
        if (!r.ok) throw new Error('Server error: ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            showToast('Đã xóa file!', 'success');
            location.reload();
        } else {
            showToast(data.error || 'Không thể xóa file', 'danger');
        }
    })
    .catch(err => {
        console.error('Error deleting attachment:', err);
        showToast('Lỗi khi xóa file: ' + err.message, 'danger');
    });
}

// ── FILE UPLOAD ──
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

if (dropZone) {
    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        handleFiles(e.dataTransfer.files);
    });
}

function handleFileSelect(input) {
    handleFiles(input.files);
}

function handleFiles(files) {
    const list = document.getElementById('fileList');
    const btn  = document.getElementById('uploadBtn');
    if (!list) return;

    const MAX_SIZE = 10 * 1024 * 1024; // 10MB
    const ALLOWED_TYPES = ['image', 'application/pdf', 'text'];
    const validFiles = [];
    
    list.innerHTML = '';
    
    Array.from(files).slice(0, 5).forEach(file => {
        // Validate file size
        if (file.size > MAX_SIZE) {
            showToast(`File "${file.name}" vượt quá 10MB`, 'warning');
            return;
        }
        
        // Validate file type
        const fileType = file.type.split('/')[0];
        if (!ALLOWED_TYPES.some(type => file.type.includes(type))) {
            showToast(`File "${file.name}" không được hỗ trợ`, 'warning');
            return;
        }
        
        validFiles.push(file);
        const sizeKB = Math.round(file.size / 1024);
        const item   = document.createElement('div');
        item.className = 'attachment-item mt-1';
        item.innerHTML = `
            <div class="attachment-icon" style="background:#E3F2FD;">
                <i class="fa fa-file" style="color:#0078D4;"></i>
            </div>
            <div class="attachment-info">
                <div class="attachment-name">${file.name}</div>
                <div class="attachment-size">${sizeKB}KB</div>
            </div>`;
        list.appendChild(item);
    });

    if (validFiles.length > 0) {
        btn?.classList.remove('d-none');
    }
}

let toastCount = 0;
function showToast(message, type = 'success') {
    const typeMap = {'success': 'alert-success', 'danger': 'alert-danger', 'warning': 'alert-warning', 'info': 'alert-info'};
    const alertClass = typeMap[type] || 'alert-info';
    const toast = document.createElement('div');
    toast.className = `alert ${alertClass} position-fixed shadow`;
    const offsetY = 20 + (toastCount * 80);
    toast.style.cssText = `bottom:${offsetY}px;right:20px;z-index:${9999 + toastCount};min-width:280px;`;
    toast.innerHTML = `<i class="fa fa-check-circle me-2"></i>${message}`;
    document.body.appendChild(toast);
    toastCount++;
    setTimeout(() => {toast.remove(); toastCount--;}, 3000);
}

function watchIssue() {
    showToast('Đang theo dõi issue này!', 'info');
}

// ── DELETE ISSUE ──
function deleteIssue(bugId) {
    if (!confirm('Bạn chắc chắn muốn xóa issue này? Hành động này không thể hoàn tác!')) return;

    fetch(APP_URL + '/issues/' + ISSUE_KEY + '/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=' + encodeURIComponent(CSRF_TOKEN)
    })
    .then(r => {
        if (!r.ok) throw new Error('Server error: ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            showToast('Đã xóa issue!', 'success');
            setTimeout(() => window.location.href = APP_URL + '/projects', 1000);
        } else {
            showToast(data.error || 'Không thể xóa issue', 'danger');
        }
    })
    .catch(err => {
        console.error('Error deleting issue:', err);
        showToast('Lỗi khi xóa issue: ' + err.message, 'danger');
    });
}

function editComment(commentId) {
    showToast('Chức năng chỉnh sửa comment sẽ được phát triển!', 'info');
}

function submitComment(e) {
    const commentContent = document.getElementById('commentContent');
    if (!commentContent.value.trim()) {
        showToast('Vui lòng nhập comment!', 'warning');
        e.preventDefault();
        return false;
    }
    return true;
}

function submitAttachment(e) {
    const fileList = document.getElementById('fileList');
    if (!fileList || fileList.children.length === 0) {
        showToast('Vui lòng chọn file!', 'warning');
        e.preventDefault();
        return false;
    }
    return true;
}
</script>