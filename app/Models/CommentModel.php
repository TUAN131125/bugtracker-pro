<?php
class CommentModel extends BaseModel {

    public function getByBug(int $bugId): array {
        return $this->fetchAll(
            "SELECT c.*, u.full_name AS user_name, u.avatar AS user_avatar
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.bug_id = ?
             ORDER BY c.created_at ASC",
            [$bugId]
        );
    }

    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT * FROM comments WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    public function create(array $data): int {
        return $this->insert(
            "INSERT INTO comments (bug_id, user_id, content)
             VALUES (?, ?, ?)",
            [$data['bug_id'], $data['user_id'], $data['content']]
        );
    }

    public function update(int $id, string $content): bool {
        return $this->execute(
            "UPDATE comments SET content = ? WHERE id = ?",
            [$content, $id]
        ) > 0;
    }

    public function delete(int $id): bool {
        return $this->execute(
            "DELETE FROM comments WHERE id = ?",
            [$id]
        ) > 0;
    }
}