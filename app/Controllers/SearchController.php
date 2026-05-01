<?php
class SearchController extends BaseController {

    public function search(): void {
        $this->requireAuth();

        $query  = trim($this->get('q', ''));
        $userId = $_SESSION['user_id'];

        if (mb_strlen($query) < 2) {
            $this->json(['issues' => [], 'projects' => [], 'users' => []]);
        }

        $db = Database::getInstance();

        // Tìm issues (chỉ trong project user có quyền)
        $stmt = $db->prepare(
            "SELECT b.issue_key, b.title, b.status, b.priority,
                    p.name AS project_name, p.key AS project_key
             FROM bugs b
             JOIN projects p ON p.id = b.project_id
             JOIN project_members pm ON pm.project_id = p.id
             WHERE pm.user_id = ?
               AND (b.title LIKE ? OR b.issue_key LIKE ? OR b.description LIKE ?)
             ORDER BY b.updated_at DESC
             LIMIT 8"
        );
        $like = '%' . $query . '%';
        $stmt->execute([$userId, $like, $like, $like]);
        $issues = $stmt->fetchAll();

        // Tìm projects
        $stmt = $db->prepare(
            "SELECT p.id, p.name, p.key, p.description
             FROM projects p
             JOIN project_members pm ON pm.project_id = p.id
             WHERE pm.user_id = ?
               AND (p.name LIKE ? OR p.key LIKE ?)
             LIMIT 5"
        );
        $stmt->execute([$userId, $like, $like]);
        $projects = $stmt->fetchAll();

        // Tìm users (trong cùng workspace)
        $stmt = $db->prepare(
            "SELECT DISTINCT u.id, u.full_name, u.username, u.avatar, u.role
             FROM users u
             JOIN workspace_members wm ON wm.user_id = u.id
             WHERE wm.workspace_id = ?
               AND (u.full_name LIKE ? OR u.username LIKE ?)
             LIMIT 5"
        );
        $wsId = $_SESSION['workspace_id'] ?? 1;
        $stmt->execute([$wsId, $like, $like]);
        $users = $stmt->fetchAll();

        $this->json([
            'query'    => $query,
            'issues'   => $issues,
            'projects' => $projects,
            'users'    => $users,
        ]);
    }
}