<?php
class ProjectModel extends BaseModel {

    // Lấy tất cả project user được tham gia
    public function getByUser(int $userId): array {
        return $this->fetchAll(
            "SELECT p.*,
                    u.full_name  AS owner_name,
                    COUNT(DISTINCT pm.user_id)  AS member_count,
                    COUNT(DISTINCT b.id)         AS total_bugs,
                    COUNT(DISTINCT CASE WHEN b.status = 'open' THEN b.id END) AS open_bugs
             FROM projects p
             JOIN project_members pm ON pm.project_id = p.id
             LEFT JOIN users u  ON u.id = p.owner_id
             LEFT JOIN bugs b   ON b.project_id = p.id
             WHERE pm.user_id = ?
               AND p.status   = 'active'
             GROUP BY p.id
             ORDER BY p.created_at DESC",
            [$userId]
        );
    }

    // Lấy 1 project theo key (vd: BUG, PRJ)
    public function findByKey(string $key): array|false {
        return $this->fetchOne(
            "SELECT p.*, u.full_name AS owner_name
             FROM projects p
             LEFT JOIN users u ON u.id = p.owner_id
             WHERE p.key = ?
             LIMIT 1",
            [strtoupper($key)]
        );
    }

    // Lấy 1 project theo ID
    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT * FROM projects WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    // Tạo project mới
    public function create(array $data): int {
        $projectId = $this->insert(
            "INSERT INTO projects
                (workspace_id, name, `key`, description, owner_id, visibility)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['workspace_id'],
                $data['name'],
                strtoupper($data['key']),
                $data['description'] ?? null,
                $data['owner_id'],
                $data['visibility'] ?? 'private',
            ]
        );

        // Tự động thêm owner vào project_members
        $this->addMember($projectId, $data['owner_id'], 'manager');

        return $projectId;
    }

    // Cập nhật project
    public function update(int $id, array $data): bool {
        return $this->execute(
            "UPDATE projects
             SET name = ?, description = ?, visibility = ?, status = ?
             WHERE id = ?",
            [
                $data['name'],
                $data['description'] ?? null,
                $data['visibility']  ?? 'private',
                $data['status']      ?? 'active',
                $id,
            ]
        ) > 0;
    }

    // Xóa project
    public function delete(int $id): bool {
        return $this->execute(
            "DELETE FROM projects WHERE id = ?",
            [$id]
        ) > 0;
    }

    // Thêm thành viên vào project
    public function addMember(int $projectId, int $userId, string $role = 'developer'): void {
        $this->execute(
            "INSERT IGNORE INTO project_members (project_id, user_id, role)
             VALUES (?, ?, ?)",
            [$projectId, $userId, $role]
        );
    }

    // Lấy danh sách thành viên project
    public function getMembers(int $projectId): array {
        return $this->fetchAll(
            "SELECT u.id, u.full_name, u.username, u.avatar, u.email,
                    pm.role, pm.joined_at
             FROM project_members pm
             JOIN users u ON u.id = pm.user_id
             WHERE pm.project_id = ?
             ORDER BY pm.joined_at ASC",
            [$projectId]
        );
    }

    // Kiểm tra user có trong project không
    public function isMember(int $projectId, int $userId): bool {
        return (bool) $this->fetchOne(
            "SELECT 1 FROM project_members
             WHERE project_id = ? AND user_id = ?
             LIMIT 1",
            [$projectId, $userId]
        );
    }

    // Lấy role của user trong project
    public function getMemberRole(int $projectId, int $userId): string {
        $row = $this->fetchOne(
            "SELECT role FROM project_members
             WHERE project_id = ? AND user_id = ?
             LIMIT 1",
            [$projectId, $userId]
        );
        return $row['role'] ?? '';
    }

    // Kiểm tra project key đã tồn tại trong workspace chưa
    public function keyExists(string $key, int $workspaceId, int $excludeId = 0): bool {
        return (bool) $this->fetchOne(
            "SELECT 1 FROM projects
             WHERE `key` = ? AND workspace_id = ? AND id != ?
             LIMIT 1",
            [strtoupper($key), $workspaceId, $excludeId]
        );
    }

    // Thống kê nhanh cho dashboard
    public function getStats(int $projectId): array {
        return $this->fetchOne(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'open')        AS open,
                SUM(status = 'in_progress') AS in_progress,
                SUM(status = 'resolved')    AS resolved,
                SUM(status = 'closed')      AS closed,
                SUM(priority = 'critical')  AS critical
             FROM bugs
             WHERE project_id = ?",
            [$projectId]
        ) ?: [];
    }

    public function countAll(): int {
        $row = $this->fetchOne("SELECT COUNT(*) AS cnt FROM projects", []);
        return (int)($row['cnt'] ?? 0);
    }
}