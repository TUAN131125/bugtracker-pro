<?php
class BugController extends BaseController {

    private BugModel          $bugModel;
    private ProjectModel      $projectModel;
    private ActivityLogModel  $activityModel;
    private NotificationModel $notifModel;

    public function __construct() {
        $this->bugModel       = new BugModel();
        $this->projectModel   = new ProjectModel();
        $this->activityModel  = new ActivityLogModel();
        $this->notifModel     = new NotificationModel();
    }

    // ══════════════════════════════════
    // XEM CHI TIẾT ISSUE
    // ══════════════════════════════════

    public function show(string $issueKey): void {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];

        $bug = $this->bugModel->findByKey($issueKey);
        if (!$bug) {
            http_response_code(404);
            die('<h1>404 — Issue không tìm thấy: ' . e($issueKey) . '</h1>');
        }

        // Kiểm tra quyền xem
        if (!$this->projectModel->isMember($bug['project_id'], $userId)
            && $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            die('<h1>403 — Bạn không có quyền xem issue này</h1>');
        }

        // Lấy comments
        $commentModel = new CommentModel();
        $comments     = $commentModel->getByBug($bug['id']);

        // Lấy activity log
        $activities = $this->activityModel->getByBug($bug['id']);

        // Lấy attachments
        $attachModel = new AttachmentModel();
        $attachments = $attachModel->getByBug($bug['id']);

        // Lấy linked issues (tạm thời trả về rỗng — ngày 4 Dev A làm)
        $linkedIssues = [];

        $this->view('issues/show', [
            'title'       => $bug['issue_key'] . ' — ' . truncate($bug['title'], 50),
            'bug'         => $bug,
            'comments'    => $comments,
            'activities'  => $activities,
            'attachments' => $attachments,
            'linkedIssues'=> $linkedIssues,
            'csrf_token'  => $this->generateCsrfToken(),
        ]);
    }

    // ══════════════════════════════════
    // TẠO ISSUE MỚI
    // ══════════════════════════════════

    public function createForm(string $projectKey): void {
        $this->requireAuth();

        $project = $this->projectModel->findByKey($projectKey);
        if (!$project) {
            http_response_code(404);
            die('Project not found');
        }

        // Kiểm tra quyền — reporter trở lên mới được tạo
        $role = $this->projectModel->getMemberRole(
            $project['id'], $_SESSION['user_id']
        );
        if (!in_array($role, ['admin','manager','developer','reporter'])
            && $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            die('<h1>403 — Bạn không có quyền tạo issue</h1>');
        }

        $members = $this->projectModel->getMembers($project['id']);

        $this->view('issues/create', [
            'title'      => 'Tạo Issue — ' . $project['name'],
            'project'    => $project,
            'members'    => $members,
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['bug_errors'] ?? [],
            'old'        => $_SESSION['bug_old']    ?? [],
        ]);

        unset($_SESSION['bug_errors'], $_SESSION['bug_old']);
    }

    public function create(string $projectKey): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $project = $this->projectModel->findByKey($projectKey);
        if (!$project) { http_response_code(404); die('Not found'); }

        // Lấy dữ liệu từ form
        $data = [
            'project_id'         => $project['id'],
            'reporter_id'        => $_SESSION['user_id'],
            'title'              => trim($this->post('title', '')),
            'description'        => trim($this->post('description', '')),
            'type'               => $this->post('type', 'bug'),
            'priority'           => $this->post('priority', 'medium'),
            'severity'           => $this->post('severity', null),
            'assignee_id'        => $this->post('assignee_id', null) ?: null,
            'due_date'           => $this->post('due_date', null) ?: null,
            'estimated_hours'    => $this->post('estimated_hours', null) ?: null,
            'steps_to_reproduce' => trim($this->post('steps_to_reproduce', '')),
            'environment'        => trim($this->post('environment', '')),
            'browser_info'       => trim($this->post('browser_info', '')),
        ];

        // Validate
        $errors = $this->validateBug($data);

        if ($errors) {
            $_SESSION['bug_errors'] = $errors;
            $_SESSION['bug_old']    = $data;
            $this->redirect('/projects/' . strtolower($projectKey) . '/issues/new');
        }

        // Tạo bug
        $bugId = $this->bugModel->create($data);
        $bug   = $this->bugModel->findByKey(
            $project['key'] . '-' . str_pad(
                $this->getLastBugNumber($project['id']), 3, '0', STR_PAD_LEFT
            )
        );

        // Ghi activity log
        $this->activityModel->log($_SESSION['user_id'], 'bug_created', [
            'bug_id'     => $bugId,
            'project_id' => $project['id'],
            'new'        => ['title' => $data['title'], 'type' => $data['type']],
        ]);

        // Gửi notification cho assignee (nếu có)
        if (!empty($data['assignee_id']) && $data['assignee_id'] != $_SESSION['user_id']) {
            $this->notifModel->create(
                $data['assignee_id'],
                'issue_assigned',
                'Bạn được giao issue mới',
                $data['title'],
                '/issues/' . ($bug['issue_key'] ?? '')
            );
        }

        // Xử lý file đính kèm nếu có
        if (!empty($_FILES['attachments']['name'][0])) {
            $this->handleAttachments($bugId, $_FILES['attachments']);
        }

        flashMessage('success', 'Issue <strong>' . e($data['title']) . '</strong> đã được tạo!');

        // Redirect về trang issue vừa tạo
        $newBug = $this->bugModel->findByKey($project['key'] . '-001');
        $this->redirect('/issues/' . ($bug['issue_key'] ?? strtoupper($projectKey) . '-001'));
    }

    // ══════════════════════════════════
    // CHỈNH SỬA ISSUE
    // ══════════════════════════════════

    public function editForm(string $issueKey): void {
        $this->requireAuth();
        $bug = $this->bugModel->findByKey($issueKey);
        if (!$bug) { http_response_code(404); die('Not found'); }

        // Chỉ developer trở lên mới được edit
        $role = $this->projectModel->getMemberRole(
            $bug['project_id'], $_SESSION['user_id']
        );
        if (!in_array($role, ['admin','manager','developer'])
            && $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            die('<h1>403 — Bạn không có quyền chỉnh sửa issue này</h1>');
        }

        $members = $this->projectModel->getMembers($bug['project_id']);

        $this->view('issues/edit', [
            'title'      => 'Sửa ' . $bug['issue_key'],
            'bug'        => $bug,
            'members'    => $members,
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['bug_errors'] ?? [],
        ]);

        unset($_SESSION['bug_errors']);
    }

    public function update(string $issueKey): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $bug = $this->bugModel->findByKey($issueKey);
        if (!$bug) { http_response_code(404); die('Not found'); }

        $data = [
            'title'              => trim($this->post('title', '')),
            'description'        => trim($this->post('description', '')),
            'type'               => $this->post('type', 'bug'),
            'status'             => $this->post('status', 'open'),
            'priority'           => $this->post('priority', 'medium'),
            'severity'           => $this->post('severity', null) ?: null,
            'assignee_id'        => $this->post('assignee_id', null) ?: null,
            'due_date'           => $this->post('due_date', null) ?: null,
            'estimated_hours'    => $this->post('estimated_hours', null) ?: null,
            'actual_hours'       => $this->post('actual_hours', null) ?: null,
            'steps_to_reproduce' => trim($this->post('steps_to_reproduce', '')),
            'environment'        => trim($this->post('environment', '')),
            'browser_info'       => trim($this->post('browser_info', '')),
            'resolution'         => trim($this->post('resolution', '')),
        ];

        $errors = $this->validateBug($data);
        if ($errors) {
            $_SESSION['bug_errors'] = $errors;
            $this->redirect('/issues/' . $issueKey . '/edit');
        }

        // Ghi lại thay đổi trước khi update
        $changes = $this->detectChanges($bug, $data);

        $this->bugModel->update($bug['id'], $data);

        // Ghi activity log cho từng thay đổi
        foreach ($changes as $field => $change) {
            $this->activityModel->log($_SESSION['user_id'],
                $field . '_changed', [
                    'bug_id'     => $bug['id'],
                    'project_id' => $bug['project_id'],
                    'old'        => [$field => $change['old']],
                    'new'        => [$field => $change['new']],
                ]
            );
        }

        // Thông báo assignee nếu bị đổi
        if (!empty($data['assignee_id'])
            && $data['assignee_id'] != $bug['assignee_id']
            && $data['assignee_id'] != $_SESSION['user_id']) {
            $this->notifModel->create(
                $data['assignee_id'],
                'issue_assigned',
                'Bạn được giao issue: ' . $issueKey,
                $data['title'],
                '/issues/' . $issueKey
            );
        }

        flashMessage('success', 'Đã cập nhật issue <strong>' . e($issueKey) . '</strong>');
        $this->redirect('/issues/' . $issueKey);
    }

    // ══════════════════════════════════
    // XÓA ISSUE
    // ══════════════════════════════════

    public function delete(string $issueKey): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $bug = $this->bugModel->findByKey($issueKey);
        if (!$bug) { http_response_code(404); die('Not found'); }

        // Chỉ admin / manager mới được xóa
        $role = $this->projectModel->getMemberRole(
            $bug['project_id'], $_SESSION['user_id']
        );
        if (!in_array($role, ['admin','manager'])
            && $_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            die('<h1>403 — Bạn không có quyền xóa issue này</h1>');
        }

        $projectKey = strtolower($bug['project_key']);
        $this->bugModel->delete($bug['id']);

        $this->activityModel->log($_SESSION['user_id'], 'bug_deleted', [
            'project_id' => $bug['project_id'],
            'new'        => ['issue_key' => $issueKey, 'title' => $bug['title']],
        ]);

        flashMessage('success', 'Đã xóa issue <strong>' . e($issueKey) . '</strong>');
        $this->redirect('/projects/' . $projectKey);
    }

    // ══════════════════════════════════
    // COMMENT
    // ══════════════════════════════════

    public function addComment(string $issueKey): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $bug = $this->bugModel->findByKey($issueKey);
        if (!$bug) { $this->json(['error' => 'Not found'], 404); }

        $content = trim($this->post('content', ''));
        if (empty($content)) {
            flashMessage('danger', 'Nội dung comment không được để trống.');
            $this->redirect('/issues/' . $issueKey);
        }

        $commentModel = new CommentModel();
        $commentId    = $commentModel->create([
            'bug_id'  => $bug['id'],
            'user_id' => $_SESSION['user_id'],
            'content' => $content,
        ]);

        $this->activityModel->log($_SESSION['user_id'], 'comment_added', [
            'bug_id'     => $bug['id'],
            'project_id' => $bug['project_id'],
        ]);

        // Thông báo cho reporter nếu người comment khác
        if ($bug['reporter_id'] != $_SESSION['user_id']) {
            $this->notifModel->create(
                $bug['reporter_id'],
                'comment_added',
                'Comment mới trên ' . $issueKey,
                truncate($content, 100),
                '/issues/' . $issueKey
            );
        }

        $this->redirect('/issues/' . $issueKey . '#comment-' . $commentId);
    }

    public function deleteComment(int $commentId): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $commentModel = new CommentModel();
        $comment      = $commentModel->findById($commentId);

        if (!$comment) {
            $this->json(['success' => false, 'error' => 'Not found'], 404);
        }

        // Chỉ chủ comment hoặc admin/manager mới được xóa
        if ($comment['user_id'] != $_SESSION['user_id']
            && !in_array($_SESSION['user_role'], ['admin','manager'])) {
            $this->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        $commentModel->delete($commentId);
        $this->json(['success' => true]);
    }

    // ══════════════════════════════════
    // ATTACHMENTS
    // ══════════════════════════════════

    public function addAttachment(string $issueKey): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $bug = $this->bugModel->findByKey($issueKey);
        if (!$bug) { http_response_code(404); die('Not found'); }

        if (empty($_FILES['attachments']['name'][0])) {
            flashMessage('danger', 'Vui lòng chọn file để upload.');
            $this->redirect('/issues/' . $issueKey);
        }

        $uploaded = $this->handleAttachments($bug['id'], $_FILES['attachments']);

        if ($uploaded > 0) {
            $this->activityModel->log($_SESSION['user_id'], 'attachment_added', [
                'bug_id'     => $bug['id'],
                'project_id' => $bug['project_id'],
                'new'        => ['count' => $uploaded],
            ]);
            flashMessage('success', "Đã upload {$uploaded} file thành công!");
        } else {
            flashMessage('danger', 'Không có file nào được upload. Kiểm tra lại định dạng và kích thước.');
        }

        $this->redirect('/issues/' . $issueKey . '#tab-attachments');
    }

    public function deleteAttachment(int $attachId): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $attachModel = new AttachmentModel();
        $attachment  = $attachModel->findById($attachId);

        if (!$attachment) {
            $this->json(['success' => false], 404);
        }

        // Xóa file vật lý
        $filePath = ROOT_PATH . '/uploads/' . $attachment['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $attachModel->delete($attachId);
        $this->json(['success' => true]);
    }

    // ══════════════════════════════════
    // AJAX ACTIONS
    // ══════════════════════════════════

    // Đổi status qua AJAX (click từ sidebar issue detail)
    public function changeStatus(string $issueKey): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $bug = $this->bugModel->findByKey($issueKey);
        if (!$bug) { $this->json(['success' => false], 404); }

        $newStatus = $this->post('status', '');
        $allowed   = ['open','in_progress','review','resolved','closed'];

        if (!in_array($newStatus, $allowed)) {
            $this->json(['success' => false, 'error' => 'Invalid status']);
        }

        $oldStatus = $bug['status'];
        $this->bugModel->update($bug['id'], array_merge($bug, ['status' => $newStatus]));

        $this->activityModel->log($_SESSION['user_id'], 'status_changed', [
            'bug_id'     => $bug['id'],
            'project_id' => $bug['project_id'],
            'old'        => ['status' => $oldStatus],
            'new'        => ['status' => $newStatus],
        ]);

        $this->json(['success' => true, 'status' => $newStatus]);
    }

    // Vote cho issue
    public function vote(string $issueKey): void {
        $this->requireAuth();

        $bug = $this->bugModel->findByKey($issueKey);
        if (!$bug) { $this->json(['success' => false], 404); }

        // Tăng vote đơn giản (không check trùng — ngày 5 Dev A cải tiến)
        $db   = Database::getInstance();
        $stmt = $db->prepare("UPDATE bugs SET votes = votes + 1 WHERE id = ?");
        $stmt->execute([$bug['id']]);

        $updated = $this->bugModel->findByKey($issueKey);
        $this->json(['success' => true, 'votes' => $updated['votes']]);
    }

    // ══════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════

    private function validateBug(array $data): array {
        $errors = [];

        if (mb_strlen($data['title']) < 5)
            $errors['title'] = 'Tiêu đề tối thiểu 5 ký tự';

        if (mb_strlen($data['title']) > 500)
            $errors['title'] = 'Tiêu đề tối đa 500 ký tự';

        if (!in_array($data['type'], ['bug','feature','task','improvement','question','epic']))
            $errors['type'] = 'Loại issue không hợp lệ';

        if (!in_array($data['priority'], ['critical','high','medium','low','trivial']))
            $errors['priority'] = 'Priority không hợp lệ';

        if (!empty($data['due_date']) && !strtotime($data['due_date']))
            $errors['due_date'] = 'Ngày hạn không hợp lệ';

        if (!empty($data['estimated_hours']) && !is_numeric($data['estimated_hours']))
            $errors['estimated_hours'] = 'Số giờ ước tính phải là số';

        return $errors;
    }

    private function detectChanges(array $old, array $new): array {
        $watchFields = ['status','priority','assignee_id','title'];
        $changes     = [];

        foreach ($watchFields as $field) {
            if (isset($old[$field], $new[$field])
                && (string)$old[$field] !== (string)$new[$field]) {
                $changes[$field] = [
                    'old' => $old[$field],
                    'new' => $new[$field],
                ];
            }
        }

        return $changes;
    }

    private function handleAttachments(int $bugId, array $files): int {
        $attachModel = new AttachmentModel();
        $uploaded    = 0;
        $uploadDir   = ROOT_PATH . '/uploads/attachments/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileCount = count($files['name']);
        $maxFiles  = 5;

        for ($i = 0; $i < min($fileCount, $maxFiles); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $file = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];

            $uploadErrors = validateUpload($file);
            if ($uploadErrors) continue;

            $filename = generateFilename($files['name'][$i]);

            if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $filename)) {
                $attachModel->create([
                    'bug_id'        => $bugId,
                    'user_id'       => $_SESSION['user_id'],
                    'filename'      => 'attachments/' . $filename,
                    'original_name' => $files['name'][$i],
                    'file_size'     => $files['size'][$i],
                    'mime_type'     => $files['type'][$i],
                ]);
                $uploaded++;
            }
        }

        return $uploaded;
    }

    private function getLastBugNumber(int $projectId): int {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT COUNT(*) AS cnt FROM bugs WHERE project_id = ?"
        );
        $stmt->execute([$projectId]);
        return (int) $stmt->fetch()['cnt'];
    }
}