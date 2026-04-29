<?php
class ActivityLogModel extends BaseModel {

    // Ghi log hoạt động
    public function log(int $userId, string $action, array $context = []): void {
        $this->execute(
            "INSERT INTO activity_log
                (user_id, bug_id, project_id, action, old_value, new_value)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $context['bug_id']     ?? null,
                $context['project_id'] ?? null,
                $action,
                isset($context['old']) ? json_encode($context['old'], JSON_UNESCAPED_UNICODE) : null,
                isset($context['new']) ? json_encode($context['new'], JSON_UNESCAPED_UNICODE) : null,
            ]
        );
    }

    // Lấy activity gần nhất của user
    public function getRecent(int $userId, int $limit = 20): array {
        return $this->fetchAll(
            "SELECT al.*,
                    u.full_name AS user_name,
                    u.avatar    AS user_avatar,
                    b.title     AS bug_title,
                    b.issue_key AS bug_key,
                    p.name      AS project_name
             FROM activity_log al
             JOIN users u       ON u.id = al.user_id
             LEFT JOIN bugs b   ON b.id = al.bug_id
             LEFT JOIN projects p ON p.id = al.project_id
             WHERE al.project_id IN (
                 SELECT project_id FROM project_members WHERE user_id = ?
             )
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }

    // Lấy activity theo bug (dùng cho tab Activity trong issue detail)
    public function getByBug(int $bugId): array {
        return $this->fetchAll(
            "SELECT al.*, u.full_name AS user_name, u.avatar AS user_avatar
             FROM activity_log al
             JOIN users u ON u.id = al.user_id
             WHERE al.bug_id = ?
             ORDER BY al.created_at ASC",
            [$bugId]
        );
    }
}