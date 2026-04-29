<?php
class BugModel extends BaseModel {

    // Lấy danh sách bug theo project với filter + phân trang
    public function getByProject(int $projectId, array $filters = [], int $page = 1): array {
        $where  = ["b.project_id = ?"];
        $params = [$projectId];

        if (!empty($filters['status'])) {
            $where[]  = "b.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $where[]  = "b.priority = ?";
            $params[] = $filters['priority'];
        }
        if (!empty($filters['type'])) {
            $where[]  = "b.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['assignee_id'])) {
            $where[]  = "b.assignee_id = ?";
            $params[] = $filters['assignee_id'];
        }
        if (!empty($filters['search'])) {
            $where[]  = "(b.title LIKE ? OR b.description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSQL = implode(' AND ', $where);
        $perPage  = ITEMS_PER_PAGE;
        $offset   = ($page - 1) * $perPage;

        $sortMap = [
            'newest'   => 'b.created_at DESC',
            'updated'  => 'b.updated_at DESC',
            'priority' => "FIELD(b.priority,'critical','high','medium','low','trivial')",
        ];
        $sort = $sortMap[$filters['sort'] ?? 'newest'] ?? 'b.created_at DESC';

        return $this->fetchAll(
            "SELECT b.*,
                    reporter.full_name  AS reporter_name,
                    reporter.avatar     AS reporter_avatar,
                    assignee.full_name  AS assignee_name,
                    assignee.avatar     AS assignee_avatar
             FROM bugs b
             JOIN users reporter ON reporter.id = b.reporter_id
             LEFT JOIN users assignee ON assignee.id = b.assignee_id
             WHERE {$whereSQL}
             ORDER BY {$sort}
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
    }

    // Đếm tổng số bug để phân trang
    public function countByProject(int $projectId, array $filters = []): int {
        $where  = ["project_id = ?"];
        $params = [$projectId];

        if (!empty($filters['status']))   { $where[] = "status = ?";     $params[] = $filters['status']; }
        if (!empty($filters['priority'])) { $where[] = "priority = ?";   $params[] = $filters['priority']; }
        if (!empty($filters['type']))     { $where[] = "type = ?";       $params[] = $filters['type']; }
        if (!empty($filters['search']))   {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSQL = implode(' AND ', $where);
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM bugs WHERE {$whereSQL}",
            $params
        );
        return (int) ($row['cnt'] ?? 0);
    }

    // Lấy chi tiết 1 bug theo issue_key (vd: BUG-042)
    public function findByKey(string $issueKey): array|false {
        return $this->fetchOne(
            "SELECT b.*,
                    p.name   AS project_name,
                    p.key    AS project_key,
                    reporter.full_name  AS reporter_name,
                    reporter.avatar     AS reporter_avatar,
                    reporter.username   AS reporter_username,
                    assignee.full_name  AS assignee_name,
                    assignee.avatar     AS assignee_avatar
             FROM bugs b
             JOIN projects p       ON p.id = b.project_id
             JOIN users reporter   ON reporter.id = b.reporter_id
             LEFT JOIN users assignee ON assignee.id = b.assignee_id
             WHERE b.issue_key = ?
             LIMIT 1",
            [strtoupper($issueKey)]
        );
    }

    // Tạo bug mới + tự sinh issue_key
    public function create(array $data): int {
        // Sinh issue key: PRJ-001, BUG-042...
        $issueKey = $this->generateIssueKey($data['project_id']);

        $bugId = $this->insert(
            "INSERT INTO bugs
                (issue_key, project_id, title, description, type,
                 status, priority, severity, reporter_id, assignee_id,
                 sprint_id, milestone_id, due_date, estimated_hours,
                 steps_to_reproduce, environment, browser_info)
             VALUES (?,?,?,?,?, ?,?,?,?,?, ?,?,?,?,?,?,?)",
            [
                $issueKey,
                $data['project_id'],
                $data['title'],
                $data['description']       ?? null,
                $data['type']              ?? 'bug',
                'open',
                $data['priority']          ?? 'medium',
                $data['severity']          ?? null,
                $data['reporter_id'],
                $data['assignee_id']       ?? null,
                $data['sprint_id']         ?? null,
                $data['milestone_id']      ?? null,
                $data['due_date']          ?? null,
                $data['estimated_hours']   ?? null,
                $data['steps_to_reproduce']?? null,
                $data['environment']       ?? null,
                $data['browser_info']      ?? null,
            ]
        );

        return $bugId;
    }

    // Cập nhật bug
    public function update(int $id, array $data): bool {
        return $this->execute(
            "UPDATE bugs
             SET title = ?, description = ?, type = ?, status = ?,
                 priority = ?, severity = ?, assignee_id = ?,
                 due_date = ?, estimated_hours = ?, actual_hours = ?,
                 steps_to_reproduce = ?, environment = ?,
                 browser_info = ?, resolution = ?,
                 resolved_at = IF(? = 'resolved' AND resolved_at IS NULL, NOW(), resolved_at)
             WHERE id = ?",
            [
                $data['title'],
                $data['description']        ?? null,
                $data['type']               ?? 'bug',
                $data['status']             ?? 'open',
                $data['priority']           ?? 'medium',
                $data['severity']           ?? null,
                $data['assignee_id']        ?? null,
                $data['due_date']           ?? null,
                $data['estimated_hours']    ?? null,
                $data['actual_hours']       ?? null,
                $data['steps_to_reproduce'] ?? null,
                $data['environment']        ?? null,
                $data['browser_info']       ?? null,
                $data['resolution']         ?? null,
                $data['status']             ?? 'open',
                $id,
            ]
        ) > 0;
    }

    // Xóa bug
    public function delete(int $id): bool {
        return $this->execute("DELETE FROM bugs WHERE id = ?", [$id]) > 0;
    }

    // Bug được giao cho user (dùng cho dashboard)
    public function getAssignedTo(int $userId, int $limit = 10): array {
        return $this->fetchAll(
            "SELECT b.*, p.name AS project_name, p.key AS project_key
             FROM bugs b
             JOIN projects p ON p.id = b.project_id
             WHERE b.assignee_id = ?
               AND b.status NOT IN ('resolved','closed')
             ORDER BY
                 FIELD(b.priority,'critical','high','medium','low','trivial'),
                 b.updated_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }

    // Bug sắp đến hạn trong N ngày (dùng cho dashboard)
    public function getUpcomingDeadlines(int $userId, int $days = 7): array {
        return $this->fetchAll(
            "SELECT b.*, p.name AS project_name, p.key AS project_key
             FROM bugs b
             JOIN projects p ON p.id = b.project_id
             WHERE b.assignee_id = ?
               AND b.due_date IS NOT NULL
               AND b.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
               AND b.status NOT IN ('resolved','closed')
             ORDER BY b.due_date ASC",
            [$userId, $days]
        );
    }

    // Bug trend 30 ngày qua (dùng cho chart)
    public function getTrend(int $projectId, int $days = 30): array {
        return $this->fetchAll(
            "SELECT
                DATE(created_at) AS date,
                COUNT(*)         AS created,
                SUM(status IN ('resolved','closed')) AS resolved
             FROM bugs
             WHERE project_id = ?
               AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$projectId, $days]
        );
    }

    // Thống kê tổng hợp cho dashboard
    public function getDashboardStats(int $userId): array {
        return $this->fetchOne(
            "SELECT
                SUM(b.assignee_id = ?)                                          AS assigned_to_me,
                SUM(b.assignee_id = ? AND b.status = 'in_progress')             AS in_progress,
                SUM(b.assignee_id = ?
                    AND b.due_date < CURDATE()
                    AND b.status NOT IN ('resolved','closed'))                   AS overdue,
                SUM(b.assignee_id = ?
                    AND b.status = 'resolved'
                    AND DATE(b.resolved_at) = CURDATE())                        AS resolved_today
             FROM bugs b
             JOIN project_members pm ON pm.project_id = b.project_id
             WHERE pm.user_id = ?",
            [$userId, $userId, $userId, $userId, $userId]
        ) ?: ['assigned_to_me' => 0, 'in_progress' => 0, 'overdue' => 0, 'resolved_today' => 0];
    }

    // Tự động sinh issue key (PRJ-001, PRJ-002...)
    private function generateIssueKey(int $projectId): string {
        $project = $this->fetchOne(
            "SELECT `key` FROM projects WHERE id = ? LIMIT 1",
            [$projectId]
        );
        $prefix = $project['key'] ?? 'BUG';

        $last = $this->fetchOne(
            "SELECT issue_key FROM bugs
             WHERE project_id = ?
             ORDER BY id DESC LIMIT 1",
            [$projectId]
        );

        if ($last) {
            preg_match('/(\d+)$/', $last['issue_key'], $matches);
            $num = ((int)($matches[1] ?? 0)) + 1;
        } else {
            $num = 1;
        }

        return $prefix . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}