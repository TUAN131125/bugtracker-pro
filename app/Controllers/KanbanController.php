<?php
class KanbanController extends BaseController {

    private ProjectModel     $projectModel;
    private BugModel         $bugModel;
    private SprintModel      $sprintModel;
    private ActivityLogModel $activityModel;

    // Cột Kanban và thứ tự cố định
    const COLUMNS = [
        'open'        => ['label' => 'Mở',          'color' => '#0078D4', 'icon' => 'fa-circle'],
        'in_progress' => ['label' => 'Đang xử lý',  'color' => '#FD7E14', 'icon' => 'fa-spinner'],
        'review'      => ['label' => 'Đang review',  'color' => '#8B5CF6', 'icon' => 'fa-eye'],
        'resolved'    => ['label' => 'Đã giải quyết','color' => '#28A745', 'icon' => 'fa-check-circle'],
        'closed'      => ['label' => 'Đóng',         'color' => '#6C757D', 'icon' => 'fa-times-circle'],
    ];

    // WIP limit mặc định cho từng cột (0 = không giới hạn)
    const WIP_LIMITS = [
        'open'        => 0,
        'in_progress' => 5,
        'review'      => 3,
        'resolved'    => 0,
        'closed'      => 0,
    ];

    public function __construct() {
        $this->projectModel  = new ProjectModel();
        $this->bugModel      = new BugModel();
        $this->sprintModel   = new SprintModel();
        $this->activityModel = new ActivityLogModel();
    }

    // ══════════════════════════════════════════
    // KANBAN BOARD — GET /projects/:key/board
    // ══════════════════════════════════════════

    public function board(string $projectKey): void {
        $this->requireAuth();
        $userId  = $_SESSION['user_id'];
        $project = $this->projectModel->findByKey($projectKey);

        if (!$project) {
            http_response_code(404);
            die('<h1>404 — Dự án không tìm thấy</h1>');
        }

        // Kiểm tra quyền
        if ($project['visibility'] === 'private'
            && !$this->projectModel->isMember($project['id'], $userId)
            && $_SESSION['user_role'] !== 'admin'
        ) {
            http_response_code(403);
            die('<h1>403 — Bạn không có quyền xem dự án này</h1>');
        }

        // Filter
        $filterAssignee  = $this->get('assignee',  '');
        $filterPriority  = $this->get('priority',  '');
        $filterSprint    = $this->get('sprint_id', '');

        // Lấy bugs theo từng cột
        $columns = [];
        foreach (array_keys(self::COLUMNS) as $status) {
            $columns[$status] = $this->bugModel->getKanbanColumn(
                $project['id'], $status, [
                    'assignee_id' => $filterAssignee,
                    'priority'    => $filterPriority,
                    'sprint_id'   => $filterSprint,
                ]
            );
        }

        $members = $this->projectModel->getMembers($project['id']);
        $sprints = $this->sprintModel->getByProject($project['id']);

        $this->view('kanban/board', [
            'title'          => 'Kanban — ' . $project['name'],
            'project'        => $project,
            'columns'        => $columns,
            'columnConfig'   => self::COLUMNS,
            'wipLimits'      => self::WIP_LIMITS,
            'members'        => $members,
            'sprints'        => $sprints,
            'filterAssignee' => $filterAssignee,
            'filterPriority' => $filterPriority,
            'filterSprint'   => $filterSprint,
        ]);
    }

    // ══════════════════════════════════════════
    // AJAX: Cập nhật status card (kéo thả)
    // POST /projects/:key/board/move
    // Body: issue_key, new_status, csrf_token
    // ══════════════════════════════════════════

    public function moveCard(string $projectKey): void {
        $this->requireAuth();

        // Verify CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['ok' => false, 'error' => 'CSRF không hợp lệ'], 403);
        }

        $issueKey  = strtoupper(trim($_POST['issue_key'] ?? ''));
        $newStatus = trim($_POST['new_status'] ?? '');

        // Validate status
        if (!array_key_exists($newStatus, self::COLUMNS)) {
            $this->json(['ok' => false, 'error' => 'Status không hợp lệ'], 400);
        }

        $bug     = $this->bugModel->findByKey($issueKey);
        $project = $this->projectModel->findByKey($projectKey);

        if (!$bug || !$project || $bug['project_id'] !== $project['id']) {
            $this->json(['ok' => false, 'error' => 'Issue không tìm thấy'], 404);
        }

        $userId  = $_SESSION['user_id'];
        $oldStatus = $bug['status'];

        // Cập nhật status
        $updated = $this->bugModel->updateStatus($bug['id'], $newStatus);
        if (!$updated) {
            $this->json(['ok' => false, 'error' => 'Cập nhật thất bại'], 500);
        }

        // Ghi activity log
        $this->activityModel->log($userId, 'status_changed', [
            'bug_id'     => $bug['id'],
            'project_id' => $project['id'],
            'old'        => ['status' => $oldStatus],
            'new'        => ['status' => $newStatus],
        ]);

        $this->json([
            'ok'         => true,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    // ══════════════════════════════════════════
    // AJAX: Quick-view popup cho card
    // GET /issues/:key/quickview
    // ══════════════════════════════════════════

    public function quickView(string $issueKey): void {
        $this->requireAuth();

        $bug = $this->bugModel->findByKey($issueKey);
        if (!$bug) {
            $this->json(['ok' => false, 'error' => 'Issue không tìm thấy'], 404);
        }

        if (!$this->projectModel->isMember($bug['project_id'], $_SESSION['user_id'])
            && $_SESSION['user_role'] !== 'admin') {
            $this->json(['ok' => false, 'error' => 'Không có quyền'], 403);
        }

        $this->json([
            'ok'  => true,
            'bug' => [
                'id'           => $bug['id'],
                'issue_key'    => $bug['issue_key'],
                'title'        => $bug['title'],
                'type'         => $bug['type'],
                'status'       => $bug['status'],
                'priority'     => $bug['priority'],
                'severity'     => $bug['severity'],
                'assignee_name'=> $bug['assignee_name'] ?? null,
                'reporter_name'=> $bug['reporter_name'],
                'due_date'     => $bug['due_date'],
                'description'  => mb_substr($bug['description'] ?? '', 0, 300),
                'url'          => APP_URL . '/issues/' . strtolower($bug['issue_key']),
            ],
        ]);
    }

    // ══════════════════════════════════════════
    // SPRINT BOARD — GET /projects/:key/sprint
    // ══════════════════════════════════════════

    public function sprintBoard(string $projectKey): void {
        $this->requireAuth();
        $userId  = $_SESSION['user_id'];
        $project = $this->projectModel->findByKey($projectKey);

        if (!$project) {
            http_response_code(404);
            die('<h1>404 — Dự án không tìm thấy</h1>');
        }

        if ($project['visibility'] === 'private'
            && !$this->projectModel->isMember($project['id'], $userId)
            && $_SESSION['user_role'] !== 'admin'
        ) {
            http_response_code(403);
            die('<h1>403 — Không có quyền</h1>');
        }

        $sprints      = $this->sprintModel->getByProject($project['id']);
        $activeSprint = $this->sprintModel->getActive($project['id']);
        $backlog      = $this->sprintModel->getBacklog($project['id']);

        // Burndown data cho sprint đang active
        $burndownData = [];
        if ($activeSprint) {
            $burndownData = $this->sprintModel->getBurndownData($activeSprint['id']);
        }

        $this->view('kanban/sprint', [
            'title'        => 'Sprint — ' . $project['name'],
            'project'      => $project,
            'sprints'      => $sprints,
            'activeSprint' => $activeSprint,
            'backlog'      => $backlog,
            'burndownData' => $burndownData,
            'csrf_token'   => $this->generateCsrfToken(),
        ]);
    }

    // ══════════════════════════════════════════
    // Tạo Sprint mới
    // POST /projects/:key/sprint/create
    // ══════════════════════════════════════════

    public function createSprint(string $projectKey): void {
        $this->requireRole('admin', 'manager');
        $this->verifyCsrf();

        $project = $this->projectModel->findByKey($projectKey);
        if (!$project) {
            http_response_code(404); die('Not found');
        }

        $name       = trim($this->post('name', ''));
        $goal       = trim($this->post('goal', ''));
        $start_date = $this->post('start_date', null);
        $end_date   = $this->post('end_date', null);

        if (mb_strlen($name) < 2) {
            flashMessage('danger', 'Tên sprint tối thiểu 2 ký tự.');
            $this->redirect('/projects/' . strtolower($projectKey) . '/sprint');
        }

        $this->sprintModel->create([
            'project_id' => $project['id'],
            'name'       => $name,
            'goal'       => $goal ?: null,
            'start_date' => $start_date ?: null,
            'end_date'   => $end_date   ?: null,
        ]);

        flashMessage('success', "Sprint <strong>{$name}</strong> đã được tạo.");
        $this->redirect('/projects/' . strtolower($projectKey) . '/sprint');
    }

    // ══════════════════════════════════════════
    // Bắt đầu Sprint
    // POST /projects/:key/sprint/:id/start
    // ══════════════════════════════════════════

    public function startSprint(string $projectKey, string $sprintId): void {
        $this->requireRole('admin', 'manager');
        $this->verifyCsrf();

        $sprint  = $this->sprintModel->findById((int)$sprintId);
        $project = $this->projectModel->findByKey($projectKey);

        if (!$sprint || !$project || $sprint['project_id'] !== $project['id']) {
            http_response_code(404); die('Not found');
        }

        // Kiểm tra có sprint active chưa
        $existing = $this->sprintModel->getActive($project['id']);
        if ($existing) {
            flashMessage('danger', 'Đã có sprint đang chạy. Hãy hoàn thành trước khi bắt đầu sprint mới.');
            $this->redirect('/projects/' . strtolower($projectKey) . '/sprint');
        }

        $this->sprintModel->start((int)$sprintId);
        flashMessage('success', "Sprint <strong>{$sprint['name']}</strong> đã bắt đầu!");
        $this->redirect('/projects/' . strtolower($projectKey) . '/sprint');
    }

    // ══════════════════════════════════════════
    // Hoàn thành Sprint
    // POST /projects/:key/sprint/:id/complete
    // ══════════════════════════════════════════

    public function completeSprint(string $projectKey, string $sprintId): void {
        $this->requireRole('admin', 'manager');
        $this->verifyCsrf();

        $sprint  = $this->sprintModel->findById((int)$sprintId);
        $project = $this->projectModel->findByKey($projectKey);

        if (!$sprint || !$project || $sprint['project_id'] !== $project['id']) {
            http_response_code(404); die('Not found');
        }

        // Chuyển issue chưa done về backlog
        $moveUnfinished = $this->post('move_unfinished', 'backlog');
        if ($moveUnfinished === 'backlog') {
            $this->bugModel->moveUnfinishedToBacklog((int)$sprintId);
        }

        $this->sprintModel->complete((int)$sprintId);
        flashMessage('success', "Sprint <strong>{$sprint['name']}</strong> đã hoàn thành!");
        $this->redirect('/projects/' . strtolower($projectKey) . '/sprint');
    }

    // ══════════════════════════════════════════
    // AJAX: Gán issue vào sprint (kéo thả backlog)
    // POST /projects/:key/sprint/assign-issue
    // Body: bug_id, sprint_id (null = backlog)
    // ══════════════════════════════════════════

    public function assignIssue(string $projectKey): void {
        $this->requireAuth();

        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['ok' => false, 'error' => 'CSRF không hợp lệ'], 403);
        }

        $bugId    = (int)($_POST['bug_id']    ?? 0);
        $sprintId = ($_POST['sprint_id'] ?? '') === '' ? null : (int)$_POST['sprint_id'];

        if (!$bugId) {
            $this->json(['ok' => false, 'error' => 'bug_id không hợp lệ'], 400);
        }

        $result = $this->sprintModel->assignIssue($bugId, $sprintId);
        $this->json(['ok' => $result]);
    }
}
