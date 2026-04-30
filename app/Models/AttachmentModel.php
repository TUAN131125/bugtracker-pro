<?php
class AttachmentModel extends BaseModel {

    public function getByBug(int $bugId): array {
        return $this->fetchAll(
            "SELECT a.*, u.full_name AS uploader_name
             FROM attachments a
             JOIN users u ON u.id = a.user_id
             WHERE a.bug_id = ?
             ORDER BY a.created_at DESC",
            [$bugId]
        );
    }

    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT * FROM attachments WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    public function create(array $data): int {
        return $this->insert(
            "INSERT INTO attachments
                (bug_id, user_id, filename, original_name, file_size, mime_type)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['bug_id'],
                $data['user_id'],
                $data['filename'],
                $data['original_name'],
                $data['file_size'],
                $data['mime_type'],
            ]
        );
    }

    public function delete(int $id): bool {
        return $this->execute(
            "DELETE FROM attachments WHERE id = ?",
            [$id]
        ) > 0;
    }
}