<?php
class ProjectController extends BaseController {

    private ProjectModel     $projectModel;
    private ActivityLogModel $activityModel;

    public function __construct() {
        $this->projectModel  = new ProjectModel();
        $this->activityModel = new ActivityLogModel();
    }

    // Danh sách project
    public function index(): void {
        $this->requireAuth();
        $userId   = $_SESSION['user_id'];
        $projects = $this->projectModel->getByUser($userId);

        $this->view('projects/index', [
            'title'    => 'Dự án — ' . APP_NAME,
            'projects' => $projects,
        ]);
    }

    // Form tạo project mới
    public function createForm(): void {
        $this->requireAuth();

        $this->view('projects/create', [
            'title'      => 'Tạo dự án mới',
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['proj_errors'] ?? [],
            'old'        => $_SESSION['proj_old']    ?? [],
        ]);
        unset($_SESSION['proj_errors'], $_SESSION['proj_old']);
    }

    // Xử lý tạo project
    public function create(): void {
        $this->requireAuth(); 
        $this->verifyCsrf();

        $name        = trim($this->post('name', ''));
        $key         = strtoupper(trim($this->post('key', '')));
        $description = trim($this->post('description', ''));
        $visibility  = $this->post('visibility', 'private');

        $errors = [];

        if (mb_strlen($name) < 2)
            $errors['name'] = 'Tên dự án tối thiểu 2 ký tự';

        if (!preg_match('/^[A-Z0-9]{2,10}$/', $key))
            $errors['key'] = 'Project Key: 2-10 ký tự viết hoa, chỉ A-Z và 0-9';

        // Lấy workspace_id từ session
        $workspaceId = $_SESSION['workspace_id'] ?? 1;

        if (empty($errors['key']) && $this->projectModel->keyExists($key, $workspaceId))
            $errors['key'] = "Key '{$key}' đã được dùng trong workspace này";

        if (!in_array($visibility, ['public', 'private', 'team_only']))
            $visibility = 'private';

        if ($errors) {
            $_SESSION['proj_errors'] = $errors;
            $_SESSION['proj_old']    = compact('name', 'key', 'description', 'visibility');
            $this->redirect('/projects/new');
        }

        $projectId = $this->projectModel->create([
            'workspace_id' => $workspaceId,
            'name'         => $name,
            'key'          => $key,
            'description'  => $description,
            'owner_id'     => $_SESSION['user_id'],
            'visibility'   => $visibility,
        ]);

        // Ghi activity log
        $this->activityModel->log($_SESSION['user_id'], 'project_created', [
            'project_id' => $projectId,
            'new'        => ['name' => $name, 'key' => $key],
        ]);

        flashMessage('success', "Dự án <strong>{$name}</strong> đã được tạo thành công!");
        $this->redirect('/projects/' . strtolower($key));
    }

    // Trang chi tiết project (issue list)
    public function show(string $key): void {
        $this->requireAuth();
        $userId  = $_SESSION['user_id'];
        $project = $this->projectModel->findByKey($key);

        if (!$project) {
            http_response_code(404);
            die('<h1>404 — Dự án không tìm thấy</h1>');
        }

        // Kiểm tra quyền truy cập
        if ($project['visibility'] === 'private'
            && !$this->projectModel->isMember($project['id'], $userId)
            && $_SESSION['user_role'] !== 'admin'
        ) {
            http_response_code(403);
            die('<h1>403 — Bạn không có quyền xem dự án này</h1>');
        }

        // Lấy filters từ URL
        $filters = [
            'status'      => $this->get('status',   ''),
            'priority'    => $this->get('priority', ''),
            'type'        => $this->get('type',     ''),
            'assignee_id' => $this->get('assignee', ''),
            'search'      => $this->get('q',        ''),
            'sort'        => $this->get('sort',     'newest'),
        ];

        $page      = max(1, (int) $this->get('page', 1));
        $bugModel  = new BugModel();
        $bugs      = $bugModel->getByProject($project['id'], $filters, $page);
        $totalBugs = $bugModel->countByProject($project['id'], $filters);
        $totalPages= ceil($totalBugs / ITEMS_PER_PAGE);
        $stats     = $this->projectModel->getStats($project['id']);
        $members   = $this->projectModel->getMembers($project['id']);

        $this->view('projects/show', [
            'title'      => $project['name'] . ' — Issues',
            'project'    => $project,
            'bugs'       => $bugs,
            'filters'    => $filters,
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalBugs'  => $totalBugs,
            'stats'      => $stats,
            'members'    => $members,
        ]);
    }

    // Cài đặt project
    public function settings(string $key): void {
        $this->requireAuth();
        $project = $this->projectModel->findByKey($key);
        if (!$project) { http_response_code(404); die('Not found'); }

        $this->requireRole('admin', 'manager');

        $this->view('projects/settings', [
            'title'      => 'Cài đặt — ' . $project['name'],
            'project'    => $project,
            'members'    => $this->projectModel->getMembers($project['id']),
            'csrf_token' => $this->generateCsrfToken(),
            'errors'     => $_SESSION['proj_errors'] ?? [],
        ]);
        unset($_SESSION['proj_errors']);
    }

    // Lưu cài đặt project
    public function saveSettings(string $key): void {
        $this->requireRole('admin', 'manager');
        $this->verifyCsrf();

        $project = $this->projectModel->findByKey($key);
        if (!$project) { http_response_code(404); die('Not found'); }

        $this->projectModel->update($project['id'], [
            'name'        => trim($this->post('name', $project['name'])),
            'description' => trim($this->post('description', '')),
            'visibility'  => $this->post('visibility', 'private'),
            'status'      => $this->post('status', 'active'),
        ]);

        flashMessage('success', 'Đã lưu cài đặt dự án.');
        $this->redirect('/projects/' . $key . '/settings');
    }

    // Xóa project (Danger Zone)
    public function delete(string $key): void {
        $this->requireRole('admin');
        $this->verifyCsrf();

        $project = $this->projectModel->findByKey($key);
        if (!$project) { http_response_code(404); die('Not found'); }

        // Yêu cầu nhập đúng tên project để xác nhận
        $confirm = $this->post('confirm_name', '');
        if ($confirm !== $project['name']) {
            flashMessage('danger', 'Tên dự án không khớp. Xóa thất bại.');
            $this->redirect('/projects/' . $key . '/settings');
        }

        $this->projectModel->delete($project['id']);
        flashMessage('success', "Đã xóa dự án <strong>{$project['name']}</strong>.");
        $this->redirect('/projects');
    }
}