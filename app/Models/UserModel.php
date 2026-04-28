<?php
class UserModel extends BaseModel {

    // Tìm user theo email hoặc username (dùng cho login)
    public function findByEmailOrUsername(string $input): array|false {
        return $this->fetchOne(
            "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1",
            [$input, $input]
        );
    }

    // Tìm theo email
    public function findByEmail(string $email): array|false {
        return $this->fetchOne(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            [$email]
        );
    }

    // Tìm theo username
    public function findByUsername(string $username): array|false {
        return $this->fetchOne(
            "SELECT * FROM users WHERE username = ? LIMIT 1",
            [$username]
        );
    }

    // Tìm theo ID
    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT * FROM users WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    // Tạo user mới — trả về ID
    public function create(array $data): int {
        return $this->insert(
            "INSERT INTO users
                (username, email, password_hash, full_name, role, is_active, email_verified)
             VALUES (?, ?, ?, ?, 'developer', 1, 0)",
            [
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
                $data['full_name'],
            ]
        );
    }

    // Cập nhật thông tin profile
    public function updateProfile(int $userId, array $data): bool {
        return $this->execute(
            "UPDATE users SET full_name = ?, bio = ?, avatar = ? WHERE id = ?",
            [$data['full_name'], $data['bio'] ?? null, $data['avatar'] ?? null, $userId]
        ) > 0;
    }

    // Cập nhật lần đăng nhập cuối
    public function updateLastLogin(int $userId): void {
        $this->execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$userId]
        );
    }

    // Đổi mật khẩu
    public function updatePassword(int $userId, string $newPassword): bool {
        return $this->execute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]), $userId]
        ) > 0;
    }

    // Kiểm tra email đã tồn tại chưa (dùng cho AJAX check realtime)
    public function emailExists(string $email, int $excludeId = 0): bool {
        $row = $this->fetchOne(
            "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1",
            [$email, $excludeId]
        );
        return (bool) $row;
    }

    // Kiểm tra username đã tồn tại chưa
    public function usernameExists(string $username, int $excludeId = 0): bool {
        $row = $this->fetchOne(
            "SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1",
            [$username, $excludeId]
        );
        return (bool) $row;
    }
}