<?php
class UserSettingModel extends BaseModel {

    // Lấy 1 setting theo key
    public function get(int $userId, string $key, mixed $default = null): mixed {
        $row = $this->fetchOne(
            "SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_key = ?",
            [$userId, $key]
        );
        return $row ? $row['setting_value'] : $default;
    }

    // Lấy tất cả settings của user dưới dạng key→value map
    public function getAll(int $userId): array {
        $rows = $this->fetchAll(
            "SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?",
            [$userId]
        );
        $map = [];
        foreach ($rows as $r) {
            $map[$r['setting_key']] = $r['setting_value'];
        }
        return $map;
    }

    // Lưu (upsert) 1 setting
    public function set(int $userId, string $key, mixed $value): void {
        $this->execute(
            "INSERT INTO user_settings (user_id, setting_key, setting_value)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [$userId, $key, $value]
        );
    }

    // Lưu nhiều settings cùng lúc
    public function setMany(int $userId, array $data): void {
        foreach ($data as $key => $value) {
            $this->set($userId, $key, $value);
        }
    }
}
