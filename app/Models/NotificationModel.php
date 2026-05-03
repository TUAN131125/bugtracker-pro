<?php
class NotificationModel extends BaseModel {

    // Tạo thông báo mới
    public function create(int $userId, string $type, string $title, string $message = '', string $link = ''): void {
        $this->execute(
            "INSERT INTO notifications (user_id, type, title, message, link)
             VALUES (?, ?, ?, ?, ?)",
            [$userId, $type, $title, $message, $link]
        );
    }

    // Đếm thông báo chưa đọc (dùng cho badge trên navbar)
    public function countUnread(int $userId): int {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM notifications
             WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    // Lấy 10 thông báo mới nhất (dùng cho dropdown)
    public function getLatest(int $userId, int $limit = 10): array {
        return $this->fetchAll(
            "SELECT * FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }

    // Đánh dấu tất cả đã đọc
    public function markAllRead(int $userId): void {
        $this->execute(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ?",
            [$userId]
        );
    }

    public function getPaginated(int $userId, int $limit, int $offset): array {
        return $this->fetchAll(
            "SELECT * FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    // Đếm tổng
    public function countAll(int $userId): int {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ?",
            [$userId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    // Tìm theo ID
    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT * FROM notifications WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    // Đánh dấu 1 đã đọc
    public function markRead(int $id): void {
        $this->execute(
            "UPDATE notifications SET is_read = 1 WHERE id = ?",
            [$id]
        );
    }
}