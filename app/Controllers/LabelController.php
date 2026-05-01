<?php
class LabelController extends BaseController {

    public function index(string $key): void {
        $this->requireAuth();
        $projModel = new ProjectModel();
        $project   = $projModel->findByKey($key);
        if (!$project) { http_response_code(404); die('Not found'); }

        $db     = Database::getInstance();
        $stmt   = $db->prepare(
            "SELECT l.*, COUNT(bl.bug_id) AS usage_count
             FROM labels l
             LEFT JOIN bug_labels bl ON bl.label_id = l.id
             WHERE l.project_id = ?
             GROUP BY l.id
             ORDER BY l.name ASC"
        );
        $stmt->execute([$project['id']]);
        $labels = $stmt->fetchAll();

        $this->view('projects/labels', [
            'title'      => 'Labels — ' . $project['name'],
            'project'    => $project,
            'labels'     => $labels,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function create(string $key): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $projModel = new ProjectModel();
        $project   = $projModel->findByKey($key);
        if (!$project) { http_response_code(404); die('Not found'); }

        $name  = trim($this->post('name', ''));
        $color = $this->post('color', '#0078D4');

        if (mb_strlen($name) >= 2) {
            $db   = Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO labels (project_id, name, color) VALUES (?, ?, ?)"
            );
            $stmt->execute([$project['id'], $name, $color]);
            flashMessage('success', "Label <strong>{$name}</strong> đã được tạo!");
        }

        $this->redirect('/projects/' . strtolower($key) . '/labels');
    }

    public function delete(string $key, int $id): void {
        $this->requireAuth();
        $this->verifyCsrf();

        $db   = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM labels WHERE id = ?");
        $stmt->execute([$id]);

        flashMessage('success', 'Đã xóa label.');
        $this->redirect('/projects/' . strtolower($key) . '/labels');
    }
}