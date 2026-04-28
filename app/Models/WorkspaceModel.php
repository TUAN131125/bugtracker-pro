<?php
class WorkspaceModel extends BaseModel {

    // Tạo workspace mới
    public function create(array $data): int {
        return $this->insert(
            "INSERT INTO workspaces (name, slug, type, owner_id)
             VALUES (?, ?, ?, ?)",
            [
                $data['name'],
                $data['slug'],
                $data['type'] ?? 'team',
                $data['owner_id'],
            ]
        );
    }

    // Thêm owner vào workspace_members với role admin
    public function addMember(int $workspaceId, int $userId, string $role = 'admin'): void {
        $this->execute(
            "INSERT IGNORE INTO workspace_members (workspace_id, user_id, role)
             VALUES (?, ?, ?)",
            [$workspaceId, $userId, $role]
        );
    }

    // Kiểm tra slug đã tồn tại chưa
    public function slugExists(string $slug): bool {
        return (bool) $this->fetchOne(
            "SELECT id FROM workspaces WHERE slug = ? LIMIT 1",
            [$slug]
        );
    }

    // Lấy workspace theo owner
    public function findByOwner(int $ownerId): array {
        return $this->fetchAll(
            "SELECT * FROM workspaces WHERE owner_id = ? ORDER BY created_at DESC",
            [$ownerId]
        );
    }

    // Lưu invitation token
    public function createInvitation(array $data): int {
        return $this->insert(
            "INSERT INTO workspace_invitations
                (workspace_id, email, role, token, invited_by, expires_at)
             VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))",
            [
                $data['workspace_id'],
                $data['email'],
                $data['role'] ?? 'developer',
                $data['token'],
                $data['invited_by'],
            ]
        );
    }
}