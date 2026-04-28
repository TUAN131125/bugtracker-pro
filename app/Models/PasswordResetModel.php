<?php
class PasswordResetModel extends BaseModel {

    // Tạo token reset mật khẩu
    public function create(string $email): string {
        // Xóa token cũ của email này trước
        $this->execute(
            "DELETE FROM password_resets WHERE email = ?",
            [$email]
        );

        $token = bin2hex(random_bytes(TOKEN_LENGTH / 2)); // 64 ký tự hex

        $this->insert(
            "INSERT INTO password_resets (email, token, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR))",
            [$email, $token]
        );

        return $token;
    }

    // Tìm token hợp lệ (chưa hết hạn, chưa dùng)
    public function findValidToken(string $token): array|false {
        return $this->fetchOne(
            "SELECT * FROM password_resets
             WHERE token = ?
               AND expires_at > NOW()
               AND used_at IS NULL
             LIMIT 1",
            [$token]
        );
    }

    // Đánh dấu token đã dùng
    public function markUsed(string $token): void {
        $this->execute(
            "UPDATE password_resets SET used_at = NOW() WHERE token = ?",
            [$token]
        );
    }
}